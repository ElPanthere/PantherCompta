<?php
header('Content-Type: application/json');

if (!isset($_FILES['fichier'])) {
  echo json_encode(["success" => false, "error" => "Aucun fichier reçu."]);
  exit;
}

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

$filename = basename($_FILES['fichier']['name']);
$targetFile = $uploadDir . $filename;

// éviter d'écraser un fichier existant
$counter = 1;
while (file_exists($targetFile)) {
  $filename = pathinfo($filename, PATHINFO_FILENAME) . "_$counter." . pathinfo($filename, PATHINFO_EXTENSION);
  $targetFile = $uploadDir . $filename;
  $counter++;
}

if (move_uploaded_file($_FILES['fichier']['tmp_name'], $targetFile)) {
  echo json_encode(["success" => true, "filename" => $filename]);
} else {
  echo json_encode(["success" => false, "error" => "Erreur de téléchargement."]);
}
