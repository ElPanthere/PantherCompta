<?php
// Vérifier que la méthode de requête est POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données envoyées par le client
    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirmPassword'] ?? '';

    // Vérifier que le mot de passe et la confirmation sont identiques
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'error' => 'Les mots de passe ne correspondent pas.']);
        exit;
    }

    // Hacher le mot de passe avec bcrypt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Path vers votre fichier .htpasswd
    $htpasswdFile = '../.htpasswd'; // Remplacer par le bon chemin absolu

    // Lire le fichier .htpasswd
    $htpasswd = file($htpasswdFile, FILE_IGNORE_NEW_LINES);

    // Chercher et remplacer l'utilisateur existant, ou ajouter un nouveau
    $found = false;
    for ($i = 0; $i < count($htpasswd); $i++) {
        $line = explode(':', $htpasswd[$i]);
        if ($line[0] == $username) {
            // L'utilisateur existe déjà, on remplace le mot de passe
            $htpasswd[$i] = $username . ':' . $hashedPassword;
            $found = true;
            break;
        }
    }

    // Si l'utilisateur n'est pas trouvé, on l'ajoute à la fin
    if (!$found) {
        $htpasswd[] = $username . ':' . $hashedPassword;
    }

    // Sauvegarder le fichier .htpasswd avec les nouvelles données
    if (file_put_contents($htpasswdFile, implode("\n", $htpasswd))) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement du fichier.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>
