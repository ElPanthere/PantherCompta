<?php
// upload_backup.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si les données sont bien envoyées
    if (isset($_POST['filename']) && isset($_POST['fileData'])) {
        $filename = $_POST['filename'];
        $fileData = $_POST['fileData'];

        // Définir le chemin où les fichiers seront enregistrés
        $dataDir = '../data/';  // Changez ce chemin si nécessaire

        // Spécifier les fichiers autorisés à être écrasés
        $allowedFiles = ['societe.json', 'clients.json', 'factures.json', 'transactions.json', 'articles.json'];

        // Vérifier si le fichier est autorisé à être écrit
        if (in_array($filename, $allowedFiles)) {
            // Créer ou écraser le fichier JSON
            $filePath = $dataDir . $filename;

            if (file_put_contents($filePath, $fileData)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'écriture du fichier.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Fichier non autorisé.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Données manquantes.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode HTTP invalide.']);
}
?>
