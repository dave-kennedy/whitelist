<?php
    $binPath = "c:/wamp/www/dnsmasq/bin";
    $config = "$binPath/dnsmasq.conf";
    $script = "$binPath/upload.exp";
    
    $domainRegEx = "[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*(?:\.[A-Za-z]{2,})";
    
    $nameServer = "8.8.8.8";
    
    $saveResult;
    $uploadResult;
    
    $categories;
    $categoryTabs;
    $categoryDivs;
    $i;
    
    $title;
    $contents;
    $line;
    
    function readConfig($config, $domainRegEx, $nameServer) {
        $categories;
        $comment;
        $contents;
        $line;
        $matches;
        $title;
        $url;
        
        $contents = file_get_contents($config);
        
        foreach (preg_split("/\n/", $contents) as $line) {
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
    
    function saveConfig($config, $domainRegEx, $nameServer) {
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
            
            $fileContents .= "\n#[Category: " . $title . "]\n";
            
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
        
        file_put_contents($config, $fileContents);
        
        return 0;
    }
    
    function getCygPath($winPath) {
        $cygPath = preg_replace("/\\\\/", "/", $winPath);
        $cygPath = preg_replace("/^([a-z]):/i", "/cygdrive/$1", $cygPath);
        
        return strtolower($cygPath);
    }
    
    function uploadConfig($script, $config) {
        $script = getCygPath($script);
        $config = getCygPath($config);
        $tempFile = getCygPath(makeTemp());
        
        $command = "c:/cygwin64/bin/expect.exe -f $script $config $tempFile";
        
        exec($command, $output, $exitCode);
        
        return $exitCode;
    }
    
    function makeTemp() {
        $tempFile = tempnam("/tmp", "php");
        
        file_put_contents($tempFile, $_POST["password"]);
        
        return $tempFile;
    }
    
    if (!file_exists($config)) {
        die("$config not found. Please make sure the file exists and refresh the page.");
    }
    
    if (!file_exists($script)) {
        die("$script not found. Please make sure the file exists and refresh the page.");
    }
    
    $saveResult = "";
    
    if (isset($_POST["action"]) && $_POST["action"] == "save") {
        if (saveConfig($config, $domainRegEx, $nameServer) == 0) {
            $saveResult = "<p class=\"result-success\" id=\"result\">Configuration saved.</p>";
        } else {
            $saveResult = "<p class=\"result-error\" id=\"result\">An error occurred while saving the configuration.</p>";
        }
    }
    
    $uploadResult = "";
    
    if (isset($_POST["action"]) && $_POST["action"] == "upload") {
        if (uploadConfig($script, $config) == 0) {
            $uploadResult = "<p class=\"result-success\" id=\"result\">Configuration uploaded.</p>";
        } else {
            $uploadResult = "<p class=\"result-error\" id=\"result\">An error occurred while uploading the configuration.</p>";
        }
    }
    
    $categories = readConfig($config, $domainRegEx, $nameServer);
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