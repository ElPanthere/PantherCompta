<?php
// Activer les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Emplacement des fichiers
$logFile = '../php/log.txt';
$jsonFile = '../data/clients.json';

// Log de démarrage
file_put_contents($logFile, "SCRIPT LANCÉ\n", FILE_APPEND);

// Lire les données envoyées
$input = file_get_contents('php://input');
file_put_contents($logFile, "RAW: " . $input . "\n", FILE_APPEND);

$data = json_decode($input, true);

if (!isset($data['id'])) {
    file_put_contents($logFile, "ID manquant\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

// Charger les clients actuels
$clients = json_decode(file_get_contents($jsonFile), true);

// Si le JSON est vide ou cassé, éviter les bugs
if (!is_array($clients)) {
    file_put_contents($logFile, "clients.json invalide ou vide\n", FILE_APPEND);
    $clients = [];
}

// Supprimer le client avec l'ID correspondant
$clients = array_filter($clients, function($c) use ($data) {
    return $c['id'] != $data['id'];
});

file_put_contents($logFile, "Avant écriture JSON : " . json_encode(array_values($clients)) . "\n", FILE_APPEND);

// Écriture finale du fichier JSON mis à jour
$ok = file_put_contents($jsonFile, json_encode(array_values($clients), JSON_PRETTY_PRINT));

file_put_contents($logFile, "Écriture OK ? " . ($ok !== false ? 'oui' : 'non') . "\n", FILE_APPEND);

// Réponse pour le frontend
echo json_encode(['success' => true]);
