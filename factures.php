<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Factures</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="navbar">
    <h1>Factures</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="factures">
    <div class="toolbar">
      <input type="text" id="search" placeholder="Rechercher une facture...">
      <button onclick="ajouterFacture()">Nouvelle facture</button>
    </div>

    <table>
      <thead>
        <tr>
          <th>N°</th>
          <th>Client</th>
          <th>Date</th>
          <th>Montant</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="factureTable">
        <!-- Lignes dynamiques -->
      </tbody>
    </table>
  </main>

  <script>
async function chargerFactures() {
  const res = await fetch('data/factures.json');
  let factures = [];

  try {
    factures = await res.json();
  } catch (e) {
    console.error("Erreur de parsing JSON :", e);
    return;
  }

  const tbody = document.getElementById('factureTable');
  tbody.innerHTML = '';

factures.forEach(facture => {
  if (!facture || !facture.id) {
    console.warn("Facture ignorée :", facture);
    return;
  }

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${facture.id}</td>
    <td>${facture.client}</td>
    <td>${facture.date || ''}</td>
    <td>${facture.montant} €</td>
    <td>${facture.statut || 'en attente'}</td>
    <td>
      <button onclick="voirFacture(${facture.id})">Voir</button>
      <button onclick="modifierFacture(${facture.id})">Modifier</button>
      <button onclick="supprimerFacture(${facture.id})" style="background-color: #ff6961;">Supprimer</button>
    </td>`;
  document.getElementById('factureTable').appendChild(tr);
});

}


    function ajouterFacture() {
      window.location.href = 'facture-form.php';
    }

    function voirFacture(id) {
      window.location.href = 'facture-view.html?id=' + id;
    }

    function modifierFacture(id) {
      window.location.href = 'facture-form.php?id=' + id;
    }

async function supprimerFacture(id) {
  console.log("🔥 SUPPRESSION FACTURE DEMANDÉE POUR ID :", id);

  const res = await fetch('php/delete_facture.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  });

  const result = await res.json();
  console.log("💾 Réponse du serveur :", result);
  chargerFactures();
}




  chargerFactures();
  </script>
</body>
</html>