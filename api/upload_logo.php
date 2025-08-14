<?php
header('Content-Type: application/json; charset=utf-8');
if(!isset($_FILES['logo'])){ echo json_encode(['ok'=>false,'message'=>'Fichier manquant']); exit; }
$ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
if(!in_array($ext, ['png','jpg','jpeg','webp','gif'])){ echo json_encode(['ok'=>false,'message'=>'Format non supporté']); exit; }
$target = __DIR__ . '/../assets/img/logo-upload.' . $ext;
if(!move_uploaded_file($_FILES['logo']['tmp_name'], $target)){
  echo json_encode(['ok'=>false,'message'=>'Upload échoué']); exit;
}
$rel = 'assets/img/' . basename($target);
echo json_encode(['ok'=>true,'path'=>$rel]);
