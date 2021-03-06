<?php
    error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

    $domainRegEx = "[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*(?:\.[A-Za-z]{2,})";

    $settings = array(
        // Locations of binaries on web server
        "expectPath" => "/usr/bin/expect",
        "scpPath" => "/usr/bin/scp",
        "sshPath" => "/usr/bin/ssh",

        // URL where we can download dnsmasq config file from DNS server
        "downloadUrl" => "http://192.168.1.1/dnsmasq.conf",

        // IP address of DNS server
        "dnsServer" => "192.168.1.1",

        // Location of DNS service file on DNS server
        "dnsService" => "/etc/init.d/dnsmasq",

        // Name of user with admin rights on DNS server
        "sshUser" => "root",

        // Location where we will upload dnsmasq config file to DNS server
        "uploadPath" => "/etc/dnsmasq.conf",

        // IP address to forward DNS requests that pass whitelist
        "upstreamDns" => "8.8.8.8"
    );

    function actionResult($success, $message) {
        return array("success" => $success, "message" => $message);
    }

    function makeConfig($upstreamDns) {
        return "#[Options]\nbogus-priv\ndomain-needed\nno-resolv\n
#[Category: Forums]\nserver=/stackauth.com/$upstreamDns\nserver=/stackexchange.com/$upstreamDns\nserver=/stackoverflow.com/$upstreamDns\n
#[Category: Games]\nserver=/steampowered.com/$upstreamDns\nserver=/teamfortress.com/$upstreamDns #This is a comment\nserver=/valvesoftware.com/$upstreamDns\n
#Games are awesome\nserver=/gog.com/$upstreamDns\n
#[Category: Search Engines]\nserver=/bing.com/$upstreamDns\nserver=/duckduckgo.com/$upstreamDns\nserver=/google.com/$upstreamDns\n";
    }

    function makeTempFile($tempDir, $fileContents) {
        $tempFile = @tempnam($tempDir, "php");

        if ($tempFile === false) {
            return false;
        }

        $fileResult = @file_put_contents($tempFile, $fileContents);

        if ($fileResult === false) {
            return false;
        }

        return $tempFile;
    }

    function readConfig($downloadUrl, $upstreamDns) {
        global $domainRegEx;

        $categories = [];

        $fileContents = @file_get_contents($downloadUrl);

        if ($fileContents === false) {
            $fileContents = makeConfig($upstreamDns);
        }

        foreach (preg_split("/\n/", $fileContents) as $line) {
            $line = trim($line);

            // e.g. "#[Category: Programming]"
            if (preg_match("/^#\[Category: (.*)\]$/", $line, $matches)) {
                $title = $matches[1];

                $categories[$title] = "";

                unset($matches);
                continue;
            }

            // If we haven't found a category yet (i.e. we are still reading the first few lines of the config file)
            // then get out
            if (!isset($title)) {
                continue;
            }

            // e.g. "server=/stackoverflow.com/8.8.8.8"
            if (preg_match("/^server=\/($domainRegEx)\/\d\.\d\.\d\.\d$/", $line, $matches)) {
                $domain = $matches[1];

                $categories[$title] .= "$domain\n";

                unset($matches);
                continue;
            }

            // e.g. "server=/stackoverflow.com/8.8.8.8 #comment"
            if (preg_match("/^server=\/($domainRegEx)\/\d\.\d\.\d\.\d ?(#.*)$/", $line, $matches)) {
                $domain = $matches[1];
                $comment = $matches[2];

                $categories[$title] .= "$domain $comment\n";

                unset($matches);
                continue;
            }

            // e.g. "#comment"
            if (preg_match("/^(#.*)$/", $line, $matches)) {
                $comment = $matches[1];

                $categories[$title] .= "\n$comment\n";

                unset($matches);
                continue;
            }
        }

        ksort($categories);

        return $categories;
    }

    function uploadConfig($dnsServer, $dnsService, $expectPath, $scpPath, $sshPath, $sshUser, $scriptPath, $tempDir, $uploadPath, $upstreamDns) {
        if (!file_exists($expectPath) || !is_executable($expectPath)) {
            return actionResult(false, "Could not find expect at $expectPath or it is not executable.");
        }

        if (!file_exists($scpPath) || !is_executable($scpPath)) {
            return actionResult(false, "Could not find scp at $scpPath or it is not executable.");
        }

        if (!file_exists($sshPath) || !is_executable($sshPath)) {
            return actionResult(false, "Could not find ssh at $sshPath or it is not executable.");
        }

        if (!file_exists($scriptPath)) {
            return actionResult(false, "Could not find upload script at $scriptPath.");
        }

        $scriptPath = realpath($scriptPath);

        $tempConfigPath = makeTempFile($tempDir, writeConfig($_POST["categories"], $upstreamDns));

        if ($tempConfigPath === false) {
            return actionResult(false, "Could not write config to file at $tempConfigPath.");
        }

        $tempConfigPath = realpath($tempConfigPath);

        $tempPasswordPath = makeTempFile($tempDir, $_POST["password"]);

        if ($tempPasswordPath === false) {
            return actionResult(false, "Could not write password to file at $tempPasswordPath.");
        }

        $tempPasswordPath = realpath($tempPasswordPath);

        $command = "$expectPath -f $scriptPath $dnsServer $dnsService $scpPath $sshPath $sshUser $tempConfigPath $tempPasswordPath $uploadPath";

        exec($command, $output, $exitCode);

        if ($exitCode == 6) {
            unlink($tempPasswordPath);
            return actionResult(false, "Invalid password.");
        }

        // I'm not sure what other exit codes are possible here
        if ($exitCode != 0) {
            unlink($tempPasswordPath);
            return actionResult(false, "Upload script returned an unknown exit code ($exitCode).");
        }

        unlink($tempPasswordPath);
        return actionResult(true, "Upload success.");
    }

    function writeConfig($categories, $upstreamDns) {
        global $domainRegEx;

        $fileContents = "#[Options]\nbogus-priv\ndomain-needed\nno-resolv\n";

        foreach ($categories as $title => $contents) {
            $title = trim($title);
            $contents = trim($contents);

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

        return $fileContents;
    }

    if (isset($_POST["action"]) && $_POST["action"] == "uploadConfig") {
        $actionResult = uploadConfig($settings["dnsServer"],
                                     $settings["dnsService"],
                                     $settings["expectPath"],
                                     $settings["scpPath"],
                                     $settings["sshPath"],
                                     $settings["sshUser"],
                                     "bin/upload.exp",
                                     "temp",
                                     $settings["uploadPath"],
                                     $settings["upstreamDns"]);
    }

    if (isset($_POST["categories"])) {
        $viewData["categories"] = $_POST["categories"];
    } else {
        $viewData["categories"] = readConfig($settings["downloadUrl"], $settings["upstreamDns"]);
    }
?>
