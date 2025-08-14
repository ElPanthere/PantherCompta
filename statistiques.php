<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Statistiques</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> <!-- Ajouter cette ligne -->
</head>
<body>
  <header class="navbar">
    <h1>Statistiques</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="statistiques">
    <section>
      <label>Du <input type="date" id="dateStart"></label>
      <label>Au <input type="date" id="dateEnd"></label>
      <button onclick="chargerStats()">Filtrer</button>
      <button onclick="exporterXLSX()">Télécharger XLSX</button>
    </section>

    <section>
      <h2>Revenus mensuels</h2>
      <canvas id="revenusChart" width="400" height="200"></canvas>
    </section>
  </main>

  <script>
    // Initialisation de la période par défaut : début de l'année
    const currentYear = new Date().getFullYear();
    document.getElementById('dateStart').value = `${currentYear}-01-01`;
    document.getElementById('dateEnd').value = `${currentYear}-12-31`;

    let chartInstance = null; // Pour garder la référence du graphique

    async function chargerStats() {
      const [facturesRes, transactionsRes] = await Promise.all([
        fetch('data/factures.json'),
        fetch('data/transactions.json') // Charger les transactions
      ]);

      const factures = await facturesRes.json();
      const transactions = await transactionsRes.json(); // Transactions manuelles

      const mois = {};
      const moisSortiesManuelles = {};

      // Filtrer les factures
      factures.forEach(facture => {
        const date = new Date(facture.date || new Date());
        const label = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
        
        // Appliquer le filtre sur les dates
        const start = document.getElementById('dateStart').value;
        const end = document.getElementById('dateEnd').value;

        if (date >= new Date(start) && date <= new Date(end)) {
          mois[label] = (mois[label] || 0) + (parseFloat(facture.montant) || 0);
        }
      });

      // Filtrer les transactions manuelles
      transactions.forEach(t => {
        const date = new Date(t.date || new Date());
        const label = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;

        // Appliquer le filtre sur les dates
        const start = document.getElementById('dateStart').value;
        const end = document.getElementById('dateEnd').value;

        if (t.type === "sortie" && date >= new Date(start) && date <= new Date(end)) {
          moisSortiesManuelles[label] = (moisSortiesManuelles[label] || 0) + parseFloat(t.montant);
        }
      });

      const labels = [...new Set([...Object.keys(mois), ...Object.keys(moisSortiesManuelles)])].sort();
      const dataEntrées = labels.map(l => mois[l] || 0); // Entrées venant des factures
      const dataSortiesManuelles = labels.map(l => moisSortiesManuelles[l] || 0); // Sorties manuelles
      const dataPerformance = dataEntrées.map((e, i) => e - dataSortiesManuelles[i]);

      // Si un graphique existe déjà, on le détruit pour éviter l'erreur du canvas déjà utilisé
      if (chartInstance) {
        chartInstance.destroy();
      }

      // Création du nouveau graphique en barres
      chartInstance = new Chart(document.getElementById('revenusChart'), {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Entrées (vert)',
              data: dataEntrées,
              backgroundColor: 'rgba(75, 192, 192, 0.5)',
              borderColor: 'rgba(75, 192, 192, 1)',
              borderWidth: 1
            },
            {
              label: 'Sorties (rouge)',
              data: dataSortiesManuelles,
              backgroundColor: 'rgba(255, 99, 132, 0.5)',
              borderColor: 'rgba(255, 99, 132, 1)',
              borderWidth: 1
            },
            {
              label: 'Performance (bleu)',
              data: dataPerformance,
              backgroundColor: 'rgba(54, 162, 235, 0.5)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 1
            }
          ]
        },
        options: {
          responsive: true,
          scales: {
            y: { 
              beginAtZero: true 
            }
          }
        }
      });
    }

    // Filtrer les données en fonction des dates
    document.getElementById('dateStart').addEventListener('change', chargerStats);
    document.getElementById('dateEnd').addEventListener('change', chargerStats);

    // Fonction d'export des statistiques en XLSX
    function exporterXLSX() {
      const lignes = chartInstance.data.labels.map((label, index) => ({
        "Date": label,
        "Entrées": chartInstance.data.datasets[0].data[index],
        "Sorties": chartInstance.data.datasets[1].data[index],
        "Performance": chartInstance.data.datasets[2].data[index]
      }));
      
      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.json_to_sheet(lignes);
      XLSX.utils.book_append_sheet(wb, ws, "Statistiques");
      XLSX.writeFile(wb, "statistiques.xlsx");
    }

    chargerStats(); // Charger les statistiques au chargement de la page
  </script>
</body>
</html>
