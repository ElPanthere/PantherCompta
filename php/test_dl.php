<?php
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="data.json"');

$data = file_get_contents('../societe.json'); // Le fichier JSON à télécharger

echo $data; // Renvoie le contenu du fichier JSON
?>