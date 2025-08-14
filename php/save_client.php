<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lire le contenu JSON envoyé
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Valider le tableau reçu
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format invalide']);
    exit;
}

// Chemin absolu du fichier clients.json
$file = '../data/clients.json';

// Sauvegarder
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
