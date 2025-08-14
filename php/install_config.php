<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Récupération des données du formulaire d'installation
    $installPath = $data['installPath'] ?? '';
    $companyName = $data['companyName'] ?? '';
    $companyAddress = $data['companyAddress'] ?? '';
    $companySiret = $data['companySiret'] ?? '';
    $companyTVA = $data['companyTVA'] ?? '';
    $companyCP = $data['companyCP'] ?? '';
    $companyVille = $data['companyVille'] ?? '';
    $adminUsername = $data['adminUsername'] ?? '';
    $adminPassword = $data['adminPassword'] ?? '';

    // Chemin du fichier de configuration et de la société
    $societeFile = '../data/societe.json';
    $configFile = '../data/config.json';
    $htpasswdFile = '../.htpasswd'; // Remplacez par le bon chemin du fichier .htpasswd

    // Préparer les données pour `societe.json`
    $societeData = [
        'nom' => $companyName,
        'adresse' => $companyAddress,
        'cp' => $companyCP,
        'ville' => $companyVille,
        'siret' => $companySiret,
        'tva' => $companyTVA
    ];

    // Sauvegarder les données de la société dans `societe.json`
    if (file_put_contents($societeFile, json_encode($societeData, JSON_PRETTY_PRINT))) {

        // Préparer les données pour `config.json`
        $configData = [
            'installPath' => $installPath,
            'admin' => [
                'username' => $adminUsername,
                'password' => password_hash($adminPassword, PASSWORD_BCRYPT) // On hache le mot de passe admin
            ]
        ];

        // Sauvegarder les données de configuration dans `config.json`
        if (file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT))) {
            // Réécrire .htpasswd avec le nouvel utilisateur et mot de passe
            $htpasswdContent = $adminUsername . ':' . password_hash($adminPassword, PASSWORD_BCRYPT) . PHP_EOL;

            // Écrire dans le fichier .htpasswd
            if (file_put_contents($htpasswdFile, $htpasswdContent)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'écriture du fichier .htpasswd']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement du fichier de configuration.']);
        }

    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement du fichier de société.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>
