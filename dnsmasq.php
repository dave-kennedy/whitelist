<?php
    $expPath = 'C:\cygwin64\bin\expect.exe';
    
    $binPath = dirname(__FILE__) . '\bin\\';
    
    $scriptName = 'upload.exp';
    $scriptPath = $binPath . $scriptName;
    
    $fileName = 'dnsmasq.conf';
    $srcPath = $binPath . $fileName;
    $destPath = '/etc/' . $fileName;
    
    $nameServer = '8.8.8.8';
    
    $categories = [];
    
    $saveResult = -1;
    $uploadResult = -1;
    
    function readConfig($srcPath, $nameServer) {
        global $categories;
        
        $cat;
        $file;
        $line;
        $matches;
        $url;
        
        $file = file($srcPath);
        
        foreach ($file as $line) {
            if (preg_match('/#\[Category: (.*)\]/', $line, $matches)) {
                $cat = $matches[1];
                
                $categories[$cat] = [];
                
                unset($matches);
                continue;
            }
            
            if (preg_match('/server=\/(.*)\/' . $nameServer . '/', $line, $matches)) {
                $url = $matches[1];
                
                $categories[$cat][] = $url;
                
                unset($matches);
                continue;
            }
        }
    }
    
    function writeConfig($srcPath, $nameServer) {
        $file;
        $name;
        $url;
        $val;
        
        $file = fopen($srcPath, 'w');
        
        fwrite($file, "#[Options]\nbogus-priv\ndomain-needed\nno-resolv\n");
        
        foreach ($_POST as $name => $val) {
            if ($name == 'action' || $name == 'password' || $val == '') {
                continue;
            }
            
            fwrite($file, "\n#[Category: " . ucfirst($name) . ']');
            
            foreach (preg_split('/\n/', $val) as $url) {
                $url = trim($url);
                
                if ($url == '') {
                    continue;
                }
                
                fwrite($file, "\nserver=/" . $url . '/' . $nameServer);
            }
            
            fwrite($file, "\n");
        }
        
        fclose($file);
        
        $saveResult = 0;
    }
    
    function getCygPath($winPath) {
        $cygPath = preg_replace("/\\\\/", '/', $winPath);
        $cygPath = preg_replace('/([a-z]):/i', '/cygdrive/$1', $cygPath);
        return $cygPath;
    }
    
    function uploadConfig($expPath, $scriptPath, $srcPath, $destPath, $password) {
        $scriptPath = getCygPath($scriptPath);
        $srcPath = getCygPath($srcPath);
        $destPath = getCygPath($destPath);
        
        $command = $expPath . ' -d -f ' . $scriptPath . ' ' . $srcPath . ' ' . $destPath . ' ' . $password;
        
        exec($command, $output, $exitCode);
        
        return $exitCode;
    }
    
    if (!file_exists($srcPath)) {
        die($srcPath . ' not found. Please make sure the file exists and refresh the page.');
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'save') {
        $saveResult = writeConfig($srcPath, $nameServer);
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'upload' && isset($_POST['password'])) {
        $uploadResult = uploadConfig($expPath, $scriptPath, $srcPath, $destPath, $_POST['password']);
    }
    
    readConfig($srcPath, $nameServer);
?>