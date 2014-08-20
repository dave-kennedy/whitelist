<?php
    $settings = array(
        "configPath" => "c:/wamp/www/dnsmasq/bin/dnsmasq.conf",
        "scriptPath" => "c:/wamp/www/dnsmasq/bin/upload.exp",
        "remoteConfigUrl" => "http://192.168.1.1/dnsmasq.conf",
        "expectPath" => "c:/cygwin64/bin/expect.exe",
        "tempDir" => "c:/wamp/www/dnsmasq/temp",
        "domainRegEx" => "[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*(?:\.[A-Za-z]{2,})",
        "ipRegEx" => "\d\.\d\.\d\.\d",
        "upstreamDns" => "8.8.8.8"
    );
    
    function makeConfig($upstreamDns) {
        return "#[Options]\nbogus-priv\ndomain-needed\nno-resolv\n
#[Category: Forums]\nserver=/stackauth.com/$upstreamDns\nserver=/stackexchange.com/$upstreamDns\nserver=/stackoverflow.com/$upstreamDns\n
#[Category: Games]\nserver=/steampowered.com/$upstreamDns\nserver=/teamfortress.com/$upstreamDns #This is a comment\nserver=/valvesoftware.com/$upstreamDns\n
#Games are awesome\nserver=/gog.com/$upstreamDns\n
#[Category: Search Engines]\nserver=/bing.com/$upstreamDns\nserver=/duckduckgo.com/$upstreamDns\nserver=/google.com/$upstreamDns\n";
    }
    
    function readConfig($configPath, $upstreamDns, $domainRegEx, $ipRegEx) {
        $categories = [];
        
        $fileContents = @file_get_contents($configPath);
        
        if ($fileContents === false) {
            $fileContents = makeConfig($upstreamDns);
        }
        
        foreach (preg_split("/\n/", $fileContents) as $line) {
            $line = trim($line);
            
            // e.g. "#[Category: Programming]"
            if (preg_match("/^#\[Category: (.*)\]$/", $line, $matches)) {
                $title = $matches[1];
                
                unset($matches);
                continue;
            }
            
            // If we haven't found a category yet (i.e. we are still reading the first few lines of the config file)
            // then get out
            if (!isset($title)) {
                continue;
            }
            
            // e.g. "server=/stackoverflow.com/8.8.8.8"
            if (preg_match("/^server=\/($domainRegEx)\/$ipRegEx$/", $line, $matches)) {
                $domain = $matches[1];
                
                $categories[$title][] = "$domain\n";
                
                unset($matches);
                continue;
            }
            
            // e.g. "server=/stackoverflow.com/8.8.8.8 #comment"
            if (preg_match("/^server=\/($domainRegEx)\/$ipRegEx ?(#.*)$/", $line, $matches)) {
                $domain = $matches[1];
                $comment = $matches[2];
                
                $categories[$title][] = "$domain $comment\n";
                
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
    
    function actionResult($success, $message) {
        return array("success" => $success, "message" => $message);
    }
    
    function saveConfig($configPath, $upstreamDns, $domainRegEx) {
        $fileContents = "#[Options]\nbogus-priv\ndomain-needed\nno-resolv\n";
        
        foreach ($_POST as $key => $value) {
            // Don't write the values of the action and password fields to the config file
            if ($key == "action" || $key == "password" || $value == "") {
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
                    $domain = $matches[1];
                    
                    $fileContents .= "server=/$domain/$upstreamDns\n";
                    
                    unset($matches);
                    continue;
                }
                
                // e.g. "stackoverflow.com #comment"
                if (preg_match("/^($domainRegEx) ?(#.*)$/", $line, $matches)) {
                    $domain = $matches[1];
                    $comment = $matches[2];
                    
                    $fileContents .= "server=/$domain/$upstreamDns $comment\n";
                    
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
        
        $fileResult = @file_put_contents($configPath, $fileContents);
        
        if ($fileResult === false) {
            return actionResult(false, "Could not write to configuration file at $configPath.");
        }
        
        return actionResult(true, "Save success.");
    }
    
    function syncConfig($configPath, $remoteConfigUrl) {
        copy($configPath, "$configPath.bak");
        
        $fileContents = @file_get_contents($remoteConfigUrl);
        
        if ($fileContents === false) {
            return actionResult(false, "Could not read remote configuration file at $remoteConfigUrl.");
        }
        
        $fileResult = @file_put_contents($configPath, $fileContents);
        
        if ($fileResult === false) {
            return actionResult(false, "Could not write to configuration file at $configPath.");
        }
        
        return actionResult(true, "Sync success.");
    }
    
    function getCygPath($winPath) {
        $cygPath = preg_replace("/\\\\/", "/", $winPath);
        $cygPath = preg_replace("/^([a-z]):/i", "/cygdrive/$1", $cygPath);
        
        return strtolower($cygPath);
    }
    
    function uploadConfig($configPath, $expectPath, $scriptPath, $tempDir) {
        if (!file_exists($expectPath)) {
            return actionResult(false, "expect executable not found at $expectPath.");
        }
        
        if (!file_exists($scriptPath)) {
            return actionResult(false, "Upload script not found at $scriptPath.");
        }
        
        if (!file_exists($configPath)) {
            return actionResult(false, "Configuration file not found at $configPath.");
        }
        
        $scriptPath = getCygPath($scriptPath);
        $configPath = getCygPath($configPath);
        
        $tempFile = @tempnam($tempDir, "php");
        
        if ($tempFile === false) {
            return actionResult(false, "Could not create temp file in $tempDir.");
        }
        
        $fileResult = @file_put_contents($tempFile, $_POST["password"]);
        
        if ($fileResult === false) {
            return actionResult(false, "Could not write to temp file at $tempFile.");
        }
        
        $tempFile = getCygPath($tempFile);
        
        $command = "$expectPath -f $scriptPath $configPath $tempFile";
        
        exec($command, $output, $exitCode);
        
        if ($exitCode == 2) {
            return actionResult(false, "Configuration file not found at $configPath.");
        }
        
        if ($exitCode == 3) {
            return actionResult(false, "Upload script not found at $configPath.");
        }
        
        if ($exitCode == 4) {
            return actionResult(false, "scp executable not found.");
        }
        
        if ($exitCode == 5) {
            return actionResult(false, "Invalid password.");
        }
        
        // I'm not sure what other exit codes are possible here
        if ($exitCode != 0) {
            return actionResult(false, "Upload script returned an unknown exit code ($exitCode).");
        }
        
        return actionResult(true, "Upload success.");
    }
    
    function compareArrays($array1, $array2) {
        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                $diff[$key] = array_diff($value, $array2[$key]);
            } else {
                $diff[$key] = $value;
            }
        }
        
        return $diff;
    }
    
    if (isset($_POST["action"]) && $_POST["action"] == "saveConfig") {
        $actionResult = saveConfig($settings["configPath"], $settings["upstreamDns"], $settings["domainRegEx"]);
    } elseif (isset($_POST["action"]) && $_POST["action"] == "syncConfig") {
        $actionResult = syncConfig($settings["configPath"], $settings["remoteConfigUrl"]);
    } elseif (isset($_POST["action"]) && $_POST["action"] == "uploadConfig") {
        $actionResult = uploadConfig($settings["configPath"], $settings["expectPath"], $settings["scriptPath"], $settings["tempDir"]);
    }
    
    $viewData["categories"] = readConfig($settings["configPath"], $settings["upstreamDns"], $settings["domainRegEx"], $settings["ipRegEx"]);
    $viewData["remoteCategories"] = readConfig($settings["remoteConfigUrl"], $settings["upstreamDns"], $settings["domainRegEx"], $settings["ipRegEx"]);
    
    $viewData["localOnly"] = compareArrays($viewData["categories"], $viewData["remoteCategories"]);
    $viewData["remoteOnly"] = compareArrays($viewData["remoteCategories"], $viewData["categories"]);
?>