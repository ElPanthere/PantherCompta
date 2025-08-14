<?php
// Récupérer les données envoyées par le frontend (format JSON)
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si des données ont été envoyées
if ($data) {
    // Charger le fichier transactions.json
    $filePath = '../data/transactions.json';

    // Lire le fichier JSON actuel
    if (file_exists($filePath)) {
        $transactions = json_decode(file_get_contents($filePath), true);
    } else {
        $transactions = [];  // Si le fichier n'existe pas encore, initialiser un tableau vide
    }

    // Ajouter la nouvelle transaction
    $transactions[] = $data;

    // Enregistrer les données dans le fichier transactions.json
    if (file_put_contents($filePath, json_encode($transactions, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'écriture dans le fichier.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Aucune donnée reçue.']);
}
?>
