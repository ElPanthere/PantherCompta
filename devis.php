<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Devis</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="navbar">
    <h1>Devis</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="factures">
    <div class="toolbar">
      <input type="text" id="search" placeholder="Rechercher un Devis...">
      <button onclick="ajouterDevis()">Nouveau devis</button>
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
      <tbody id="devisTable">
        <!-- Lignes dynamiques -->
      </tbody>
    </table>
  </main>

  <script>
async function chargerDevis() {
  const res = await fetch('data/devis.json');
  let devis = [];

  try {
    devis = await res.json();
  } catch (e) {
    console.error("Erreur de parsing JSON :", e);
    return;
  }

  const tbody = document.getElementById('devisTable');
  tbody.innerHTML = '';

devis.forEach(devis => {
  if (!devis || !devis.id) {
    console.warn("Devis ignorée :", devis);
    return;
  }

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${devis.id}</td>
    <td>${devis.client}</td>
    <td>${devis.date || ''}</td>
    <td>${devis.montant} €</td>
    <td>${devis.statut || 'en attente'}</td>
    <td>
      <button onclick="voirDevis(${devis.id})">Voir</button>
      <button onclick="modifierDevis(${devis.id})">Modifier</button>
      <button onclick="supprimerDevis(${devis.id})" style="background-color: #ff6961;">Supprimer</button>
    </td>`;
  document.getElementById('devisTable').appendChild(tr);
});

}


    function ajouterDevis() {
      window.location.href = 'devis-form.php';
    }

    function voirDevis(id) {
      window.location.href = 'devis-view.html?id=' + id;
    }

    function modifierDevis(id) {
      window.location.href = 'devis-form.php?id=' + id;
    }

async function supprimerDevis(id) {
  console.log("🔥 SUPPRESSION Devis DEMANDÉE POUR ID :", id);

  const res = await fetch('php/delete_devis.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  });

  const result = await res.json();
  console.log("💾 Réponse du serveur :", result);
  chargerDevis();
}




  chargerDevis();
  </script>
</body>
</html>