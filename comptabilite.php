<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Comptabilité</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <style>
    /* Style de la modale */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5); /* Fond semi-transparent */
      z-index: 999;
      backdrop-filter: blur(5px); /* Flou sur le fond */
    }

    .modal-content {
      position: relative;
      background-color: white;
      padding: 20px;
      border-radius: 5px;
      width: 50%;
      margin: 0 auto;
      top: 50%;
      transform: translateY(-50%);
    }

    /* Bouton pour fermer la modale */
    .close {
      color: #aaa;
      font-size: 28px;
      font-weight: bold;
      position: absolute;
      top: 10px;
      right: 15px;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    /* Modifie l'apparence du fond lorsque la modale est active */
    .modal-active {
      backdrop-filter: blur(5px);
    }
  </style>
</head>
<body>
  <header class="navbar">
    <h1>Comptabilité</h1>
    <nav>
      <?php include('php/menu.php'); ?>
    </nav>
  </header>

  <main class="comptabilite">
    <div class="toolbar filters">
      <label>Du <input type="date" id="dateStart"></label>
      <label>Au <input type="date" id="dateEnd"></label>
      <button onclick="chargerComptabilite()">Filtrer</button>
      <button onclick="exporterXLSX()">Télécharger XLSX</button>
      <button onclick="ouvrirFormulaireTransaction()">Ajouter une opération</button> <!-- Nouveau bouton -->
    </div>

    <table>
      <thead>
        <tr>
          <th>N° Facture</th>
          <th>Date</th>
          <th>Client</th>
          <th>Type</th>
          <th>Total HT</th>
          <th>TVA (%)</th>
          <th>Total TVA</th>
          <th>Total TTC</th>
          <th>Voir</th>
        </tr>
      </thead>
      <tbody id="comptaTable">
        <!-- Donn�es dynamiques -->
      </tbody>
    </table>

    <div class="total" id="totalRevenu">Total : 0.00 €</div>

    <!-- Modale pour ajouter une op�ration -->
    <div id="modalForm" class="modal">
      <div class="modal-content">
        <span class="close" onclick="fermerFormulaireTransaction()">&times;</span>
        <h2>Ajouter une opération</h2>
        <form id="formOperation" onsubmit="ajouterOperation(event)">
          <label>Titre : <input type="text" id="titreOperation" required></label><br><br>
          <label>Type :
            <select id="typeOperation" required>
              <option value="">-- Choisir --</option>
              <option value="entrée">Entrée</option>
              <option value="sortie">Sortie</option>
            </select>
          </label><br><br>
          <label>Date : <input type="date" id="dateOperation" required></label><br><br>
          <label>Montant H.T : <input type="number" id="montantOperation" step="0.5" required></label><br><br>
          <label>Url de la facture : <input type="url" id="urlOperation" required></label><br><br>
          <label>TVA : <input type="number" id="tvaFacture" required></label><br><br>
          <button type="submit">Ajouter</button>
          <button type="button" onclick="fermerFormulaireTransaction()">Fermer</button>
        </form>
      </div>
    </div>
  </main>

<script>
  let factures = [], clients = [], societe = [], transactions = [];
  
  

  // Charger les donn�es depuis le serveur
  async function chargerComptabilite() {
    try {
      const [resF, resC, resS, resT] = await Promise.all([
        fetch("data/factures.json"),
        fetch("data/clients.json"),
        fetch("data/societe.json"),
        fetch("data/transactions.json") // Charger �galement les transactions
      ]);

      factures = await resF.json();
      clients = await resC.json();
      societe = await resS.json();
      transactions = await resT.json(); // Charger les transactions

      const tvaRate = parseFloat(societe.tva || 0);
      const start = document.getElementById("dateStart").value;
      const end = document.getElementById("dateEnd").value;

      const tbody = document.getElementById("comptaTable");
      tbody.innerHTML = "";  // Effacer l'ancien tableau
      let totalGlobal = 0;
      const lignes = [];
	  

      // Filtrer et traiter les factures pay�es
      factures.filter(f => f.statut.toLowerCase() === "payée")
        .filter(f => {
          let factureDate = f.date.split('/').join('-');  // Assurez-vous que les dates sont au m�me format
          if (!start && !end) return true;
          return (!start || factureDate >= start) && (!end || factureDate <= end);
        })
        .forEach(facture => {
          const client = clients.find(c => c.nom === facture.client);
          const nomClient = client ? client.nom : 'Inconnu';

          const totalHT_services = facture.articles.filter(a => a.type === "service").reduce((s, a) => s + a.prix * a.quantite, 0);
          const totalHT_biens = facture.articles.filter(a => a.type === "bien").reduce((s, a) => s + a.prix * a.quantite, 0);

          const totalTVA_services = totalHT_services * (tvaRate / 100);
          const totalTVA_biens = 0; // exon�r�s

          const totalTTC = totalHT_services + totalTVA_services + totalHT_biens;
          totalGlobal += totalTTC;

          [
            { type: "service", ht: totalHT_services, tva: totalTVA_services },
            { type: "bien", ht: totalHT_biens, tva: totalTVA_biens }
          ].forEach(({ type, ht, tva }) => {
            if (ht === 0) return;
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${facture.id}</td>
              <td>${facture.date}</td>
              <td>${nomClient}</td>
              <td>${type}</td>
              <td>${ht.toFixed(2)} €</td>
              <td>${type === 'service' ? tvaRate : 0} %</td>
              <td>${tva.toFixed(2)} €</td>
              <td>${(ht + tva).toFixed(2)} €</td>
              <td><a href="facture-view.html?id=${facture.id}" target="_blank">Voir</a></td>
            `;
            tbody.appendChild(tr);
            lignes.push({
              "N° Facture": facture.id,
              "Date": facture.date,
              "Client": nomClient,
              "Type": type,
              "Total HT": ht,
              "TVA (%)": type === 'service' ? tvaRate : 0,
              "Total TVA": tva,
              "Total TTC": ht + tva
            });
          });
        });

      // Filtrer et traiter les transactions manuelles (entrées et sorties)
      let totalOpTTC = 0;
      transactions.filter(t => {
        let transactionDate = t.date.split('/').join('-');  // Assurez-vous que les dates sont au m�me format
        return (!start || transactionDate >= start) && (!end || transactionDate <= end);
      }).forEach(t => {
        const tr = document.createElement("tr");
        // Calculer le montant TTC avec le signe (+ ou -)
        const totalTTC = t.type === "entrée" ? parseFloat(t.montant) : -parseFloat(t.montant); // N�gatif pour "sortie"
        totalOpTTC += totalTTC;
        tr.innerHTML = `
          <td>-</td>
          <td>${t.date}</td>
          <td>${t.description}</td>  <!-- Affichage du titre dans la colonne Client -->
          <td>${t.type}</td>
          <td>${totalTTC.toFixed(2)} €</td>
          <td>${t.tva} %</td>
          <td>${t.tvaSet} €</td>
          <td>${totalTTC - t.tvaSet} €</td> <!-- Affichage du montant dans la colonne Total TTC -->
          <td><a href=${t.url} target=_Blank>voir</a></td>
        `;
        tbody.appendChild(tr);
      });

      document.getElementById("totalRevenu").textContent = "Total : " + (totalGlobal + totalOpTTC).toFixed(2) + " €";
    } catch (err) {
      console.error("Erreur lors du chargement des données:", err);
    }
  }

  function exporterXLSX() {
    if (!window.lignesXLSX || window.lignesXLSX.length === 0) return alert("Aucune donnée à exporter.");
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(window.lignesXLSX);
    XLSX.utils.book_append_sheet(wb, ws, "Comptabilité");
    XLSX.writeFile(wb, "comptabilite.xlsx");
  }

  // Fonction d'ajout d'op�ration (entr�e ou sortie)
  async function ajouterOperation(e) {
    e.preventDefault();
    const titre = document.getElementById("titreOperation").value.trim();
    const type = document.getElementById("typeOperation").value;
    const date = document.getElementById("dateOperation").value;
    const montant = parseFloat(document.getElementById("montantOperation").value);
    const url = document.getElementById("urlOperation").value;
    const tva = document.getElementById("tvaFacture").value;
	const tvaPrice = (tva/100)*montant;

    if (!titre || !type || !date || isNaN(montant)) return alert("Tous les champs sont requis.");

    const signe = type === "entrée" ? "+" : "-"; // Ajoute un signe selon le type d'op�ration
    const totalTTC = (type === "entrée" ? montant : -montant).toFixed(2); // N�gatif pour "sortie"
    const ligne = {
      date: date,
      type: type,
      categorie: "manuel",
      description: titre,
      montant: montant.toFixed(2),
      totalTTC: totalTTC,
      url: url,
	  tvaSet : tvaPrice.toFixed(2),
	  tva : tva
    };

    // Sauvegarder la transaction dans le fichier JSON
    const finalSave = () => {
      fetch('php/save_transaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(ligne)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Recharger les donn�es apr�s l'ajout pour afficher la nouvelle transaction
          chargerComptabilite();
        } else {
          alert('Erreur d\'ajout de transaction : ' + data.error);
        }
      })
      .catch(err => console.error('Erreur ajout JSON :', err));
    };

    // Ajouter une ligne dans le tableau de comptabilit� sans attendre le rechargement
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>-</td>
      <td>${ligne.date}</td>
      <td>${ligne.description}</td>  <!-- Le titre est maintenant dans la colonne Client -->
      <td>${ligne.type}</td>
      <td>-</td>
      <td>-</td>
      <td>-</td>
      <td>${ligne.totalTTC} €</td> <!-- Le montant va dans la colonne Total TTC -->
    `;
    document.getElementById("comptaTable").appendChild(tr);

    document.getElementById("formOperation").reset(); // R�initialiser le formulaire
    finalSave();
  }

  // Fonction pour ouvrir le formulaire d'ajout d'op�ration
  function ouvrirFormulaireTransaction() {
    document.getElementById("modalForm").style.display = "block";
  }

  // Fonction pour fermer le formulaire d'ajout d'op�ration
  function fermerFormulaireTransaction() {
    document.getElementById("modalForm").style.display = "none";
  }

  chargerComptabilite(); // Charger les donn�es au chargement de la page
</script>
