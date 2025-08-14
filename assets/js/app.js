// Simple SPA Router + API helpers
const routes = [
  { path: '#/dashboard', name:'Dashboard', icon:'layout-dashboard', render: renderDashboard },
  { path: '#/clients', name:'Clients', icon:'users', render: renderClients },
  { path: '#/invoices', name:'Factures', icon:'file-text', render: renderInvoices },
  { path: '#/quotes', name:'Devis', icon:'file', render: renderQuotes },
  { path: '#/accounting', name:'Comptabilité', icon:'calculator', render: renderAccounting },
  { path: '#/stock', name:'Stock', icon:'boxes', render: renderStock },
  { path: '#/purchases', name:'Achats', icon:'shopping-cart', render: renderPurchases },
  { path: '#/stats', name:'Statistiques', icon:'bar-chart-3', render: renderStats },
  { path: '#/settings', name:'Paramètres', icon:'settings', render: renderSettings },
];

const state = {
  settings: null,
  clients: [],
  products: [],
  invoices: [],
  quotes: [],
  purchases: []
};

const view = document.getElementById('view');
const breadcrumb = document.getElementById('breadcrumb');
const sidebar = document.getElementById('sidebar');
const mobileSidebar = document.getElementById('mobileSidebar');
const yearEl = document.getElementById('year');
yearEl.textContent = new Date().getFullYear();

// Build sidebar
function buildNav() {
  const navHTML = routes.map(r => 
    `<a href="${r.path}" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100" data-path="${r.path}">
      <i class="lucide-${r.icon}"></i><span>${r.name}</span>
    </a>`
  ).join('');
  sidebar.innerHTML = navHTML;
  mobileSidebar.innerHTML = navHTML;
}
buildNav();

// Mobile drawer
const drawer = document.getElementById('mobileDrawer');
document.getElementById('mobileMenuBtn').onclick = ()=> drawer.classList.remove('hidden');
document.getElementById('mobileClose').onclick = ()=> drawer.classList.add('hidden');
mobileSidebar.addEventListener('click', ()=> drawer.classList.add('hidden'));

// Router
window.addEventListener('hashchange', route);
async function route(){
  const r = routes.find(x => x.path === location.hash) || routes[0];
  breadcrumb.textContent = r.name;
  highlightNav(r.path);
  await ensureDataLoaded();
  await r.render();
}
function highlightNav(path){
  [...document.querySelectorAll('#sidebar a, #mobileSidebar a')].forEach(a => {
    a.classList.toggle('bg-gray-100', a.getAttribute('data-path')===path);
  });
}

// API helpers
async function apiRead(name){
  const res = await fetch(`api/read_json.php?file=${encodeURIComponent(name)}`);
  if(!res.ok) throw new Error('read failed');
  return res.json();
}
async function apiWrite(name, data){
  const res = await fetch('api/write_json.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({file: name, data})
  });
  if(!res.ok) throw new Error('write failed');
  return res.json();
}
async function apiDeleteInvoice(id){
  const res = await fetch('api/delete_invoice.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({id})
  });
  return res.json();
}
async function ensureDataLoaded(){
  if(!state.settings) state.settings = await apiRead('settings');
  state.clients = await apiRead('clients');
  state.products = await apiRead('products');
  state.invoices = await apiRead('invoices');
  state.quotes = await apiRead('quotes');
  state.purchases = await apiRead('purchases');
}

// Export / Import
document.getElementById('exportJsonBtn').onclick = async () => {
  const link = document.createElement('a');
  link.href = 'api/export_zip.php';
  link.click();
};
document.getElementById('importZipInput').addEventListener('change', async (e)=>{
  const file = e.target.files[0];
  if(!file) return;
  const fd = new FormData();
  fd.append('zip', file);
  const res = await fetch('api/import_zip.php', { method:'POST', body: fd });
  const ok = await res.json();
  alert(ok.message || 'Import terminé');
  location.reload();
});

// Utils
function money(x){ return (Number(x)||0).toFixed(2)+' €'; }
function nextId(list){ return list.length? Math.max(...list.map(x=>x.id||0))+1 : 1; }
function isoDate(d=new Date()){ return d.toISOString().slice(0,10); }
function monthKey(d){ return d.slice(0,7); }
function generateNumber(type='FAC'){
  const today = new Date();
  const year = today.getFullYear();
  const list = type === 'FAC' ? state.invoices : state.quotes;
  const nums = list.filter(i => String(i.number).startsWith(year+'-')).map(i => Number(String(i.number).split('-')[1]||0));
  const seq = (nums.length? Math.max(...nums) : 0) + 1;
  return `${year}-${String(seq).padStart(4,'0')}`;
}

window.state = state;
window.money = money;
window.nextId = nextId;
window.isoDate = isoDate;
window.generateNumber = generateNumber;

// Start
if(!location.hash) location.hash = '#/dashboard';
route();
