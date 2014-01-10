<?php
    $binPath = "c:/wamp/www/dnsmasq/bin";
    $config = "$binPath/dnsmasq.conf";
    $script = "$binPath/upload.exp";
    
    $nameServer = "8.8.8.8";
    
    $saveResult = -1;
    $uploadResult = -1;
    
    $tempFile;
    
    $domainRegEx = "[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,})";
    
    function readConfig($config, $nameServer) {
        global $domainRegEx;
        
        $category;
        $categories;
        $comment;
        $contents;
        $line;
        $matches;
        $url;
        
        $contents = file($config);
        
        foreach ($contents as $line) {
            $line = trim($line);
            
            // e.g. "#[Category: Programming]"
            if (preg_match("/^#\[Category: (.*)\]$/", $line, $matches)) {
                $category = $matches[1];
                
                $categories[$category] = [];
                
                unset($matches);
                continue;
            }
            
            // If we haven't found a category yet (i.e. we are still reading the first few lines of the config file)
            // then get out
            if (!isset($category)) {
                continue;
            }
            
            // e.g. "server=/stackoverflow.com/8.8.8.8"
            if (preg_match("/^server=\/($domainRegEx)\/$nameServer$/", $line, $matches)) {
                $url = $matches[1];
                
                $categories[$category][] = "$url\n";
                
                unset($matches);
                continue;
            }
            
            // e.g. "server=/stackoverflow.com/8.8.8.8 #comment"
            if (preg_match("/^server=\/($domainRegEx)\/$nameServer ?(#.*)$/", $line, $matches)) {
                $url = $matches[1];
                $comment = end($matches);
                
                $categories[$category][] = "$url $comment\n";
                
                unset($matches);
                continue;
            }
            
            // e.g. "#comment"
            if (preg_match("/^(#.*)$/", $line, $matches)) {
                $comment = $matches[1];
                
                $categories[$category][] = "\n$comment\n";
                
                unset($matches);
                continue;
            }
        }
        
        return $categories;
    }
    
    function writeConfig($config, $nameServer) {
        global $domainRegEx;
        
        $comment;
        $handle;
        $line;
        $name;
        $url;
        $value;
        
        $handle = fopen($config, "w");
        
        fwrite($handle, "#[Options]\nbogus-priv\ndomain-needed\nno-resolv\n");
        
        foreach ($_POST as $name => $value) {
            // Don't write the values of the action and password fields to the config file
            if ($name == "action" || $name == "password" || $value == "") {
                continue;
            }
            
            fwrite($handle, "\n#[Category: " . ucfirst($name) . "]\n");
            
            foreach (preg_split("/\n/", $value) as $line) {
                $line = trim($line);
                
                // e.g. "stackoverflow.com"
                if (preg_match("/^($domainRegEx)$/", $line, $matches)) {
                    $url = $matches[1];
                    
                    fwrite($handle, "server=/$url/$nameServer\n");
                    
                    unset($matches);
                    continue;
                }
                
                // e.g. "stackoverflow.com #comment"
                if (preg_match("/^($domainRegEx) ?(#.*)$/", $line, $matches)) {
                    $url = $matches[1];
                    $comment = end($matches);
                    
                    fwrite($handle, "server=/$url/$nameServer $comment\n");
                    
                    unset($matches);
                    continue;
                }
                
                // e.g. "#comment"
                if (preg_match("/^(#.*)$/", $line, $matches)) {
                    $comment = $matches[1];
                    
                    fwrite($handle, "\n$comment\n");
                    
                    unset($matches);
                    continue;
                }
            }
        }
        
        fclose($handle);
        
        $saveResult = 0;
    }
    
    function getCygPath($winPath) {
        $cygPath = preg_replace("/^([a-z]):/i", "/cygdrive/$1", $winPath);
        
        return $cygPath;
    }
    
    function uploadConfig($script, $config, $tempFile) {
        $script = getCygPath($script);
        $config = getCygPath($config);
        $tempFile = getCygPath($tempFile);
        $command = "c:/cygwin64/bin/expect.exe -f $script $config $tempFile";
        
        exec($command, $output, $exitCode);
        
        return $exitCode;
    }
    
    function makeTemp() {
        $tempFile = tempnam("/tmp", "php");
        $handle = fopen($tempFile, "w");
        
        fwrite($handle, $_POST["password"]);
        
        fclose($handle);
        
        return $tempFile;
    }
    
    if (!file_exists($config)) {
        die("$config not found. Please make sure the file exists and refresh the page.");
    }
    
    if (isset($_POST["action"]) && $_POST["action"] == "save") {
        $saveResult = writeConfig($config, $nameServer);
    }
    
    if (isset($_POST["action"]) && $_POST["action"] == "upload") {
        $tempFile = makeTemp();
        $uploadResult = uploadConfig($script, $config, $tempFile);
        unlink($tempFile);
    }
?>