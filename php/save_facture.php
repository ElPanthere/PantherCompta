<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lire le JSON envoyé
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format invalide']);
    exit;
}

// Chemin absolu
$path = '../data/factures.json';

// Charger les factures existantes
$factures = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
if (!is_array($factures)) $factures = [];

// Savoir si c'est une édition ou une création
if (isset($data['id'])) {
    foreach ($factures as &$f) {
        if ($f['id'] == $data['id']) {
            $f = $data;
            break;
        }
    }
} else {
    $data['id'] = count($factures) ? max(array_column($factures, 'id')) + 1 : 1;
    $factures[] = $data;
}

// Enregistrer
file_put_contents($path, json_encode($factures, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
