<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <header class="navbar">
    <h1>Tableau de bord</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="dashboard">
    <section class="cards">
      <div class="card" id="card-clients">
        <h2>Clients</h2>
        <p id="nbClients">0</p>
      </div>
      <div class="card" id="card-factures">
        <h2>Factures</h2>
        <p id="nbFactures">0</p>
      </div>
      <div class="card" id="card-revenus">
        <h2>Revenus</h2>
        <p id="revenusTotal">0 €</p>
      </div>
    </section>

    <section class="graph">
      <canvas id="revenusChart" width="400" height="200"></canvas>
    </section>
  </main>

  <script>
    async function loadData() {
      const [clientsRes, facturesRes, transactionsRes] = await Promise.all([
        fetch('data/clients.json'),
        fetch('data/factures.json'),
        fetch('data/transactions.json') // Charger les transactions
      ]);

      const clients = await clientsRes.json();
      const factures = await facturesRes.json();
      const transactions = await transactionsRes.json(); // Transactions

      document.getElementById('nbClients').textContent = clients.length;
      document.getElementById('nbFactures').textContent = factures.length;

      // Calcul des entr�es et sorties
      const entries = {}, exits = {};
      transactions.forEach(t => {
        const date = new Date(t.date || new Date());
        const label = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
        
        if (t.type === "entrée") {
          entries[label] = (entries[label] || 0) + parseFloat(t.montant);
        } else if (t.type === "sortie") {
          exits[label] = (exits[label] || 0) + parseFloat(t.montant);
        }
      });

      // Ajouter les entr�es des factures
      factures.forEach(facture => {
        const date = new Date(facture.date || new Date());
        const label = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
        if (facture.statut === "payée") {
          entries[label] = (entries[label] || 0) + parseFloat(facture.montant);
        }
      });

      const labels = [...new Set([...Object.keys(entries), ...Object.keys(exits)])].sort();
      const dataEntrées = labels.map(l => entries[l] || 0);
      const dataSorties = labels.map(l => exits[l] || 0);
      const dataPerformance = dataEntrées.map((e, i) => e - dataSorties[i]);

      // Calcul des revenus (entr�es - sorties)
      const totalRevenus = dataEntrées.reduce((sum, entry) => sum + entry, 0) - dataSorties.reduce((sum, exit) => sum + exit, 0);
      document.getElementById('revenusTotal').textContent = totalRevenus.toFixed(2) + ' €';

      // Affichage des graphiques (uniquement la performance)
      new Chart(document.getElementById('revenusChart'), {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Performance (Entrées - Sorties)',
              data: dataPerformance,
              borderColor: 'blue',
              backgroundColor: 'rgba(0, 0, 255, 0.3)',
              fill: true,
              tension: 0.3
            }
          ]
        },
        options: {
          responsive: true,
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
    }

    loadData();
  </script>
</body>
</html>
