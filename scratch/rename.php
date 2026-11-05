<?php

$dirs = ['app', 'routes', 'resources', 'database', 'bootstrap', 'config'];
$replacements = [
    'Customer' => 'Saver',
    'customer' => 'saver',
    'Customers' => 'Savers',
    'customers' => 'savers',
];

function replaceInFile($filePath, $replacements) {
    $content = file_get_contents($filePath);
    $newContent = $content;
    foreach ($replacements as $search => $replace) {
        $newContent = str_replace($search, $replace, $newContent);
    }
    if ($content !== $newContent) {
        file_put_contents($filePath, $newContent);
        echo "Updated content in: $filePath\n";
    }
}

function processDir($dir, $replacements) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            processDir($path, $replacements);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, ['php', 'json', 'md', 'env', 'js', 'html', 'css', 'xml'])) {
                replaceInFile($path, $replacements);
            }
        }
    }
}

// Rename files and dirs first
function renameFilesDirs($dir, $replacements) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        
        $newName = $file;
        foreach ($replacements as $search => $replace) {
            $newName = str_replace($search, $replace, $newName);
        }
        
        $newPath = $dir . '/' . $newName;
        
        if (is_dir($path)) {
            renameFilesDirs($path, $replacements);
        }
        
        if ($newName !== $file) {
            rename($path, $newPath);
            echo "Renamed: $path to $newPath\n";
        }
    }
}

foreach ($dirs as $dir) {
    $fullPath = "c:/laragon/www/bankmini/" . $dir;
    if (is_dir($fullPath)) {
        processDir($fullPath, $replacements);
        renameFilesDirs($fullPath, $replacements);
        // Process again after rename just in case
        processDir($fullPath, $replacements);
    }
}

echo "Done.";
