async function renderStock(){
  const target = document.createElement('div');
  target.innerHTML = `
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Stock (produits & services)</h2>
      <button id="add" class="px-3 py-2 bg-black text-white rounded-lg text-sm">Ajouter</button>
    </div>
    <div class="mt-3 overflow-x-auto bg-white border rounded-xl">
      <table class="table text-sm" id="tbl">
        <thead><tr>
          <th>Nom</th><th>Type</th><th>Prix HT</th><th>Unité</th><th></th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
  `;
  view.innerHTML=''; view.appendChild(target);

  const tbody = target.querySelector('tbody');
  function draw(){
    tbody.innerHTML = state.products.map(p=>`<tr>
      <td>${p.name}</td>
      <td>${p.type==='product'?'Produit':'Service'}</td>
      <td>${money(p.priceHT||0)}</td>
      <td>${p.unit||'U'}</td>
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
    if(e.target.classList.contains('edit')) openForm(state.products.find(x=>x.id===id));
    if(e.target.classList.contains('delete')){
      if(confirm('Supprimer cet article ?')){
        const idx = state.products.findIndex(x=>x.id===id);
        state.products.splice(idx,1);
        await apiWrite('products', state.products);
        draw();
      }
    }
  });

  function openForm(p=null){
    const isEdit = !!p;
    const data = p ? {...p} : {id: nextId(state.products), name:'', type:'service', priceHT:0, unit:'U'};
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/40 z-50 flex items-center justify-center';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-4 w-[560px] max-w-[95vw]">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-semibold">${isEdit?'Modifier':'Ajouter'} un article</h3>
          <button class="p-2 border rounded close"><i class="lucide-x"></i></button>
        </div>
        <form id="f" class="grid grid-cols-2 gap-3">
          <input class="border rounded p-2 col-span-2" name="name" placeholder="Nom" value="${data.name}" required />
          <div>
            <label class="text-xs text-gray-500">Type</label>
            <select name="type" class="border rounded p-2 w-full">
              <option value="product" ${data.type==='product'?'selected':''}>Produit</option>
              <option value="service" ${data.type!=='product'?'selected':''}>Service</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-gray-500">Unité</label>
            <input class="border rounded p-2 w-full" name="unit" value="${data.unit||'U'}" placeholder="U, H, etc"/>
          </div>
          <div class="col-span-2">
            <label class="text-xs text-gray-500">Prix HT</label>
            <input class="border rounded p-2 w-full" name="priceHT" type="number" step="0.01" value="${data.priceHT||0}"/>
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
      obj.priceHT = parseFloat(obj.priceHT||'0');
      obj.id = data.id;
      if(isEdit){
        Object.assign(p, obj);
      }else{
        state.products.push(obj);
      }
      await apiWrite('products', state.products);
      modal.remove(); draw();
    };
  }
}
