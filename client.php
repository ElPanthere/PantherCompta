<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fiche Client</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="navbar">
    <h1>Fiche Client</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="fiche-client">
    <form id="clientForm">
      <label>Nom :</label>
      <input type="text" name="nom" required>

      <label>Email :</label>
      <input type="email" name="email" required>

      <label>Téléphone :</label>
      <input type="number" name="telephone">
	  
	  <label>SIRET :</label>
      <input type="number" name="siret">

      <label>Adresse postale :</label>
      <input type="text" name="adresse">

      <label>Statut :</label>
      <select name="statut">
        <option value="Actif">Actif</option>
        <option value="Inactif">Inactif</option>
      </select>

      <div class="actions">
        <button type="submit">Enregistrer</button>
        <button type="button" onclick="window.location.href='clients.php'">Annuler</button>
      </div>
    </form>
  </main>

<script>
  const params = new URLSearchParams(window.location.search);
  const clientId = params.get('id');

  async function chargerClient() {
    if (!clientId) return;  // Si pas de clientId, on n'affiche rien
    const res = await fetch('data/clients.json');
    const clients = await res.json();
    const client = clients.find(c => c.id == clientId);
    if (!client) return;

    document.querySelector('[name="nom"]').value = client.nom;
    document.querySelector('[name="email"]').value = client.email;
    document.querySelector('[name="telephone"]').value = client.telephone || '';
    document.querySelector('[name="siret"]').value = client.siret || '';
    document.querySelector('[name="adresse"]').value = client.adresse || '';
    document.querySelector('[name="statut"]').value = client.statut || 'Actif';
  }

  // Fonction pour générer un ID aléatoire
  function generateRandomId() {
    return Math.floor(Math.random() * 10000) + 1; // ID entre 1 et 10000
  }

  document.getElementById('clientForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form));  // Récupère les données du formulaire
    const res = await fetch('data/clients.json');
    const clients = await res.json();

    // Si c'est un client existant (modification), on garde son ID
    if (clientId) {
      const index = clients.findIndex(c => c.id == clientId);
      clients[index] = { id: Number(clientId), ...data };  // On garde l'ID existant et on met à jour les autres données
    } else {
      // Si c'est un nouveau client, on génère un ID aléatoire
      const newId = generateRandomId();
      const newClient = { id: newId, ...data };  // Ajouter le nouvel ID au client
      clients.push(newClient);  // Ajout du client avec son nouvel ID
    }

    // Sauvegarde des clients modifiés ou nouveaux dans le fichier JSON
    await fetch('php/save_client.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(clients)  // Envoie les données avec l'ID
    });

    window.location.href = 'clients.php';  // Redirection après enregistrement
  });

  chargerClient();  // Charger les données du client au démarrage
</script>

</body>
</html>