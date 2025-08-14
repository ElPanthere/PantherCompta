async function renderSettings(){
  const s = state.settings;
  const target = document.createElement('div');
  target.innerHTML = `
    <h2 class="text-xl font-semibold mb-3">Paramètres</h2>
    <div class="grid md:grid-cols-2 gap-4">
      <div class="card">
        <h3 class="font-medium mb-2">Informations société</h3>
        <form id="company" class="grid grid-cols-2 gap-3">
          <input class="border rounded p-2 col-span-2" name="companyName" placeholder="Nom de l'entreprise" value="${s.companyName||''}" required/>
          <input class="border rounded p-2 col-span-2" name="address1" placeholder="Adresse ligne 1" value="${s.address1||''}"/>
          <input class="border rounded p-2 col-span-2" name="address2" placeholder="Adresse ligne 2" value="${s.address2||''}"/>
          <input class="border rounded p-2" name="postalCode" placeholder="Code postal" value="${s.postalCode||''}"/>
          <input class="border rounded p-2" name="city" placeholder="Ville" value="${s.city||''}"/>
          <input class="border rounded p-2" name="siret" placeholder="SIRET" value="${s.siret||''}"/>
          <input class="border rounded p-2" name="phone" placeholder="Téléphone" value="${s.phone||''}"/>
          <div class="col-span-2 grid grid-cols-3 gap-3">
            <div>
              <label class="text-xs text-gray-500">TVA (%)</label>
              <input class="border rounded p-2 w-full" type="number" step="0.01" name="vatRate" value="${s.vatRate||0}"/>
            </div>
            <div>
              <label class="text-xs text-gray-500">URSSAF Services (%)</label>
              <input class="border rounded p-2 w-full" type="number" step="0.01" name="urssafRateService" value="${s.urssafRateService||22}"/>
            </div>
            <div>
              <label class="text-xs text-gray-500">URSSAF Produits (%)</label>
              <input class="border rounded p-2 w-full" type="number" step="0.01" name="urssafRateProduct" value="${s.urssafRateProduct||12}"/>
            </div>
          </div>
          <div class="col-span-2 flex justify-end gap-2">
            <button class="px-3 py-2 bg-black text-white rounded">Enregistrer</button>
          </div>
        </form>
      </div>

      <div class="card">
        <h3 class="font-medium mb-2">Logo</h3>
        <div class="flex items-center gap-3">
          <img src="${s.logoPath||'assets/img/logo.png'}" class="w-16 h-16 object-contain border rounded" onerror="this.src='assets/img/default-logo.png'"/>
          <form id="logoForm" class="flex items-center gap-2">
            <input type="file" name="logo" accept="image/*" class="border rounded p-2"/>
            <button class="px-3 py-2 border rounded">Téléverser</button>
          </form>
        </div>
      </div>

      <div class="card">
        <h3 class="font-medium mb-2">Sécurité (.htaccess / .htpasswd)</h3>
        <p class="text-sm text-gray-600">Identifiants actuels : utilisateur par défaut <code>admin</code>. Modifiez le mot de passe ci-dessous.</p>
        <form id="pwdForm" class="grid grid-cols-2 gap-3 mt-2">
          <input class="border rounded p-2" name="username" placeholder="Utilisateur" value="admin" required/>
          <input class="border rounded p-2" name="password" placeholder="Nouveau mot de passe" required/>
          <div class="col-span-2 flex justify-end gap-2">
            <button class="px-3 py-2 border rounded">Mettre à jour</button>
          </div>
        </form>
        <div class="text-xs text-gray-500 mt-2">Si votre serveur exige un chemin absolu dans <code>AuthUserFile</code>, éditez <code>.htaccess</code> et remplacez par le chemin affiché : <code id="absPath"></code></div>
      </div>
    </div>
  `;
  view.innerHTML=''; view.appendChild(target);

  target.querySelector('#company').onsubmit = async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    const obj = Object.fromEntries(fd.entries());
    obj.vatRate = parseFloat(obj.vatRate||'0');
    obj.urssafRateService = parseFloat(obj.urssafRateService||'0');
    obj.urssafRateProduct = parseFloat(obj.urssafRateProduct||'0');
    Object.assign(state.settings, obj);
    await apiWrite('settings', state.settings);
    alert('Paramètres enregistrés.');
  };

  target.querySelector('#logoForm').onsubmit = async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('api/upload_logo.php', { method:'POST', body: fd });
    const out = await res.json();
    if(out.ok){ state.settings.logoPath = out.path; await apiWrite('settings', state.settings); renderSettings(); }
    else alert(out.message||'Erreur');
  };

  // fetch absolute path for hint
  fetch('api/abs_path.php').then(r=>r.json()).then(j=>{
    const el = document.getElementById('absPath'); if(el) el.textContent = j.path;
  });

  target.querySelector('#pwdForm').onsubmit = async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('api/update_htpasswd.php', { method:'POST', body: fd });
    const out = await res.json();
    alert(out.message || (out.ok? 'OK':'Erreur'));
  };
}
