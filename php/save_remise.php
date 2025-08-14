<?php
// Sauvegarde la remise et réinitialise remises.json si nécessaire
$data = json_decode(file_get_contents('php://input'), true);
$remisesFile = '../data/remises.json';

if (!file_exists($remisesFile)) {
    file_put_contents($remisesFile, json_encode([])); // Crée un fichier vide si il n'existe pas
} else {
    $remises = json_decode(file_get_contents($remisesFile), true);
    if (!is_array($remises)) {
        $remises = []; // Si le fichier n'est pas un tableau valide, on le réinitialise
    }
}

// Ajouter la nouvelle remise
$remises[] = $data;

// Sauvegarder les remises dans le fichier
if (file_put_contents($remisesFile, json_encode($remises, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Échec de l'enregistrement des données."]);
}
?>
