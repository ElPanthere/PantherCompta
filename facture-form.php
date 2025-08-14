<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Créer / Modifier Facture</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .articles-table { margin-top: 2rem; width: 100%; border-collapse: collapse; }
    .articles-table th, .articles-table td { border: 1px solid #ccc; padding: 8px; }
    .articles-table th { background: #f0f0f0; }
  </style>
</head>
<body>
  <header class="navbar">
    <h1>Formulaire Facture</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="facture-form">
    <form id="factureForm">
      <label>Client :</label>
      <select name="client" id="clientSelect" required>
        <option value="">-- Sélectionner un client --</option>
      </select>

      <label>Date :</label>
      <input type="date" name="date">

      <label>Statut :</label>
      <select name="statut">
        <option value="payée">Payée</option>
        <option value="en attente">En attente</option>
        <option value="en retard">En retard</option>
      </select>

      <label>Notes :</label>
      <textarea name="notes"></textarea>

      <h2>Articles</h2>

      <label>Article existant :</label>
      <select id="articleSelect" onchange="remplirDepuisArticle()">
        <option value="">-- Choisir un article --</option>
      </select>

      <div>
        <input type="text" id="articleNom" placeholder="Nom">
        <select id="articleType">
          <option value="service">Service</option>
          <option value="produit">Produit</option>
        </select>
        <input type="number" step="0.01" id="articlePrix" placeholder="Prix">
        <input type="number" id="articleQuantite" placeholder="Quantité">
        <input type="text" id="articleUnite" placeholder="Unité (H, Unitaire...)">
        <input type="number" step="0.01" id="remise" placeholder="Remise (%)">
        <button type="button" onclick="ajouterArticle()">Ajouter</button>
      </div>

      <table class="articles-table" id="articlesTable">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Type</th>
            <th>Prix unit.</th>
            <th>Quantité</th>
            <th>Unité</th>
            <th>Remise</th>
            <th>Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <label>Montant total :</label>
      <input type="number" step="0.01" name="montant" required readonly>

      <div class="actions">
        <button type="submit">Enregistrer</button>
        <button type="button" onclick="window.location.href='factures.php'">Annuler</button>
      </div>
    </form>
  </main>

  <script>
    const params = new URLSearchParams(window.location.search);
    const factureId = params.get('id');
    const articles = [];
    let articlesDispo = [];

    async function chargerClients(premierNom = '') {
      const res = await fetch('data/clients.json');
      const clients = await res.json();

      const select = document.getElementById('clientSelect');
      clients.forEach(client => {
        const option = document.createElement('option');
        option.value = client.nom;
        option.textContent = client.nom;
        if (client.nom === premierNom) {
          option.selected = true;
        }
        select.appendChild(option);
      });
    }

    async function chargerArticlesDispo() {
      const res = await fetch('data/articles.json');
      articlesDispo = await res.json();
      const select = document.getElementById('articleSelect');
      articlesDispo.forEach((a, index) => {
        const opt = document.createElement('option');
        opt.value = index;
        opt.textContent = a.nom + ' (' + a.prix + '€ / ' + a.unite + ')';
        select.appendChild(opt);
      });
    }

    function remplirDepuisArticle() {
      const index = document.getElementById('articleSelect').value;
      if (index === '') return;
      const a = articlesDispo[index];
      document.getElementById('articleNom').value = a.nom;
      document.getElementById('articleType').value = a.type;
      document.getElementById('articlePrix').value = a.prix;
      document.getElementById('articleUnite').value = a.unite;
    }

    function ajouterArticle() {
      const nom = document.getElementById('articleNom').value.trim();
      const type = document.getElementById('articleType').value;
      const prix = parseFloat(document.getElementById('articlePrix').value);
      const quantite = parseFloat(document.getElementById('articleQuantite').value);
      const unite = document.getElementById('articleUnite').value.trim();
      const remise = parseFloat(document.getElementById('remise').value) || 0;

      if (!nom || isNaN(prix) || isNaN(quantite) || !unite) return alert("Veuillez remplir tous les champs de l'article");

      // Appliquer la remise si nécessaire
      const total = prix * quantite;
      const totalAvecRemise = total - (total * (remise / 100));

      const article = { nom, type, prix, quantite, unite, remise, totalAvecRemise };
      articles.push(article);
      majTableArticles();
      recalculerMontantTotal();

      document.getElementById('articleNom').value = '';
      document.getElementById('articlePrix').value = '';
      document.getElementById('articleQuantite').value = '';
      document.getElementById('articleUnite').value = '';
      document.getElementById('articleSelect').value = '';
      document.getElementById('remise').value = '';
    }

    function majTableArticles() {
      const tbody = document.querySelector('#articlesTable tbody');
      tbody.innerHTML = '';
      articles.forEach((a, i) => {
        const total = a.totalAvecRemise;
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${a.nom}</td>
          <td>${a.type}</td>
          <td>${a.prix.toFixed(2)} €</td>
          <td>${a.quantite}</td>
          <td>${a.unite}</td>
          <td>${a.remise} %</td>
          <td>${total.toFixed(2)} €</td>
          <td><button type="button" onclick="supprimerArticle(${i})">Supprimer</button></td>
        `;
        tbody.appendChild(tr);
      });
    }

    function supprimerArticle(index) {
      articles.splice(index, 1);
      majTableArticles();
      recalculerMontantTotal();
    }

    function recalculerMontantTotal() {
      const total = articles.reduce((somme, a) => somme + a.totalAvecRemise, 0);
      document.querySelector('[name="montant"]').value = total.toFixed(2);
    }

    async function chargerFacture() {
      if (!factureId) {
        await chargerClients();
        await chargerArticlesDispo();
        return;
      }

      const res = await fetch('data/factures.json');
      const factures = await res.json();
      const facture = factures.find(f => f.id == factureId);
      if (!facture) return;

      await chargerClients(facture.client);
      await chargerArticlesDispo();
      document.querySelector('[name="date"]').value = facture.date || '';
      document.querySelector('[name="statut"]').value = facture.statut || 'en attente';
      document.querySelector('[name="notes"]').value = facture.notes || '';

      // Réinitialisation des articles avant de les ajouter
      articles.length = 0;

      if (facture.articles && Array.isArray(facture.articles)) {
        facture.articles.forEach(a => articles.push(a));
      }

      majTableArticles();
      recalculerMontantTotal();
    }

    document.getElementById('factureForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.target;
      const data = Object.fromEntries(new FormData(form));
      data.articles = articles;

      if (factureId) data.id = Number(factureId);

      const res = await fetch('php/save_facture.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const result = await res.json();
      console.log("Résultat enregistrement :", result);
      window.location.href = 'factures.php';
    });

    chargerFacture();
  </script>
</body>
</html>
