<?php
if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $zipPath = $_FILES['file']['tmp_name'];
    $extractPath = '/var/www/admin-site/data/';

    // Ouvrir le fichier ZIP
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        // Extraire tous les fichiers dans le répertoire de destination
        $zip->extractTo($extractPath);
        $zip->close();
        echo json_encode(["success" => true, "message" => "Fichiers importés avec succès."]);
    } else {
        echo json_encode(["success" => false, "error" => "Échec de l'extraction du fichier ZIP."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Erreur lors de l'upload du fichier."]);
}
?>
