<?php
header('Content-Type: application/json; charset=utf-8');
if(!isset($_FILES['zip'])){ echo json_encode(['ok'=>false,'message'=>'Fichier manquant']); exit; }
$zipTmp = $_FILES['zip']['tmp_name'];
$zip = new ZipArchive();
if($zip->open($zipTmp) !== TRUE){ echo json_encode(['ok'=>false,'message'=>'ZIP invalide']); exit; }
$root = realpath(__DIR__ . '/..');
$dataDir = $root . '/data';
if(!is_dir($dataDir)) mkdir($dataDir, 0775, true);
for($i=0; $i<$zip->numFiles; $i++){
  $name = $zip->getNameIndex($i);
  if(preg_match('/\.json$/', $name)){
    $stream = $zip->getStream($name);
    $contents = stream_get_contents($stream);
    fclose($stream);
    $base = basename($name);
    file_put_contents($dataDir . '/' . $base, $contents);
  }
}
$zip->close();
echo json_encode(['ok'=>true,'message'=>'Import réussi']);
