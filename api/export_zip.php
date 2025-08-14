<?php
$root = realpath(__DIR__ . '/..');
$zipName = 'donnees-json.zip';
$zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;
$zip = new ZipArchive();
if($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)!==TRUE){
  http_response_code(500); echo 'ZIP error'; exit;
}
$dataDir = $root . '/data';
if(!is_dir($dataDir)) mkdir($dataDir, 0775, true);
$files = scandir($dataDir);
foreach($files as $file){
  if(pathinfo($file, PATHINFO_EXTENSION) === 'json'){
    $zip->addFile($dataDir . '/' . $file, 'data/' . $file);
  }
}
$zip->close();
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="'.$zipName.'"');
readfile($zipPath);
unlink($zipPath);
