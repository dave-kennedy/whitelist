<?php
    $settings = array(
        "configPath" => "c:/wamp/www/dnsmasq/bin/dnsmasq.conf",
        "scriptPath" => "c:/wamp/www/dnsmasq/bin/upload.exp",
        "remoteConfigUrl" => "http://192.168.1.1/dnsmasq.conf",
        "expectPath" => "c:/cygwin64/bin/expect.exe",
        "tmpDir" => "/tmp",
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
    
    function readConfig() {
        global $settings;
        
        $categories = [];
        
        $fileContents = @file_get_contents($settings["configPath"]);
        
        if ($fileContents === false) {
            $fileContents = makeConfig($settings["upstreamDns"]);
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
            if (preg_match("/^server=\/(" . $settings["domainRegEx"] . ")\/" . $settings["ipRegEx"] . "$/", $line, $matches)) {
                $domain = $matches[1];
                
                $categories[$title][] = "$domain\n";
                
                unset($matches);
                continue;
            }
            
            // e.g. "server=/stackoverflow.com/8.8.8.8 #comment"
            if (preg_match("/^server=\/(" . $settings["domainRegEx"] . ")\/" . $settings["ipRegEx"] . " ?(#.*)$/", $line, $matches)) {
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
        
        return array("categories" => $categories);
    }
    
    function actionResult($success, $message) {
        return array("success" => $success, "message" => $message);
    }
    
    function saveConfig() {
        global $settings;
        
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
                if (preg_match("/^(" . $settings["domainRegEx"] . ")$/", $line, $matches)) {
                    $domain = $matches[1];
                    
                    $fileContents .= "server=/$domain/" . $settings["upstreamDns"] . "\n";
                    
                    unset($matches);
                    continue;
                }
                
                // e.g. "stackoverflow.com #comment"
                if (preg_match("/^(" . $settings["domainRegEx"] . ") ?(#.*)$/", $line, $matches)) {
                    $domain = $matches[1];
                    $comment = $matches[2];
                    
                    $fileContents .= "server=/$domain/" . $settings["upstreamDns"] . " $comment\n";
                    
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
        
        $fileResult = @file_put_contents($settings["configPath"], $fileContents);
        
        if ($fileResult === false) {
            return actionResult(false, "Could not write to configuration file at " . $settings["configPath"] . ".");
        }
        
        return actionResult(true, "Save success.");
    }
    
    function syncConfig() {
        global $settings;
        
        $fileContents = @file_get_contents($settings["remoteConfigUrl"]);
        
        if ($fileContents === false) {
            return actionResult(false, "Could not read remote configuration file at " . $settings["remoteConfigUrl"] . ".");
        }
        
        $fileResult = @file_put_contents($settings["configPath"], $fileContents);
        
        if ($fileResult === false) {
            return actionResult(false, "Could not write to configuration file at " . $settings["configPath"] . ".");
        }
        
        return actionResult(true, "Sync success.");
    }
    
    function getCygPath($winPath) {
        $cygPath = preg_replace("/\\\\/", "/", $winPath);
        $cygPath = preg_replace("/^([a-z]):/i", "/cygdrive/$1", $cygPath);
        
        return strtolower($cygPath);
    }
    
    function uploadConfig() {
        global $settings;
        
        if (!file_exists($settings["expectPath"])) {
            return actionResult(false, "expect executable not found at " . $settings["expectPath"] . ".");
        }
        
        if (!file_exists($settings["scriptPath"])) {
            return actionResult(false, "Upload script not found at " . $settings["scriptPath"] . ".");
        }
        
        if (!file_exists($settings["configPath"])) {
            return actionResult(false, "Configuration file not found at " . $settings["configPath"] . ".");
        }
        
        $scriptPath = getCygPath($settings["scriptPath"]);
        $configPath = getCygPath($settings["configPath"]);
        
        $tempFile = @tempnam($settings["tmpDir"], "php");
        
        if ($tempFile === false) {
            return actionResult(false, "Could not create temp file in " . $settings["tmpDir"] . ".");
        }
        
        $fileResult = @file_put_contents($tempFile, $_POST["password"]);
        
        if ($fileResult === false) {
            return actionResult(false, "Could not write to temp file at $tempFile.");
        }
        
        $command = $settings["expectPath"] . " -f $scriptPath $configPath $tempFile";
        
        exec($command, $output, $exitCode);
        
        if ($exitCode == 1) {
            return actionResult(false, "Invalid password.");
        }
        
        // I'm not sure what other exit codes are possible here
        if ($exitCode != 0) {
            return actionResult(false, "Upload script returned an unknown exit code ($exitCode).");
        }
        
        return actionResult(true, "Upload success!");
    }
    
    function doAction($action) {
        if ($action == "saveConfig") {
            return saveConfig();
        }
        
        if ($action == "syncConfig") {
            return syncConfig();
        }
        
        if ($action == "uploadConfig") {
            return uploadConfig();
        }
        
        return actionResult(false, "Unsupported action requested: $action.");
    }
    
    if (isset($_POST["action"])) {
        $actionResult = doAction($_POST["action"]);
    }
    
    $viewData = readConfig();
?>