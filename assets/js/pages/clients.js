async function renderClients(){
  const clients = state.clients;
  const target = document.createElement('div');
  target.innerHTML = `
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Clients</h2>
      <div class="flex items-center gap-2">
        <button id="mailSelected" class="px-3 py-2 border rounded-lg text-sm">Mail aux sélectionnés</button>
        <button id="mailAll" class="px-3 py-2 border rounded-lg text-sm">Mail à tous</button>
        <button id="addClientBtn" class="px-3 py-2 bg-black text-white rounded-lg text-sm">Ajouter</button>
      </div>
    </div>
    <div class="mt-3 overflow-x-auto bg-white border rounded-xl">
      <table class="table text-sm" id="clientsTable">
        <thead><tr>
          <th><input type="checkbox" id="selectAll"></th>
          <th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>SIRET</th><th>TVA</th><th>Actif</th><th></th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
  `;
  view.innerHTML = '';
  view.appendChild(target);

  const tbody = target.querySelector('tbody');
  function draw(){
    tbody.innerHTML = state.clients.map(c=>`<tr>
      <td><input type="checkbox" data-id="${c.id}"></td>
      <td>${c.lastName||''}</td>
      <td>${c.firstName||''}</td>
      <td>${c.email||''}</td>
      <td>${c.phone||''}</td>
      <td>${c.siret||''}</td>
      <td>${c.vatNumber||''}</td>
      <td>${c.active? 'Oui':'Non'}</td>
      <td class="text-right">
        <button class="px-2 py-1 text-xs border rounded edit" data-id="${c.id}">Modifier</button>
        <button class="px-2 py-1 text-xs border rounded delete" data-id="${c.id}">Supprimer</button>
      </td>
    </tr>`).join('');
  }
  draw();

  target.querySelector('#addClientBtn').onclick = ()=> openClientForm();
  tbody.addEventListener('click', async (e)=>{
    const id = Number(e.target.getAttribute('data-id'));
    if(e.target.classList.contains('edit')) openClientForm(state.clients.find(x=>x.id===id));
    if(e.target.classList.contains('delete')){
      if(confirm('Supprimer ce client ?')){
        const idx = state.clients.findIndex(x=>x.id===id);
        state.clients.splice(idx,1);
        await apiWrite('clients', state.clients);
        draw();
      }
    }
  });

  target.querySelector('#selectAll').onchange = (e)=>{
    tbody.querySelectorAll('input[type=checkbox]').forEach(cb=> cb.checked = e.target.checked);
  };
  target.querySelector('#mailAll').onclick = ()=> {
    const emails = state.clients.map(c=>c.email).filter(Boolean).join(',');
    window.location.href = `mailto:?bcc=${encodeURIComponent(emails)}`;
  };
  target.querySelector('#mailSelected').onclick = ()=> {
    const ids = [...tbody.querySelectorAll('input[type=checkbox]:checked')].map(cb=> Number(cb.getAttribute('data-id')));
    const emails = state.clients.filter(c=> ids.includes(c.id)).map(c=>c.email).filter(Boolean).join(',');
    window.location.href = `mailto:?bcc=${encodeURIComponent(emails)}`;
  };

  function openClientForm(client=null){
    const isEdit = !!client;
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/40 z-50 flex items-center justify-center';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-4 w-[680px] max-w-[95vw]">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-semibold">${isEdit? 'Modifier':'Ajouter'} un client</h3>
          <button class="p-2 border rounded close"><i class="lucide-x"></i></button>
        </div>
        <form id="clientForm" class="grid grid-cols-2 gap-3">
          <input class="border rounded p-2" name="lastName" placeholder="Nom" value="${client?.lastName||''}" required />
          <input class="border rounded p-2" name="firstName" placeholder="Prénom" value="${client?.firstName||''}" required />
          <input class="border rounded p-2 col-span-2" name="address" placeholder="Adresse" value="${client?.address||''}" />
          <div class="grid grid-cols-2 gap-2 col-span-2">
            <input class="border rounded p-2" name="postalCode" placeholder="Code postal" value="${client?.postalCode||''}"/>
            <input class="border rounded p-2" name="city" placeholder="Ville" value="${client?.city||''}"/>
          </div>
          <input class="border rounded p-2" name="email" placeholder="Email" type="email" value="${client?.email||''}" />
          <input class="border rounded p-2" name="phone" placeholder="Téléphone" value="${client?.phone||''}" />
          <input class="border rounded p-2" name="siret" placeholder="SIRET (pro)" value="${client?.siret||''}" />
          <input class="border rounded p-2" name="vatNumber" placeholder="N° TVA (pro)" value="${client?.vatNumber||''}" />
          <label class="flex items-center gap-2"><input type="checkbox" name="isPro" ${client?.isPro?'checked':''}/> Pro</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="active" ${client?.active!==false?'checked':''}/> Actif</label>
          <div class="col-span-2 flex justify-end gap-2 mt-2">
            <button type="button" class="px-3 py-2 border rounded close">Annuler</button>
            <button class="px-3 py-2 bg-black text-white rounded">${isEdit?'Enregistrer':'Ajouter'}</button>
          </div>
        </form>
      </div>
    `;
    document.body.appendChild(modal);
    modal.querySelectorAll('.close').forEach(b=> b.onclick = ()=> modal.remove());
    modal.querySelector('#clientForm').onsubmit = async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      const data = Object.fromEntries(fd.entries());
      data.isPro = fd.get('isPro') === 'on';
      data.active = fd.get('active') === 'on';
      if(isEdit){
        Object.assign(client, data);
      } else {
        data.id = nextId(state.clients);
        data.createdAt = new Date().toISOString();
        state.clients.push(data);
      }
      await apiWrite('clients', state.clients);
      modal.remove();
      draw();
    };
  }
}
