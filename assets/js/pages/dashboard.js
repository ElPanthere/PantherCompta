async function renderDashboard(){
  const { invoices, clients, purchases } = state;
  const thisMonth = new Date().toISOString().slice(0,7);
  const revMonth = invoices.filter(i => i.date.slice(0,7)===thisMonth).reduce((s,i)=> s + (i.items||[]).reduce((a,it)=>{
    const line = (it.qty||1) * (it.unitPriceHT||0);
    const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                 it.discountType==='amount' ? (it.discountValue||0) : 0;
    return a + (line - disc);
  },0), 0);
  const purchMonth = purchases.filter(p => p.date.slice(0,7)===thisMonth).reduce((s,p)=> s + (p.amountHT||0), 0);
  const pending = invoices.filter(i => i.status !== 'paid');
  const lastClients = [...clients].sort((a,b)=> new Date(b.createdAt)-new Date(a.createdAt)).slice(0,5);
  const lastInvoices = [...invoices].sort((a,b)=> new Date(b.date)-new Date(a.date)).slice(0,5);

  view.innerHTML = `
    <div class="grid md:grid-cols-4 gap-4">
      <div class="card"><div class="text-sm text-gray-500">Revenus ce mois</div><div class="text-2xl font-semibold">${money(revMonth)}</div></div>
      <div class="card"><div class="text-sm text-gray-500">Achats ce mois</div><div class="text-2xl font-semibold">${money(purchMonth)}</div></div>
      <div class="card"><div class="text-sm text-gray-500">Factures en attente</div><div class="text-2xl font-semibold">${pending.length}</div></div>
      <div class="card"><div class="text-sm text-gray-500">Clients</div><div class="text-2xl font-semibold">${clients.length}</div></div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mt-4">
      <div class="card">
        <div class="flex items-center justify-between mb-2"><h3 class="font-medium">Derniers clients</h3></div>
        <table class="table text-sm">
          <thead><tr><th>Nom</th><th>Email</th><th>Tél</th><th>Actif</th></tr></thead>
          <tbody>
            ${lastClients.map(c=>`<tr>
              <td>${c.lastName||''} ${c.firstName||''}</td>
              <td>${c.email||''}</td>
              <td>${c.phone||''}</td>
              <td><span class="badge ${c.active?'green':'gray'}">${c.active?'Actif':'Inactif'}</span></td>
            </tr>`).join('')}
          </tbody>
        </table>
      </div>
      <div class="card">
        <div class="flex items-center justify-between mb-2"><h3 class="font-medium">Dernières factures</h3></div>
        <table class="table text-sm">
          <thead><tr><th>N°</th><th>Date</th><th>Client</th><th>Statut</th><th>Total</th></tr></thead>
          <tbody>
            ${lastInvoices.map(i=>{
              const c = state.clients.find(x=>x.id===i.clientId)||{lastName:'—'};
              const total = (i.items||[]).reduce((a,it)=>{
                const line = (it.qty||1)*(it.unitPriceHT||0);
                const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                             it.discountType==='amount' ? (it.discountValue||0) : 0;
                return a + (line - disc);
              },0);
              return `<tr>
                <td>${i.number}</td><td>${i.date}</td><td>${c.lastName||''}</td>
                <td><span class="badge ${i.status==='paid'?'green':'gray'}">${i.status==='paid'?'Payée':'En attente'}</span></td>
                <td>${money(total)}</td>
              </tr>`
            }).join('')}
          </tbody>
        </table>
      </div>
    </div>
  `;
}
