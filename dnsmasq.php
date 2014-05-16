<?php
    $settings = array(
        "configPath" => "c:/wamp/www/dnsmasq/bin/dnsmasq.conf",
        "scriptPath" => "c:/wamp/www/dnsmasq/bin/upload.exp",
        "remoteConfigUrl" => "http://192.168.1.1/dnsmasq.conf",
        "expectPath" => "c:/cygwin64/bin/expect.exe",
        "tmpDir" => "/tmp",
        "domainRegEx" => "[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*(?:\.[A-Za-z]{2,})",
        "ipRegEx" => "\d\.\d\.\d\.\d",
        "nameServer" => "8.8.8.8"
    );
    
    function readConfig() {
        global $settings;
        
        $categories = [];
        $exceptions = [];
        $exceptionMode = false;
        
        $fileContents = file_get_contents($settings["configPath"]);
        
        foreach (preg_split("/\n/", $fileContents) as $line) {
            $line = trim($line);
            
            // e.g. "#[Category: Programming]"
            if (preg_match("/^#\[Category: (.*)\]$/", $line, $matches)) {
                $exceptionMode = false;
                $title = $matches[1];
                
                unset($matches);
                continue;
            }
            
            if ($line == "#[Exceptions]") {
                $exceptionMode = true;
                continue;
            }
            
            // If we haven't found a category yet (i.e. we are still reading the first few lines of the config file)
            // then get out
            if (!isset($title)) {
                continue;
            }
            
            // e.g. "server=/stackoverflow.com/8.8.8.8"
            if (preg_match("/^server=\/(" . $settings["domainRegEx"] . ")\/" . $settings["ipRegEx"] . "$/", $line, $matches)) {
                $domain = $matches[1];
                
                if ($exceptionMode) {
                    $exceptions[] = "$domain\n";
                } else {
                    $categories[$title][] = "$domain\n";
                }
                
                unset($matches);
                continue;
            }
            
            // e.g. "server=/stackoverflow.com/8.8.8.8 #comment"
            if (preg_match("/^server=\/(" . $settings["domainRegEx"] . ")\/" . $settings["ipRegEx"] . " ?(#.*)$/", $line, $matches)) {
                $domain = $matches[1];
                $comment = $matches[2];
                
                if ($exceptionMode) {
                    $exceptions[] = "$domain $comment\n";
                } else {
                    $categories[$title][] = "$domain $comment\n";
                }
                
                unset($matches);
                continue;
            }
            
            // e.g. "#comment"
            if (preg_match("/^(#.*)$/", $line, $matches)) {
                $comment = $matches[1];
                
                if ($exceptionMode) {
                    $exceptions[] = "\n$comment\n";
                } else {
                    $categories[$title][] = "\n$comment\n";
                }
                
                unset($matches);
                continue;
            }
        }
        
        ksort($categories);
        
        return array("categories" => $categories, "exceptions" => $exceptions);
    }
    
    function syncConfig() {
        global $settings;
        
        $fileContents = file_get_contents($settings["remoteConfigUrl"]);
        
        file_put_contents($settings["configPath"], $fileContents);
    }
    
    function saveConfig() {
        global $settings;
        
        $exceptionMode = false;
        
        $fileContents = "#[Options]\nbogus-priv\ndomain-needed\nno-resolv\n";
        
        foreach ($_POST as $key => $value) {
            // Don't write the values of the action and password fields to the config file
            if ($key == "action" || $key == "password" || $value == "") {
                continue;
            }
            
            if ($key == "exceptions") {
                $exceptionMode = true;
                
                $contents = trim($value);
                
                if ($contents == "") {
                    continue;
                }
                
                $fileContents .= "\n#[Exceptions]\n";
            } else {
                $exceptionMode = false;
            
                $title = trim($value["title"]);
                $contents = trim($value["contents"]);
                
                if ($title == "" || $contents == "") {
                    continue;
                }
                
                $fileContents .= "\n#[Category: $title]\n";
            }
            
            foreach (preg_split("/\n/", $contents) as $line) {
                $line = trim($line);
                
                // e.g. "stackoverflow.com"
                if (preg_match("/^(" . $settings["domainRegEx"] . ")$/", $line, $matches)) {
                    $domain = $matches[1];
                    
                    if ($exceptionMode) {
                        $fileContents .= "server=/$domain/0.0.0.0\n";
                    } else {
                        $fileContents .= "server=/$domain/" . $settings["nameServer"] . "\n";
                    }
                    
                    unset($matches);
                    continue;
                }
                
                // e.g. "stackoverflow.com #comment"
                if (preg_match("/^(" . $settings["domainRegEx"] . ") ?(#.*)$/", $line, $matches)) {
                    $domain = $matches[1];
                    $comment = $matches[2];
                    
                    if ($exceptionMode) {
                        $fileContents .= "server=/$domain/0.0.0.0 $comment\n";
                    } else {
                        $fileContents .= "server=/$domain/" . $settings["nameServer"] . " $comment\n";
                    }
                    
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
        
        file_put_contents($settings["configPath"], $fileContents);
    }
    
    function getCygPath($winPath) {
        $cygPath = preg_replace("/\\\\/", "/", $winPath);
        $cygPath = preg_replace("/^([a-z]):/i", "/cygdrive/$1", $cygPath);
        
        return strtolower($cygPath);
    }
    
    function uploadConfig() {
        global $settings;
        
        $scriptPath = getCygPath($settings["scriptPath"]);
        $configPath = getCygPath($settings["configPath"]);
        
        $tempFile = tempnam($settings["tmpDir"], "php");
        
        file_put_contents($tempFile, $_POST["password"]);
        
        $command = $settings["expectPath"] . " -f $scriptPath $configPath $tempFile";
        
        exec($command, $output, $exitCode);
        
        return $exitCode;
    }
    
    function getActionResult() {
        $result["actionResult"]["action"] = "";
        $result["actionResult"]["success"] = false;
        
        if (isset($_POST["action"]) && $_POST["action"] == "sync") {
            syncConfig();
            
            $result["actionResult"]["action"] = "sync";
            $result["actionResult"]["success"] = true;
            
        } elseif (isset($_POST["action"]) && $_POST["action"] == "save") {
            saveConfig();
            
            $result["actionResult"]["action"] = "sync";
            $result["actionResult"]["success"] = true;
            
        } elseif (isset($_POST["action"]) && $_POST["action"] == "upload") {
            $result["actionResult"]["action"] = "sync";
            
            if (uploadConfig() == 0) {
                $result["actionResult"]["success"] = true;
            }
        }
        
        return $result;
    }
    
    $viewData = array_merge(getActionResult(), readConfig());
?>