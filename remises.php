<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Remises</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="navbar">
    <h1>Gestion des Remises</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="remises">
    <h2>Liste des Remises</h2>
    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Pourcentage</th>
          <th>Article Associé</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="remisesTable">
        <!-- Les remises seront affichées ici -->
      </tbody>
    </table>

    <button onclick="ouvrirFormulaireRemise()">Ajouter une Remise</button>

    <!-- Formulaire d'ajout de remise -->
    <div id="modalForm" class="modal" style="display: none; position: sticky;">
      <div class="modal-content">
        <span class="close" onclick="fermerFormulaireRemise()">&times;</span>
        <h2>Ajouter une Remise</h2>
        <form id="formRemise" onsubmit="ajouterRemise(event)">
          <label>Nom :</label>
          <input type="text" id="nomRemise" required><br><br>
          <label>Pourcentage (%) :</label>
          <input type="number" id="pourcentageRemise" required><br><br>
          <label>Article (optionnel) :</label>
<select id="articleRemise">
  <option value="">-- Sélectionner un article --</option>
</select><br><br>

          <button type="submit">Ajouter</button>
          <button type="button" onclick="fermerFormulaireRemise()">Fermer</button>
        </form>
      </div>
    </div>
  </main>

  <script>
    let remises = [];

    // Charger les remises depuis le fichier JSON
async function chargerRemises() {
  try {
    const [resRemises, resArticles] = await Promise.all([
      fetch('data/remises.json'),
      fetch('data/articles.json')  // Charger les articles
    ]);

    const remises = await resRemises.json();
    const articles = await resArticles.json();

    // Ajouter les articles à la liste déroulante
    const articleSelect = document.getElementById('articleRemise');
    articles.forEach(article => {
      const option = document.createElement('option');
      option.value = article.id;
      option.textContent = article.nom;
      articleSelect.appendChild(option);
    });

    // Affichage des remises dans le tableau
    const tbody = document.getElementById("remisesTable");
    tbody.innerHTML = "";  // Vider le tableau avant de le remplir

    remises.forEach(remise => {
      const tr = document.createElement("tr");
      
      // Chercher l'article correspondant à l'ID de la remise
      let articleNom = 'Appliqué au total'; // Si pas d'article, afficher cette valeur
      if (remise.article) {
        const article = articles.find(a => a.id == remise.article);
        articleNom = article ? article.nom : 'Article inconnu';
      }

      tr.innerHTML = `
        <td>${remise.nom}</td>
        <td>${remise.pourcentage} %</td>
        <td>${articleNom}</td>
        <td>
          <button onclick="supprimerRemise(${remise.id})">Supprimer</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  } catch (error) {
    console.error("Erreur lors du chargement des remises:", error);
  }
}





    // Ajouter une remise
async function ajouterRemise(e) {
  e.preventDefault();

  const nom = document.getElementById("nomRemise").value.trim();
  const pourcentage = parseFloat(document.getElementById("pourcentageRemise").value);
  const article = document.getElementById("articleRemise").value; // Récupère l'ID de l'article

  // Valider les données avant de les envoyer
  if (!nom || isNaN(pourcentage)) {
    alert("Veuillez remplir tous les champs correctement.");
    return;
  }

  const remise = {
    nom,
    pourcentage,
    article: article || null,  // Si l'article est vide, on l'envoie comme null
  };

  try {
    const res = await fetch("php/save_remise.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(remise),
    });

    const result = await res.json();
    if (result.success) {
      alert("Remise ajoutée avec succès!");
      chargerRemises();  // Rafraîchir la liste des remises
    } else {
      alert("Erreur : " + result.error);
    }
  } catch (error) {
    alert("Erreur serveur : " + error.message);
    console.error("Erreur lors de l'ajout de la remise", error);
  }
}



    // Supprimer une remise
    async function supprimerRemise(id) {
      remises = remises.filter(r => r.id !== id);

      // Sauvegarder dans le fichier JSON
      await fetch('php/save_remise.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(remises)
      });

      chargerRemises();
    }

    // Ouvrir le formulaire d'ajout de remise
    function ouvrirFormulaireRemise() {
      document.getElementById('modalForm').style.display = 'block';
    }

    // Fermer le formulaire d'ajout de remise
    function fermerFormulaireRemise() {
      document.getElementById('modalForm').style.display = 'none';
    }

    // Charger les remises au démarrage
    chargerRemises();
  </script>
</body>
</html>
