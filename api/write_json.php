<?php
header('Content-Type: application/json; charset=utf-8');
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
$allowed = ['clients','products','invoices','quotes','purchases','settings'];
$name = $payload['file'] ?? '';
$data = $payload['data'] ?? null;
if(!in_array($name, $allowed)){ http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Invalid file']); exit; }
$path = __DIR__ . '/../data/' . $name . '.json';
$f = fopen($path, 'c+');
if(!$f){ http_response_code(500); echo json_encode(['ok'=>false,'message'=>'Cannot open file']); exit; }
flock($f, LOCK_EX);
ftruncate($f, 0);
fwrite($f, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
fflush($f); flock($f, LOCK_UN);
fclose($f);
echo json_encode(['ok'=>true]);
