<?php
    $binDir = "c:/wamp/www/dnsmasq/bin";
    $configPath = "$binDir/dnsmasq.conf";
    $scriptPath = "$binDir/upload.exp";
    
    $remoteConfigPath = "http://192.168.1.1/dnsmasq.conf";
    
    $expectPath = "c:/cygwin64/bin/expect.exe";
    
    $tmpDir = "/tmp";
    
    $domainRegEx = "[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*(?:\.[A-Za-z]{2,})";
    
    $nameServer = "8.8.8.8";
    
    $actionResult;
    
    $categories;
    $categoryTabs;
    $categoryDivs;
    $i;
    
    $title;
    $contents;
    $line;
    
    function readConfig($configPath, $domainRegEx, $nameServer) {
        $categories;
        $comment;
        $fileContents;
        $line;
        $matches;
        $title;
        $url;
        
        $fileContents = file_get_contents($configPath);
        
        foreach (preg_split("/\n/", $fileContents) as $line) {
            $line = trim($line);
            
            // e.g. "#[Category: Programming]"
            if (preg_match("/^#\[Category: (.*)\]$/", $line, $matches)) {
                $title = $matches[1];
                
                $categories[$title] = [];
                
                unset($matches);
                continue;
            }
            
            // If we haven't found a category yet (i.e. we are still reading the first few lines of the config file)
            // then get out
            if (!isset($title)) {
                continue;
            }
            
            // e.g. "server=/stackoverflow.com/8.8.8.8"
            if (preg_match("/^server=\/($domainRegEx)\/$nameServer$/", $line, $matches)) {
                $url = $matches[1];
                
                $categories[$title][] = "$url\n";
                
                unset($matches);
                continue;
            }
            
            // e.g. "server=/stackoverflow.com/8.8.8.8 #comment"
            if (preg_match("/^server=\/($domainRegEx)\/$nameServer ?(#.*)$/", $line, $matches)) {
                $url = $matches[1];
                $comment = $matches[2];
                
                $categories[$title][] = "$url $comment\n";
                
                unset($matches);
                continue;
            }
            
            // e.g. "#comment"
            if (preg_match("/^(#.*)$/", $line, $matches)) {
                $comment = $matches[1];
                
                $categories[$title][] = "\n$comment\n";
                
                unset($matches);
                continue;
            }
        }
        
        ksort($categories);
        
        return $categories;
    }
    
    function refreshConfig($configPath, $remoteConfigPath) {
        $fileContents = file_get_contents($remoteConfigPath);
        
        file_put_contents($configPath, $fileContents);
    }
    
    function saveConfig($configPath, $domainRegEx, $nameServer) {
        $comment;
        $contents;
        $fileContents;
        $line;
        $name;
        $title;
        $url;
        $value;
        
        $fileContents = "#[Options]\nbogus-priv\ndomain-needed\nno-resolv\n";
        
        foreach ($_POST as $name => $value) {
            // Don't write the values of the action and password fields to the config file
            if ($name == "action" || $name == "password" || $value == "") {
                continue;
            }
            
            $title = trim($value["title"]);
            $contents = trim($value["contents"]);
            
            if ($title == "" || $contents == "") {
                continue;
            }
            
            $fileContents .= "\n#[Category: $title]\n";
            
            foreach (preg_split("/\n/", $contents) as $line) {
                $line = trim($line);
                
                // e.g. "stackoverflow.com"
                if (preg_match("/^($domainRegEx)$/", $line, $matches)) {
                    $url = $matches[1];
                    
                    $fileContents .= "server=/$url/$nameServer\n";
                    
                    unset($matches);
                    continue;
                }
                
                // e.g. "stackoverflow.com #comment"
                if (preg_match("/^($domainRegEx) ?(#.*)$/", $line, $matches)) {
                    $url = $matches[1];
                    $comment = $matches[2];
                    
                    $fileContents .= "server=/$url/$nameServer $comment\n";
                    
                    unset($matches);
                    continue;
                }
                
                // e.g. "#comment"
                if (preg_match("/^(#.*)$/", $line, $matches)) {
                    $comment = $matches[1];
                    
                    $fileContents .= "\n$comment\n";
                    
                    unset($matches);
                    continue;
                }
            }
        }
        
        file_put_contents($configPath, $fileContents);
    }
    
    function getCygPath($winPath) {
        $cygPath = preg_replace("/\\\\/", "/", $winPath);
        $cygPath = preg_replace("/^([a-z]):/i", "/cygdrive/$1", $cygPath);
        
        return strtolower($cygPath);
    }
    
    function uploadConfig($expectPath, $scriptPath, $configPath, $tmpDir) {
        $scriptPath = getCygPath($scriptPath);
        $configPath = getCygPath($configPath);
        
        $tempFile = tempnam($tmpDir, "php");
        
        file_put_contents($tempFile, $_POST["password"]);
        
        $command = "$expectPath -f $scriptPath $configPath $tempFile";
        
        exec($command, $output, $exitCode);
        
        return $exitCode;
    }
    
    if (!file_exists($configPath)) {
        die("$configPath not found. Please make sure the file exists and refresh the page.");
    }
    
    if (!file_exists($scriptPath)) {
        die("$scriptPath not found. Please make sure the file exists and refresh the page.");
    }
    
    $actionResult = "";
    
    if (isset($_POST["action"]) && $_POST["action"] == "refresh") {
        refreshConfig($configPath, $remoteConfigPath);
        
        $actionResult = "<p class=\"result-success\" id=\"result\">Configuration refreshed.</p>";
    } elseif (isset($_POST["action"]) && $_POST["action"] == "save") {
        saveConfig($configPath, $domainRegEx, $nameServer);
        
        $actionResult = "<p class=\"result-success\" id=\"result\">Configuration saved.</p>";
    } elseif (isset($_POST["action"]) && $_POST["action"] == "upload") {
        if (uploadConfig($expectPath, $scriptPath, $configPath, $tmpDir) == 0) {
            $actionResult = "<p class=\"result-success\" id=\"result\">Configuration uploaded.</p>";
        } else {
            $actionResult = "<p class=\"result-error\" id=\"result\">An error occurred while uploading the configuration.</p>";
        }
    }
    
    $categories = readConfig($configPath, $domainRegEx, $nameServer);
    $categoryTabs = "";
    $categoryDivs = "";
    $i = 1;
    
    foreach ($categories as $title => $contents) {
        $categoryTabs .= "<li><a href=\"#category-$i\">$title</a></li>";
        $categoryDivs .= "<div id=\"category-$i\">
            <p><input class=\"category-title\" name=\"category-$i" . "[title]\" type=\"text\" value=\"$title\" /></p>
            <p><textarea class=\"category-contents\" name=\"category-$i" . "[contents]\">";
        
        foreach ($contents as $line) {
            $categoryDivs .= $line;
        }
        
        $categoryDivs .= "</textarea></p></div>";
        
        $i++;
    }
?>