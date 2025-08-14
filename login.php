<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    if ($password == 'tonMotDePasseSecurise') {
        $_SESSION['logged_in'] = true;
        header('Location: index.php'); // Redirection vers la page sécurisée
    } else {
        $error = "Mot de passe incorrect.";
    }
}
?>
<form method="POST">
    <input type="password" name="password" required>
    <button type="submit">Se connecter</button>
</form>
<?php if (isset($error)) { echo $error; } ?>
