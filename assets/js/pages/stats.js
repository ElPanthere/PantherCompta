async function renderStats(){
  const target = document.createElement('div');
  target.innerHTML = `
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Statistiques</h2>
      <button id="exportXlsx" class="px-3 py-2 border rounded-lg text-sm">Exporter en XLSX</button>
    </div>
    <div class="grid md:grid-cols-3 gap-4 mt-3">
      <div class="card"><canvas id="caChart" height="160"></canvas></div>
      <div class="card"><canvas id="benefChart" height="160"></canvas></div>
      <div class="card"><canvas id="achatsChart" height="160"></canvas></div>
    </div>
  `;
  view.innerHTML=''; view.appendChild(target);

  // monthly sums
  const byMonth = {};
  function add(m,k,v){ byMonth[m] = byMonth[m]||{ca:0, achats:0}; byMonth[m][k] += v; }
  state.invoices.forEach(i=>{
    const m = i.date.slice(0,7);
    const v = (i.items||[]).reduce((a,it)=>{
      const line = (it.qty||1)*(it.unitPriceHT||0);
      const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                   it.discountType==='amount' ? (it.discountValue||0) : 0;
      return a + (line - disc);
    },0);
    add(m,'ca',v);
  });
  state.purchases.forEach(p=> add(p.date.slice(0,7),'achats', p.amountHT||0));
  const labels = Object.keys(byMonth).sort();
  const ca = labels.map(m=> byMonth[m].ca);
  const achats = labels.map(m=> byMonth[m].achats);

  const settings = state.settings;
  const rateService = Number(settings.urssafRateService||22);
  const rateProduct = Number(settings.urssafRateProduct||12);
  const servicesTotal = state.invoices.flatMap(i=> i.items||[]).filter(it=> it.type!=='product')
    .reduce((a,it)=>{
      const line = (it.qty||1)*(it.unitPriceHT||0);
      const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                   it.discountType==='amount' ? (it.discountValue||0) : 0;
      return a + (line - disc);
    },0);
  const productsTotal = state.invoices.flatMap(i=> i.items||[]).filter(it=> it.type==='product')
    .reduce((a,it)=>{
      const line = (it.qty||1)*(it.unitPriceHT||0);
      const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                   it.discountType==='amount' ? (it.discountValue||0) : 0;
      return a + (line - disc);
    },0);
  const urssaf = (servicesTotal * rateService/100) + (productsTotal * rateProduct/100);
  const benef = labels.map((m,i)=> (ca[i] - urssaf/labels.length - achats[i]));

  // charts
  new Chart(document.getElementById('caChart'), {
    type:'line', data: { labels, datasets: [{label:"CA", data: ca}]},
    options:{ responsive:true, maintainAspectRatio:false }
  });
  new Chart(document.getElementById('benefChart'), {
    type:'line', data: { labels, datasets: [{label:"Bénéfice estimé", data: benef}]},
    options:{ responsive:true, maintainAspectRatio:false }
  });
  new Chart(document.getElementById('achatsChart'), {
    type:'bar', data: { labels, datasets: [{label:"Achats", data: achats}]},
    options:{ responsive:true, maintainAspectRatio:false }
  });

  // export XLSX
  document.getElementById('exportXlsx').onclick = ()=>{
    const wb = XLSX.utils.book_new();
    const rows = [['Mois','CA','Achats','Bénéfice estimé']];
    labels.forEach((m,i)=> rows.push([m, ca[i], achats[i], benef[i]]));
    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, 'Statistiques');
    XLSX.writeFile(wb, 'statistiques.xlsx');
  };
}
