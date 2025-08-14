<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="ainsi">
  <title>Stock</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
    }
    .form-container {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .form-modal {
      background-color: #fff;
      padding: 30px;
      border-radius: 8px;
      max-width: 600px;
      width: 100%;
      box-sizing: border-box;
    }

    .form-modal h2 {
      text-align: center;
    }

    .form-modal input, .form-modal select, .form-modal button {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      box-sizing: border-box;
    }

    .btn-close {
      background-color: #ff6961;
      color: white;
      padding: 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .btn-close:hover {
      background-color: #ff4c4c;
    }

    .btn-add {
      background-color: #007bff;
      color: white;
      padding: 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .btn-add:hover {
      background-color: #0056b3;
    }

    .stock-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .stock-table th, .stock-table td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: center;
    }

    .btn-delete {
      background-color: #ff6961;
      color: white;
      padding: 5px 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .btn-delete:hover {
      background-color: #ff4c4c;
    }
  </style>
</head>
<body>

  <header class="navbar">
    <h1>Stock</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="stock">
    <section class="stock-list">
      <h2>Articles et Services</h2>
      <table class="stock-table" id="stockTable">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Type</th>
            <th>Prix</th>
            <th>UnitÃ©</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="stockList">
          <!-- Articles dynamiques ici -->
        </tbody>
      </table>
      <button onclick="ouvrirFormulaire()" class="btn-add">Ajouter un article/service</button>
    </section>
  </main>

  <!-- Formulaire Modal -->
  <div class="form-container" id="formContainer">
    <div class="form-modal">
      <h2>Ajouter un article/service</h2>
      <form id="formArticle" onsubmit="ajouterArticle(event)">
        <input type="text" id="nom" placeholder="Nom" required>
        <select id="type" required>
          <option value="">Choisir type</option>
          <option value="service">Service</option>
          <option value="bien">Bien</option>
        </select>
        <input type="number" id="prix" placeholder="Prix" step="0.01" required>
        <input type="text" id="unite" placeholder="UnitÃ©" required>
        <button type="submit" class="btn-add">Ajouter</button>
      </form>
      <button class="btn-close" onclick="fermerFormulaire()">Fermer</button>
    </div>
  </div>

  <script>
    let stockData = []; // Tableau pour stocker les articles

    // Charger les articles depuis le fichier JSON
    async function chargerStock() {
      const response = await fetch('data/articles.json');
      stockData = await response.json();

      afficherStock();
    }

    // Afficher les articles dans le tableau
    function afficherStock() {
      const stockList = document.getElementById('stockList');
      stockList.innerHTML = '';

      stockData.forEach((article, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${article.nom}</td>
          <td>${article.type}</td>
          <td>${article.prix} â‚¬</td>
          <td>${article.unite}</td>
          <td>
            <button class="btn-delete" onclick="supprimerArticle(${index})">Supprimer</button>
            <button class="btn-add" onclick="modifierArticle(${index})">Modifier</button>
          </td>
        `;
        stockList.appendChild(tr);
      });
    }

    // Ouvrir le formulaire d'ajout
    function ouvrirFormulaire() {
      document.getElementById('formContainer').style.display = 'flex';
      document.body.style.overflow = 'hidden'; // Désactive le scroll pendant l'ouverture du formulaire
    }

    // Fermer le formulaire
    function fermerFormulaire() {
      document.getElementById('formContainer').style.display = 'none';
      document.body.style.overflow = 'auto'; // Réactive le scroll
    }

    // Ajouter un article au tableau et sauvegarder dans le JSON
    async function ajouterArticle(event) {
      event.preventDefault();

      const nom = document.getElementById('nom').value;
      const type = document.getElementById('type').value;
      const prix = parseFloat(document.getElementById('prix').value);
      const unite = document.getElementById('unite').value;

      if (nom && type && prix && unite) {
        const nouveauArticle = { nom, type, prix, unite };
        stockData.push(nouveauArticle);
        await sauvegarderStock();
        afficherStock();
        fermerFormulaire();
      } else {
        alert('Tous les champs doivent Ãªtre remplis');
      }
    }

    // Supprimer un article
    async function supprimerArticle(index) {
      stockData.splice(index, 1);
      await sauvegarderStock();
      afficherStock();
    }

    // Modifier un article
    function modifierArticle(index) {
      const article = stockData[index];
      document.getElementById('nom').value = article.nom;
      document.getElementById('type').value = article.type;
      document.getElementById('prix').value = article.prix;
      document.getElementById('unite').value = article.unite;

      ouvrirFormulaire();
      stockData.splice(index, 1); // Supprimer l'article de la liste pour pouvoir le réajouter modifié
    }

    // Sauvegarder les articles dans le fichier JSON
    async function sauvegarderStock() {
      const response = await fetch('php/save_stock.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(stockData)
      });

      const data = await response.json();
      if (!data.success) {
        alert('Erreur de sauvegarde');
      }
    }

    chargerStock(); // Charger les données au début
  </script>
</body>
</html>
