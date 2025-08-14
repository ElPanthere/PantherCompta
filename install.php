<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Installation du logiciel</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="navbar">
    <h1>Installation du logiciel</h1>
  </header>

  <main class="install">
    <h2>Configuration initiale</h2>
    <form id="installForm">
      <label for="installPath">Chemin d'installation :</label>
      <input type="text" id="installPath" required><br><br>

      <label for="companyName">Nom de l'entreprise :</label>
      <input type="text" id="companyName" required><br><br>

      <label for="companyAddress">Adresse de l'entreprise :</label>
      <input type="text" id="companyAddress" required><br><br>

      <label for="companySiret">SIRET :</label>
      <input type="text" id="companySiret" required><br><br>

      <label for="companyCP">Code postal :</label>
      <input type="text" id="companyCP" required><br><br>

      <label for="companyVille">Ville :</label>
      <input type="text" id="companyVille" required><br><br>

      <label for="companyTVA">TVA appliquée (%) :</label>
      <input type="number" id="companyTVA" step="0.01" required><br><br>

      <label for="adminUsername">Nom d'utilisateur (Admin) :</label>
      <input type="text" id="adminUsername" required><br><br>

      <label for="adminPassword">Mot de passe :</label>
      <input type="password" id="adminPassword" required><br><br>

      <label for="adminConfirmPassword">Confirmer le mot de passe :</label>
      <input type="password" id="adminConfirmPassword" required><br><br>

      <button type="submit">Installer</button>
    </form>
  </main>

  <script>
    document.getElementById('installForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const installPath = document.getElementById('installPath').value.trim();
      const companyName = document.getElementById('companyName').value.trim();
      const companyAddress = document.getElementById('companyAddress').value.trim();
      const companySiret = document.getElementById('companySiret').value.trim();
      const companyTVA = document.getElementById('companyTVA').value.trim();
      const companyCP = document.getElementById('companyCP').value.trim();
      const companyVille = document.getElementById('companyVille').value.trim();
      const adminUsername = document.getElementById('adminUsername').value.trim();
      const adminPassword = document.getElementById('adminPassword').value.trim();
      const adminConfirmPassword = document.getElementById('adminConfirmPassword').value.trim();

      if (adminPassword !== adminConfirmPassword) {
        alert("Les mots de passe ne correspondent pas.");
        return;
      }

      const configData = {
        installPath,
        companyName,
        companyAddress,
        companySiret,
        companyTVA,
        companyCP,
        companyVille,
        adminUsername,
        adminPassword
      };

      try {
        const response = await fetch('php/install_config.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(configData)
        });

        const result = await response.json();
        if (result.success) {
          alert('Installation réussie !');
          window.location.href = "index.php"; // Redirige vers la page principale
        } else {
          alert("Erreur lors de l'installation : " + result.error);
        }
      } catch (error) {
        alert("Erreur lors de l'installation : " + error.message);
      }
    });
  </script>
</body>
</html>
