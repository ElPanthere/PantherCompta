<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$logFile = __DIR__ . '/../data/debug_log.txt';
file_put_contents($logFile, "SCRIPT LANCÉ\n", FILE_APPEND);

// Vérifie que le fichier cible existe
$dataFile = __DIR__ . '/../data/societe.json';
if (!file_exists($dataFile)) {
    file_put_contents($logFile, "FICHIER INEXISTANT : $dataFile\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Fichier introuvable"]);
    exit;
}

// Lire le corps de la requête
$input = file_get_contents('php://input');
file_put_contents($logFile, "INPUT REÇU : $input\n", FILE_APPEND);

if (!$input) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Aucune donnée reçue"]);
    exit;
}

$societe = json_decode($input, true);
file_put_contents($logFile, "JSON DÉCODÉ : " . var_export($societe, true) . "\n", FILE_APPEND);

if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents($logFile, "ERREUR JSON : " . json_last_error_msg() . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "JSON invalide : " . json_last_error_msg()]);
    exit;
}

// Tentative d’écriture
$resultat = file_put_contents($dataFile, json_encode($societe, JSON_PRETTY_PRINT));
file_put_contents($logFile, "RÉSULTAT ÉCRITURE : " . var_export($resultat, true) . "\n", FILE_APPEND);

if ($resultat !== false) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Échec d'écriture dans societe.json"]);
}
