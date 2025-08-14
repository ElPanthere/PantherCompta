<?php
header('Content-Type: application/json; charset=utf-8');
$path = __DIR__ . '/../data/invoices.json';
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
$id = intval($payload['id'] ?? 0);
$list = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
$idx = -1;
foreach($list as $k=>$v){ if(($v['id']??0) === $id){ $idx=$k; break; } }
if($idx===-1){ echo json_encode(['ok'=>false,'message'=>'Introuvable']); exit; }
if(($list[$idx]['status'] ?? '') === 'paid'){ echo json_encode(['ok'=>false,'message'=>'Impossible de supprimer une facture payée.']); exit; }
array_splice($list, $idx, 1);
file_put_contents($path, json_encode($list, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['ok'=>true,'message'=>'Supprimée']);
