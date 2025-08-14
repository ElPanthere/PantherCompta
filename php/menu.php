<?php
// Liste des pages à afficher dans le menu
$pages = [
    'index.php' => 'Dashboard',
    'clients.php' => 'Clients',
    'factures.php' => 'Factures',
    'devis.php' => 'Devis',
    'comptabilite.php' => 'Comptabilité',
    'stocks.php' => 'Stock',
    'statistiques.php' => 'Statistiques',
    'parametres.php' => 'Paramètres', // Nouvelle page ajoutée
];

// Affichage du menu
echo '<nav>';
foreach ($pages as $file => $title) {
    // Détermine la page active pour appliquer une classe "active"
    $class = (basename($_SERVER['PHP_SELF']) == $file) ? 'class="active"' : '';
    echo '<a href="' . $file . '" ' . $class . '>' . $title . '</a>';
}
echo '</nav>';
?>
