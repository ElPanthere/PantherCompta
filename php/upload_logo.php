<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $uploadDir = '../data/';
    $uploadFile = $uploadDir . 'logo.png';  // Nous utilisons toujours "logo.png" comme nom de fichier

    // Vérifier si l'extension est autorisée
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $fileExtension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);

    if (in_array(strtolower($fileExtension), $allowedExtensions)) {
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadFile)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Échec du téléchargement du fichier.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Format de fichier non autorisé.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Aucun fichier reçu.']);
}
?>
