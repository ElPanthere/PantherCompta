<?php
// Récupérer le contenu envoyé en JSON
$input = file_get_contents('php://input');

// Décoder les données JSON
$data = json_decode($input, true);

// Spécifier le chemin du fichier JSON
$filePath = '../data/articles.json';

// Vérifier si le fichier existe
if (file_exists($filePath)) {
    // Lire le fichier JSON existant
    $existingData = file_get_contents($filePath);
    $articles = json_decode($existingData, true);
} else {
    // Si le fichier n'existe pas, initialiser un tableau vide
    $articles = [];
}

// Ajouter les nouveaux articles ou modifier les existants
// Remplacer tout le contenu du fichier par les nouveaux articles
$articles = $data;

// Sauvegarder les données dans le fichier JSON
if (file_put_contents($filePath, json_encode($articles, JSON_PRETTY_PRINT))) {
    // Retourner une réponse JSON avec un message de succès
    echo json_encode(["success" => true]);
} else {
    // Retourner une réponse JSON avec un message d'erreur
    echo json_encode(["success" => false, "error" => "Erreur lors de l'enregistrement des données."]);
}
?>
