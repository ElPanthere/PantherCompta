<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Paramètres</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/jszip@3.7.1/dist/jszip.min.js"></script>
</head>
<body>
  <header class="navbar">
    <h1>Paramètres</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="parametres">
<section>
  <h2>Utilisateur</h2>
  <label>Nom d'utilisateur :</label>
  <input type="text" id="username" required>
  <label>Mot de passe :</label>
  <input type="password" id="password" required>
  <label>Confirmer le mot de passe :</label>
  <input type="password" id="confirmPassword" required>
  <button onclick="sauvegarderUtilisateur()">Enregistrer</button>
</section>


    <section>
      <h2>Préférences</h2>
      <label>Devise :</label>
      <select>
        <option value="EUR">Euro (€)</option>
        <option value="USD">Dollar ($)</option>
        <option value="GBP">Livre (£)</option>
      </select>
    </section>

    <section>
      <h2>Entreprise</h2>
      <label>Nom de l'entreprise :</label>
      <input type="text" id="societeNom">
      <label>Adresse :</label>
      <input type="text" id="societeAdresse">
      <label>Code postal :</label>
      <input type="text" id="societeCP">
      <label>Ville :</label>
      <input type="text" id="societeVille">
      <label>Téléphone :</label>
      <input type="text" id="societeTelephone">
      <label>Email :</label>
      <input type="text" id="societeEmail">
      <label>SIRET :</label>
      <input type="text" id="societeSiret">
      <label>TVA appliquée (%) :</label>
      <input type="number" id="societeTVA" step="0.01">
      <button onclick="sauvegarderSociete()">Enregistrer</button>
    </section>
	
	<section>
  <h2>Logo de l'entreprise</h2>
  <form id="logoForm" enctype="multipart/form-data" method="post">
    <label for="logo">Choisir un logo :</label>
    <input type="file" id="logo" name="logo" accept="image/png" required>
    <button type="submit" onclick="uploadLogo()">Enregistrer le logo</button>
  </form>
</section>

    <section>
      <h2>Import / Export</h2>
      <button onclick="createBackup()">Créer une sauvegarde</button>
	<h1>Importer une sauvegarde</h1>
		<input type="file" id="fileInput" accept=".zip">
		<button onclick="importBackup()">Importer la sauvegarde</button>
    </section>
  </main>

  <script>
    async function chargerSociete() {
      try {
        const res = await fetch("data/societe.json");
        const societe = await res.json();
        document.getElementById("societeNom").value = societe.nom || "";
        document.getElementById("societeAdresse").value = societe.adresse || "";
        document.getElementById("societeCP").value = societe.cp || "";
        document.getElementById("societeVille").value = societe.ville || "";
        document.getElementById("societeTelephone").value = societe.telephone || "";
        document.getElementById("societeEmail").value = societe.email || "";
        document.getElementById("societeSiret").value = societe.siret || "";
        document.getElementById("societeTVA").value = societe.tva || "";
      } catch (err) {
        console.error("Erreur chargement societe.json", err);
      }
    }

    window.onload = chargerSociete;

    async function sauvegarderSociete() {
      const donnees = {
        nom: document.getElementById("societeNom").value,
        adresse: document.getElementById("societeAdresse").value,
        cp: document.getElementById("societeCP").value,
        ville: document.getElementById("societeVille").value,
        telephone: document.getElementById("societeTelephone").value,
        email: document.getElementById("societeEmail").value,
        siret: document.getElementById("societeSiret").value,
        tva: document.getElementById("societeTVA").value
      };

      try {
        const res = await fetch("php/save_societe.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(donnees)
        });

        const result = await res.json();
        if (result.success) {
          alert("Informations enregistrées.");
        } else {
          alert("Erreur : " + (result.error || "Échec de l'enregistrement."));
        }
      } catch (err) {
        alert("Erreur serveur : " + err.message);
        console.error("Erreur lors de l'enregistrement :", err);
      }
    }
	
