async function renderAccounting(){
  const target = document.createElement('div');
  const settings = state.settings;
  // Compute CA: ALL invoices (paid or not), as per user's request
  const ca = state.invoices.reduce((s,i)=> s + (i.items||[]).reduce((a,it)=>{
    const line = (it.qty||1)*(it.unitPriceHT||0);
    const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                 it.discountType==='amount' ? (it.discountValue||0) : 0;
    return a + (line - disc);
  },0), 0);
  const achats = state.purchases.reduce((s,p)=> s + (p.amountHT||0), 0);

  // URSSAF rates configurable per type
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
  const benefice = ca - urssaf - achats;

  target.innerHTML = `
    <h2 class="text-xl font-semibold mb-3">Comptabilité</h2>
    <div class="grid md:grid-cols-4 gap-4">
      <div class="card"><div class="text-sm text-gray-500">Chiffre d'affaires</div><div class="text-2xl font-semibold">${money(ca)}</div></div>
      <div class="card"><div class="text-sm text-gray-500">Achats</div><div class="text-2xl font-semibold">${money(achats)}</div></div>
      <div class="card"><div class="text-sm text-gray-500">URSSAF estimée</div><div class="text-2xl font-semibold">${money(urssaf)}</div><div class="text-xs text-gray-500">Services ${rateService}% · Produits ${rateProduct}%</div></div>
      <div class="card"><div class="text-sm text-gray-500">Bénéfice estimé</div><div class="text-2xl font-semibold">${money(benefice)}</div></div>
    </div>

    <div class="card mt-4">
      <div class="flex items-center gap-2">
        <div>
          <label class="text-xs text-gray-500">Du</label>
          <input id="from" type="date" class="border rounded p-2"/>
        </div>
        <div>
          <label class="text-xs text-gray-500">Au</label>
          <input id="to" type="date" class="border rounded p-2"/>
        </div>
        <button id="filter" class="px-3 py-2 border rounded self-end">Filtrer</button>
      </div>
      <div id="report" class="mt-3"></div>
    </div>
  `;
  view.innerHTML=''; view.appendChild(target);

  function buildReport(from='', to=''){
    const inRange = (d)=> (!from || d>=from) && (!to || d<=to);
    const rows = [];
    state.invoices.forEach(i=>{
      (i.items||[]).forEach(it=>{
        rows.push({
          date: i.date,
          doc: i.number,
          clientId: i.clientId,
          type: it.type==='product' ? 'Produit' : 'Service',
          name: it.name,
          qty: it.qty||1,
          unit: it.unit||'U',
          unitPrice: it.unitPriceHT||0,
          discount: it.discountType==='percent'? `${it.discountValue||0}%` : it.discountType==='amount'? it.discountValue||0 : 0,
          total: (()=>{
            const line = (it.qty||1)*(it.unitPriceHT||0);
            const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                         it.discountType==='amount' ? (it.discountValue||0) : 0;
            return line - disc;
          })()
        });
      });
    });
    const filtered = rows.filter(r=> inRange(r.date));
    const servicesX = filtered.filter(r=> r.type==='Service').reduce((s,r)=> s+r.total,0);
    const productsX = filtered.filter(r=> r.type==='Produit').reduce((s,r)=> s+r.total,0);

    const table = `
      <table class="table text-sm bg-white rounded-xl border overflow-hidden">
        <thead><tr><th>Date</th><th>Document</th><th>Type</th><th>Désignation</th><th>Qté</th><th>Unité</th><th>PU</th><th>Remise</th><th>Total</th></tr></thead>
        <tbody>
          ${filtered.map(r=>`<tr>
            <td>${r.date}</td><td>${r.doc}</td><td>${r.type}</td><td>${r.name}</td>
            <td>${r.qty}</td><td>${r.unit}</td><td>${money(r.unitPrice)}</td><td>${typeof r.discount==='number'? money(r.discount): r.discount}</td><td>${money(r.total)}</td>
          </tr>`).join('')}
        </tbody>
      </table>
      <div class="mt-3 text-sm">
        À déclarer sur la période : <strong>${money(servicesX)}</strong> de services et <strong>${money(productsX)}</strong> de produits.
      </div>
    `;
    target.querySelector('#report').innerHTML = table;
  }
  buildReport();

  target.querySelector('#filter').onclick = ()=>{
    const from = target.querySelector('#from').value;
    const to = target.querySelector('#to').value;
    buildReport(from,to);
  };
}
