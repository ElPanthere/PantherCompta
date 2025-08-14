async function renderPurchases(){
  const target = document.createElement('div');
  target.innerHTML = `
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Achats</h2>
      <button id="add" class="px-3 py-2 bg-black text-white rounded-lg text-sm">Ajouter</button>
    </div>
    <div class="mt-3 overflow-x-auto bg-white border rounded-xl">
      <table class="table text-sm" id="tbl">
        <thead><tr>
          <th>Date</th><th>Fournisseur</th><th>Description</th><th>Type</th><th>Montant HT</th><th></th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
  `;
  view.innerHTML=''; view.appendChild(target);

  const tbody = target.querySelector('tbody');
  function draw(){
    tbody.innerHTML = state.purchases.sort((a,b)=> new Date(b.date)-new Date(a.date)).map(p=>`<tr>
      <td>${p.date}</td><td>${p.supplier||''}</td><td>${p.description||''}</td>
      <td>${p.type==='product'?'Produit':'Service'}</td><td>${money(p.amountHT||0)}</td>
      <td class="text-right">
        <button class="px-2 py-1 text-xs border rounded edit" data-id="${p.id}">Modifier</button>
        <button class="px-2 py-1 text-xs border rounded delete" data-id="${p.id}">Supprimer</button>
      </td>
    </tr>`).join('');
  }
  draw();

  target.querySelector('#add').onclick = ()=> openForm();
  tbody.addEventListener('click', async (e)=>{
    const id = Number(e.target.getAttribute('data-id'));
    if(e.target.classList.contains('edit')) openForm(state.purchases.find(x=>x.id===id));
    if(e.target.classList.contains('delete')){
      if(confirm('Supprimer cet achat ?')){
        const idx = state.purchases.findIndex(x=>x.id===id);
        state.purchases.splice(idx,1);
        await apiWrite('purchases', state.purchases);
        draw();
      }
    }
  });

  function openForm(p=null){
    const isEdit = !!p;
    const data = p ? {...p} : {id: nextId(state.purchases), date: isoDate(new Date()), supplier:'', description:'', type:'product', amountHT:0};
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/40 z-50 flex items-center justify-center';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-4 w-[560px] max-w-[95vw]">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-semibold">${isEdit?'Modifier':'Ajouter'} un achat</h3>
          <button class="p-2 border rounded close"><i class="lucide-x"></i></button>
        </div>
        <form id="f" class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs text-gray-500">Date</label>
            <input class="border rounded p-2 w-full" name="date" type="date" value="${data.date}"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Type</label>
            <select class="border rounded p-2 w-full" name="type">
              <option value="product" ${data.type==='product'?'selected':''}>Produit</option>
              <option value="service" ${data.type!=='product'?'selected':''}>Service</option>
            </select>
          </div>
          <input class="border rounded p-2 col-span-2" name="supplier" placeholder="Fournisseur" value="${data.supplier||''}"/>
          <input class="border rounded p-2 col-span-2" name="description" placeholder="Description" value="${data.description||''}"/>
          <div class="col-span-2">
            <label class="text-xs text-gray-500">Montant HT</label>
            <input class="border rounded p-2 w-full" name="amountHT" type="number" step="0.01" value="${data.amountHT||0}"/>
          </div>
          <div class="col-span-2 flex justify-end gap-2 mt-2">
            <button type="button" class="px-3 py-2 border rounded close">Annuler</button>
            <button class="px-3 py-2 bg-black text-white rounded">${isEdit?'Enregistrer':'Ajouter'}</button>
          </div>
        </form>
      </div>
    `;
    document.body.appendChild(modal);
    modal.querySelectorAll('.close').forEach(b=> b.onclick = ()=> modal.remove());
    modal.querySelector('#f').onsubmit = async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      const obj = Object.fromEntries(fd.entries());
      obj.amountHT = parseFloat(obj.amountHT||'0');
      obj.id = data.id;
      if(isEdit){
        Object.assign(p, obj);
      }else{
        state.purchases.push(obj);
      }
      await apiWrite('purchases', state.purchases);
      modal.remove(); draw();
    };
  }
}
