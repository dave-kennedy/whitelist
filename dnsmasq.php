<?php
    $binPath = 'C:/wamp/www/dnsmasq/bin/';
    $config = $binPath . 'dnsmasq.conf';
    $script = $binPath . 'upload.exp';
    
    $nameServer = '8.8.8.8';
    
    $saveResult = -1;
    $uploadResult = -1;
    
    $tempFile;
    
    function readConfig($config, $nameServer) {
        $category;
        $categories;
        $contents;
        $line;
        $matches;
        $url;
        
        $contents = file($config);
        
        foreach ($contents as $line) {
            if (preg_match('/#\[Category: (.*)\]/', $line, $matches)) {
                $category = $matches[1];
                
                $categories[$category] = [];
                
                unset($matches);
                continue;
            }
            
            if (preg_match('/server=\/(.*)\/' . $nameServer . '/', $line, $matches)) {
                $url = $matches[1];
                
                $categories[$category][] = $url;
                
                unset($matches);
                continue;
            }
        }
        
        return $categories;
    }
    
    function writeConfig($config, $nameServer) {
        $handle;
        $name;
        $url;
        $value;
        
        $handle = fopen($config, 'w');
        
        fwrite($handle, "#[Options]\nbogus-priv\ndomain-needed\nno-resolv\n");
        
        foreach ($_POST as $name => $value) {
            if ($name == 'action' || $name == 'password' || $value == '') {
                continue;
            }
            
            fwrite($handle, "\n#[Category: " . ucfirst($name) . ']');
            
            foreach (preg_split('/\n/', $value) as $url) {
                $url = trim($url);
                
                if ($url == '') {
                    continue;
                }
                
                fwrite($handle, "\nserver=/" . $url . '/' . $nameServer);
            }
            
            fwrite($handle, "\n");
        }
        
        fclose($handle);
        
        $saveResult = 0;
    }
    
    function getCygPath($winPath) {
        $cygPath = preg_replace("/\\\\/", '/', $winPath);
        $cygPath = preg_replace('/([a-z]):/i', '/cygdrive/$1', $cygPath);
        
        return $cygPath;
    }
    
    function uploadConfig($script, $config, $tempFile) {
        $script = getCygPath($script);
        $config = getCygPath($config);
        $tempFile = getCygPath($tempFile);
        $command = 'C:\cygwin64\bin\expect.exe -f ' . $script . ' ' . $config . ' ' . $tempFile;
        
        exec($command, $output, $exitCode);
        
        return $exitCode;
    }
    
    function makeTemp() {
        $tempFile = tempnam('/tmp', 'php');
        $handle = fopen($tempFile, 'w');
        
        fwrite($handle, $_POST['password']);
        
        fclose($handle);
        
        return $tempFile;
    }
    
    if (!file_exists($config)) {
        die($config . ' not found. Please make sure the file exists and refresh the page.');
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'save') {
        $saveResult = writeConfig($config, $nameServer);
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'upload') {
        $tempFile = makeTemp();
        $uploadResult = uploadConfig($script, $config, $tempFile);
        unlink($tempFile);
    }
?>