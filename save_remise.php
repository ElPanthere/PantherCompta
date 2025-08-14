<?php
$file = '../data/test.txt';

// Essayer d'écrire dans un fichier de test
if (file_put_contents($file, 'Test d\'écriture')) {
    echo "Fichier créé avec succès!";
} else {
    echo "Impossible de créer le fichier.";
}
?>
