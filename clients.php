<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Clients</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="navbar">
    <h1>Clients</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="clients">
    <div class="toolbar">
      <input type="text" id="search" placeholder="Rechercher un client...">
      <button onclick="ajouterClient()">Ajouter un client</button>
    </div>

    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Email</th>
          <th>Téléphone</th>
          <th>Adresse</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="clientTable">
        <!-- Données dynamiques -->
      </tbody>
    </table>
  </main>

  <script>
    async function chargerClients() {
      const res = await fetch('data/clients.json');
      const clients = await res.json();
      const tbody = document.getElementById('clientTable');
      tbody.innerHTML = '';

      clients.forEach(client => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${client.id}</td>
          <td>${client.nom}</td>
          <td>${client.email}</td>
          <td>${client.telephone || ''}</td>
          <td>${client.adresse || ''}</td>
          <td>${client.statut || 'Actif'}</td>
          <td>
            <button onclick="voirClient(${client.id})">Voir</button>
            <button onclick="modifierClient(${client.id})">Modifier</button>
            <button onclick="supprimerClient(${client.id})" style="background-color: #ff6961;">Supprimer</button>
          </td>`;
        tbody.appendChild(tr);
      });
    }

    function ajouterClient() {
      window.location.href = 'client.php';
    }

    function voirClient(id) {
		window.location.href = 'client.php?id=' + id + '&readonly=true';
	}


    function modifierClient(id) {
      window.location.href = 'client.php?id=' + id;
    }

async function supprimerClient(id) {
	if (!confirm("Confirmer la suppression de ce client ?")) return;
  console.log("Suppression id :", id); // log JS

  const response = await fetch('php/delete_client.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })  // ← C’est CETTE LIGNE qui manquait probablement
  });

  const result = await response.json();
  console.log("Résultat suppression :", result);

  chargerClients(); // recharge la liste après suppression
}

	

    chargerClients();
  </script>
</body>
</html>
