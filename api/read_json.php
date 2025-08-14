<?php
header('Content-Type: application/json; charset=utf-8');
$allowed = ['clients','products','invoices','quotes','purchases','settings'];
$name = $_GET['file'] ?? '';
if(!in_array($name, $allowed)){ http_response_code(400); echo json_encode(['error'=>'Invalid file']); exit; }
$path = __DIR__ . '/../data/' . $name . '.json';
if(!file_exists($path)){
  // seed defaults
  $seed = [];
  if($name==='settings'){ $seed = [
    'companyName' => 'Panthère Informatique',
    'address1' => '', 'address2' => '',
    'postalCode' => '', 'city' => '',
    'siret' => '', 'phone' => '',
    'vatRate' => 0, 'urssafRateService' => 22, 'urssafRateProduct' => 12,
    'logoPath' => 'assets/img/logo.png'
  ];}
  if(!is_dir(dirname($path))) mkdir(dirname($path), 0775, true);
  file_put_contents($path, json_encode($seed, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
echo file_get_contents($path);
