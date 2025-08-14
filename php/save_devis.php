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
$path = '../data/devis.json';

// Charger les factures existantes
$devis = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
if (!is_array($devis)) $devis = [];

// Savoir si c'est une édition ou une création
if (isset($data['id'])) {
    foreach ($devis as &$f) {
        if ($f['id'] == $data['id']) {
            $f = $data;
            break;
        }
    }
} else {
    $data['id'] = count($devis) ? max(array_column($devis, 'id')) + 1 : 1;
    $devis[] = $data;
}

// Enregistrer
file_put_contents($path, json_encode($devis, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
