<?php
require_once 'config.php';
header('Content-Type: application/json');

$timestamps = [];
foreach ($filesToCheck as $file) {
    $timestamps[$file] = file_exists($file) ? filemtime($file) : 0;
}

echo json_encode($timestamps);
?>