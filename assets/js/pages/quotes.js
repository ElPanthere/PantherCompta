async function renderQuotes(){
  const target = document.createElement('div');
  target.innerHTML = `
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Devis</h2>
      <div class="flex items-center gap-2">
        <input id="search" class="border rounded-lg px-3 py-2 text-sm" placeholder="Rechercher..."/>
        <button id="add" class="px-3 py-2 bg-black text-white rounded-lg text-sm">Ajouter</button>
      </div>
    </div>
    <div class="mt-3 overflow-x-auto bg-white border rounded-xl">
      <table class="table text-sm" id="tbl">
        <thead><tr>
          <th>N°</th><th>Date</th><th>Client</th><th>Total</th><th></th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
  `;
  view.innerHTML=''; view.appendChild(target);

  const tbody = target.querySelector('tbody');
  function row(i){
    const c = state.clients.find(x=>x.id===i.clientId) || {};
    const total = (i.items||[]).reduce((a,it)=>{
      const line = (it.qty||1)*(it.unitPriceHT||0);
      const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                   it.discountType==='amount' ? (it.discountValue||0) : 0;
      return a + (line - disc);
    },0);
    return `<tr>
      <td>${i.number}</td><td>${i.date}</td><td>${c.lastName||''}</td>
      <td>${money(total)}</td>
      <td class="text-right">
        <button class="px-2 py-1 text-xs border rounded view" data-id="${i.id}">Voir</button>
        <button class="px-2 py-1 text-xs border rounded edit" data-id="${i.id}">Modifier</button>
        <button class="px-2 py-1 text-xs border rounded delete" data-id="${i.id}">Supprimer</button>
        <button class="px-2 py-1 text-xs border rounded toinv" data-id="${i.id}">Transformer en facture</button>
      </td>
    </tr>`;
  }
  function draw(filter=''){
    const list = state.quotes.filter(i=> {
      const c = state.clients.find(x=>x.id===i.clientId) || {};
      const str = `${i.number} ${i.date} ${c.lastName||''}`.toLowerCase();
      return str.includes(filter.toLowerCase());
    }).sort((a,b)=> new Date(b.date)-new Date(a.date));
    tbody.innerHTML = list.map(row).join('');
  }
  draw();

  target.querySelector('#search').oninput = (e)=> draw(e.target.value);
  target.querySelector('#add').onclick = ()=> openQuoteForm();
  tbody.addEventListener('click', async (e)=>{
    const id = Number(e.target.getAttribute('data-id'));
    const q = state.quotes.find(x=>x.id===id);
    if(e.target.classList.contains('view')) openQuoteView(id);
    if(e.target.classList.contains('edit')) openQuoteForm(q);
    if(e.target.classList.contains('delete')){
      if(confirm('Supprimer ce devis ?')){
        const idx = state.quotes.findIndex(x=>x.id===id);
        state.quotes.splice(idx,1);
        await apiWrite('quotes', state.quotes);
        draw();
      }
    }
    if(e.target.classList.contains('toinv')){
      // Create an invoice from the quote
      const inv = {
        id: nextId(state.invoices),
        number: generateNumber('FAC'),
        date: isoDate(new Date()),
        clientId: q.clientId,
        status: 'pending',
        notes: q.notes||'',
        items: q.items||[]
      };
      state.invoices.push(inv);
      await apiWrite('invoices', state.invoices);
      alert('Transformé en facture.');
      location.hash = '#/invoices';
    }
  });

  function openQuoteForm(inv=null){
    const isEdit = !!inv;
    const data = inv ? JSON.parse(JSON.stringify(inv)) : {
      id: nextId(state.quotes),
      number: generateNumber('DEV'),
      date: isoDate(new Date()),
      clientId: state.clients[0]?.id || null,
      notes: '',
      items: []
    };
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/40 z-50 flex items-center justify-center';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-4 w-[980px] max-w-[98vw] max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-semibold">${isEdit?'Modifier':'Nouveau'} devis</h3>
          <button class="p-2 border rounded close"><i class="lucide-x"></i></button>
        </div>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="text-xs text-gray-500">Client</label>
            <select id="client" class="border rounded p-2 w-full">
              ${state.clients.map(c=>`<option value="${c.id}" ${c.id===data.clientId?'selected':''}>${c.lastName} ${c.firstName||''}</option>`).join('')}
            </select>
          </div>
          <div>
            <label class="text-xs text-gray-500">Date</label>
            <input id="date" type="date" class="border rounded p-2 w-full" value="${data.date}"/>
          </div>
          <div class="col-span-3">
            <label class="text-xs text-gray-500">Notes (visible sur le devis)</label>
            <textarea id="notes" class="border rounded p-2 w-full" rows="2">${data.notes||''}</textarea>
          </div>
        </div>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-2">
            <h4 class="font-medium">Lignes</h4>
            <div class="flex gap-2">
              <button id="addStock" class="px-3 py-2 border rounded">Depuis stock</button>
              <button id="addLine" class="px-3 py-2 border rounded">Ligne libre</button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="table text-sm">
              <thead><tr>
                <th>Type</th><th>Désignation</th><th>Qté</th><th>Unité</th><th>PU HT</th><th>Remise</th><th></th>
              </tr></thead>
              <tbody id="lines"></tbody>
            </table>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-3">
          <button class="px-3 py-2 border rounded close">Annuler</button>
          <button id="save" class="px-3 py-2 bg-black text-white rounded">${isEdit?'Enregistrer':'Créer'}</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    modal.querySelectorAll('.close').forEach(b=> b.onclick = ()=> modal.remove());

    const linesTbody = modal.querySelector('#lines');
    function drawLines(){
      linesTbody.innerHTML = data.items.map((it,idx)=>`
        <tr>
          <td>
            <select data-i="${idx}" data-k="type" class="border rounded p-1">
              <option value="product" ${it.type==='product'?'selected':''}>Produit</option>
              <option value="service" ${it.type!=='product'?'selected':''}>Service</option>
            </select>
          </td>
          <td><input class="border rounded p-1 w-80" data-i="${idx}" data-k="name" value="${it.name||''}"></td>
          <td><input type="number" step="0.01" class="border rounded p-1 w-20" data-i="${idx}" data-k="qty" value="${it.qty||1}"></td>
          <td><input class="border rounded p-1 w-16" data-i="${idx}" data-k="unit" value="${it.unit||'U'}"></td>
          <td><input type="number" step="0.01" class="border rounded p-1 w-24" data-i="${idx}" data-k="unitPriceHT" value="${it.unitPriceHT||0}"></td>
          <td>
            <div class="flex items-center gap-1">
              <input type="number" step="0.01" class="border rounded p-1 w-20" data-i="${idx}" data-k="discountValue" value="${it.discountValue||0}">
              <select data-i="${idx}" data-k="discountType" class="border rounded p-1">
                <option value="" ${!it.discountType?'selected':''}>—</option>
                <option value="percent" ${it.discountType==='percent'?'selected':''}>%</option>
                <option value="amount" ${it.discountType==='amount'?'selected':''}>€</option>
              </select>
            </div>
          </td>
          <td class="text-right"><button class="px-2 py-1 text-xs border rounded del" data-i="${idx}">Retirer</button></td>
        </tr>
      `).join('');
    }
    drawLines();

    linesTbody.addEventListener('input', (e)=>{
      const i = Number(e.target.getAttribute('data-i'));
      const k = e.target.getAttribute('data-k');
      if(k==='qty' || k==='unitPriceHT' || k==='discountValue') data.items[i][k] = parseFloat(e.target.value || '0');
      else data.items[i][k] = e.target.value;
    });
    linesTbody.addEventListener('click', (e)=>{
      if(e.target.classList.contains('del')){
        const i = Number(e.target.getAttribute('data-i')); data.items.splice(i,1); drawLines();
      }
    });
    modal.querySelector('#addLine').onclick = ()=>{
      data.items.push({type:'service', name:'', qty:1, unit:'U', unitPriceHT:0, discountType:'', discountValue:0}); drawLines();
    };
    modal.querySelector('#addStock').onclick = ()=>{
      const pick = document.createElement('div');
      pick.className='fixed inset-0 bg-black/40 z-50 flex items-center justify-center';
      pick.innerHTML = `<div class="bg-white rounded-2xl p-4 w-[720px] max-w-[95vw] max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-semibold">Ajouter depuis le stock</h3>
          <button class="p-2 border rounded close2"><i class="lucide-x"></i></button>
        </div>
        <div class="grid grid-cols-3 gap-2">
          ${state.products.map(p=>`<button class="border rounded p-2 text-left add" data-id="${p.id}">
            <div class="font-medium">${p.name}</div>
            <div class="text-xs text-gray-500">${p.type==='product'?'Produit':'Service'} • ${money(p.priceHT||0)} / ${p.unit||'U'}</div>
          </button>`).join('')}
        </div>
      </div>`;
      document.body.appendChild(pick);
      pick.querySelector('.close2').onclick = ()=> pick.remove();
      pick.addEventListener('click', (e)=>{
        if(e.target.classList.contains('add')){
          const p = state.products.find(x=> x.id === Number(e.target.getAttribute('data-id')));
          data.items.push({type:p.type, name:p.name, qty:1, unit:p.unit||'U', unitPriceHT:p.priceHT||0, discountType:'', discountValue:0, productId:p.id});
          drawLines(); pick.remove();
        }
      });
    };

    modal.querySelector('#save').onclick = async ()=>{
      data.clientId = Number(modal.querySelector('#client').value);
      data.date = modal.querySelector('#date').value;
      data.notes = modal.querySelector('#notes').value;
      if(isEdit){
        const idx = state.quotes.findIndex(x=>x.id===inv.id);
        state.quotes[idx] = data;
      } else {
        state.quotes.push(data);
      }
      await apiWrite('quotes', state.quotes);
      modal.remove(); renderQuotes();
    };
  }

  function openQuoteView(id){
    const q = state.quotes.find(x=>x.id===id);
    const client = state.clients.find(x=>x.id===q.clientId) || {};
    const settings = state.settings;
    const totalHT = (q.items||[]).reduce((a,it)=>{
      const line = (it.qty||1)*(it.unitPriceHT||0);
      const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                   it.discountType==='amount' ? (it.discountValue||0) : 0;
      return a + (line - disc);
    },0);
    const vat = Number(settings.vatRate||0);
    const totalTTC = totalHT * (1 + vat/100);
    const legal = vat===0 ? 'TVA non applicable, art. 293 B du CGI.' : `TVA ${vat.toFixed(2)}%`;

    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/40 z-50 flex items-center justify-center';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-4 w-[980px] max-w-[98vw] max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-semibold">Devis ${q.number}</h3>
          <div class="flex gap-2">
            <button class="px-3 py-2 border rounded dl">Télécharger PDF</button>
            <button class="p-2 border rounded close"><i class="lucide-x"></i></button>
          </div>
        </div>
        <div id="quote" class="border rounded-2xl p-6">
          <div class="flex items-start justify-between">
            <div>
              <div class="text-2xl font-bold mb-1">${settings.companyName||'Panthère Informatique'}</div>
              <div class="text-sm">${settings.address1||''} ${settings.address2||''}</div>
              <div class="text-sm">${settings.postalCode||''} ${settings.city||''}</div>
              <div class="text-sm">SIRET: ${settings.siret||''}</div>
              <div class="text-sm">Tél: ${settings.phone||''}</div>
            </div>
            <img src="${settings.logoPath||'assets/img/logo.png'}" class="w-20 h-20 object-contain rounded border" onerror="this.src='assets/img/default-logo.png'"/>
          </div>
          <hr class="my-4"/>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <div class="font-medium">Pour</div>
              <div>${client.lastName||''} ${client.firstName||''}</div>
              <div class="text-sm">${client.address||''}</div>
              <div class="text-sm">${client.postalCode||''} ${client.city||''}</div>
              <div class="text-sm">${client.email||''}</div>
            </div>
            <div class="text-right">
              <div>Devis n° <strong>${q.number}</strong></div>
              <div>Date: <strong>${q.date}</strong></div>
            </div>
          </div>
          <table class="table text-sm mt-4">
            <thead><tr><th>Désignation</th><th>Qté</th><th>Unité</th><th>PU HT</th><th>Remise</th><th>Total</th></tr></thead>
            <tbody>
              ${(q.items||[]).map(it=>{
                const line = (it.qty||1)*(it.unitPriceHT||0);
                const disc = it.discountType==='percent' ? line*(it.discountValue||0)/100 :
                             it.discountType==='amount' ? (it.discountValue||0) : 0;
                const total = line - disc;
                const rem = it.discountType==='percent'? `${it.discountValue||0}%` :
                            it.discountType==='amount'? money(it.discountValue||0) : '—';
                return `<tr>
                  <td>${it.name}</td><td>${it.qty}</td><td>${it.unit||'U'}</td>
                  <td>${money(it.unitPriceHT)}</td><td>${rem}</td><td>${money(total)}</td>
                </tr>`
              }).join('')}
            </tbody>
          </table>
          <div class="mt-4 flex flex-col items-end gap-1">
            <div class="text-sm">Total HT: <strong>${money(totalHT)}</strong></div>
            <div class="text-sm">TVA: <strong>${vat.toFixed(2)}%</strong></div>
            <div class="text-lg">Total TTC: <strong>${money(totalTTC)}</strong></div>
            <div class="text-xs text-gray-500 mt-2">${legal}</div>
            ${q.notes? `<div class="text-sm mt-2"><strong>Notes:</strong> ${q.notes}</div>` : ''}
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    modal.querySelector('.close').onclick = ()=> modal.remove();
    modal.querySelector('.dl').onclick = async ()=>{
      const { jsPDF } = window.jspdf;
      const el = modal.querySelector('#quote');
      await html2canvas(el).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF({ unit:'pt', format:'a4' });
        const pageWidth = pdf.internal.pageSize.getWidth();
        const imgWidth = pageWidth - 60;
        const imgHeight = canvas.height * imgWidth / canvas.width;
        pdf.addImage(imgData, 'PNG', 30, 30, imgWidth, imgHeight);
        pdf.save(`Devis-${q.number}.pdf`);
      });
    };
  }
}