async function sauvegarderUtilisateur() {
  const username = document.getElementById("username").value.trim();  // Récupérer le nom d'utilisateur
  const password = document.getElementById("password").value;        // Récupérer le mot de passe
  const confirmPassword = document.getElementById("confirmPassword").value;  // Confirmer le mot de passe

  if (!username || !password || !confirmPassword) {
    alert("Tous les champs sont requis.");
    return;
  }

  if (password !== confirmPassword) {
    alert("Les mots de passe ne correspondent pas.");
    return;
  }

  try {
    const response = await fetch("php/save_user.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ username, password, confirmPassword })  // Envoyer le nom d'utilisateur et mot de passe
    });

    const result = await response.json();
    if (result.success) {
      alert("Utilisateur enregistré.");
    } else {
      alert("Erreur : " + result.error);
    }
  } catch (error) {
    alert("Erreur serveur : " + error.message);
    console.error("Erreur lors de l'enregistrement", error);
  }
}
function telechargerJson() {
    fetch('php/telecharger_json.php')
    .then(response => response.json())  // Assurer que la réponse est en JSON
    .then(data => {
        if (data.success) {
            window.location.href = data.zip;  // Télécharger le fichier ZIP
        } else {
            alert(data.error);  // Afficher l'erreur
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

function importerZip() {
    const fileInput = document.getElementById('zipFile');
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);

    fetch('php/importer_zip.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Importation réussie");
        } else {
            alert(data.error);
        }
    })
    .catch(err => {
        console.error('Erreur lors de l\'importation:', err);
    });
}

 async function createBackup() {
      // Créer un nouvel objet JSZip
      const zip = new JSZip();

      // Liste des fichiers JSON à inclure dans le ZIP
      const files = ['data/societe.json', 'data/clients.json', 'data/factures.json', 'data/transactions.json', 'data/articles.json'];

      // Ajouter chaque fichier au ZIP
      for (const file of files) {
        const response = await fetch(file);
        const fileContent = await response.text();
        zip.file(file.split('/').pop(), fileContent);
      }

      // Générer le fichier ZIP
      zip.generateAsync({ type: "blob" }).then(function(content) {
        // Créer un lien de téléchargement pour le fichier ZIP
        const link = document.createElement('a');
        link.href = URL.createObjectURL(content);
        link.download = 'backup.zip';  // Nom du fichier ZIP
        link.click();
      });
    }
	
	 async function importBackup() {
      const fileInput = document.getElementById('fileInput');
      const file = fileInput.files[0];

      if (!file) {
        alert("Veuillez sélectionner un fichier ZIP.");
        return;
      }

      const zip = new JSZip();
      
      // Lire le fichier ZIP sélectionné
      const zipData = await file.arrayBuffer();
      zip.loadAsync(zipData).then(function(zip) {
        Object.keys(zip.files).forEach(function(filename) {
          // Extraire les fichiers JSON du ZIP
          zip.files[filename].async("string").then(function(fileData) {
            // Envoi des données au serveur pour remplacer les fichiers existants
            uploadFile(filename, fileData);
          });
        });
      });
    }

    // Fonction pour envoyer le fichier vers le serveur
    async function uploadFile(filename, fileData) {
      const formData = new FormData();
      formData.append('filename', filename);
      formData.append('fileData', fileData);

      try {
        const response = await fetch('php/upload_backup.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();
        if (result.success) {
          alert(`Le fichier ${filename} a été importé et les données ont été mises à jour.`);
        } else {
          alert(`Erreur lors de l'importation de ${filename}.`);
        }
      } catch (error) {
        alert("Erreur serveur : " + error.message);
      }
    }
	
function uploadLogo(event) {
document.getElementById('logoForm').addEventListener('submit', function(event) {
  event.preventDefault(); // Empêche le formulaire de se soumettre normalement

  const formData = new FormData(this);

  fetch('php/upload_logo.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Logo téléchargé avec succès!');
      // Rafraîchir l'image pour qu'elle soit mise à jour
      document.getElementById('logoImage').src = 'data/logo.png?' + new Date().getTime();  // Cache busting
    } else {
      alert('Erreur : ' + data.error);
    }
  });
});

}

  </script>
</body>
</html>