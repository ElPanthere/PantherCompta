<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Chemin du fichier JSON
$jsonFile = '../data/devis.json';

// Lire les données envoyées
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

// Charger les devis
$devis = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
if (!is_array($devis)) $devis = [];

// Supprimer la facture
$devis = array_filter($devis, function ($f) use ($data) {
    return isset($f['id']) && $f['id'] != $data['id'];
});

// Réécriture
$result = file_put_contents($jsonFile, json_encode(array_values($devis), JSON_PRETTY_PRINT));

// Réponse JSON
echo json_encode(['success' => $result !== false]);
