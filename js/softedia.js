
/* ================== Softédia Overlay JS ==================
   - Toggle sombre/clair persistant
   - Ajout d'un bouton flottant si aucun switch de thème n'existe
   - Option: Ajoute (si souhaité) une topbar Softédia minimaliste si aucune navbar n'est détectée
=================================================================== */

(function(){
  document.addEventListener('DOMContentLoaded', function(){
    try{
      var st = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      if (st === 'dark') document.documentElement.classList.add('dark');
    }catch(e){}

    // Bouton flottant thème si rien dans la page
    if (!document.querySelector('[data-theme-toggle]')){
      var fab = document.createElement('button');
      fab.textContent = 'Thème';
      fab.setAttribute('data-theme-toggle','1');
      Object.assign(fab.style, {
        position:'fixed', right:'16px', bottom:'16px', zIndex:1000,
        padding:'10px 12px', borderRadius:'12px', border:'1px solid #e6ebf2',
        background:'#fff', boxShadow:'0 10px 20px rgba(0,0,0,.12)', fontWeight:'700', cursor:'pointer'
      });
      fab.addEventListener('click', function(){
        document.documentElement.classList.toggle('dark');
        try{ localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light'); }catch(e){}
      });
      document.body.appendChild(fab);
    }

    // Navbar minimale si aucune détectée (optionnel)
    var hasNav = document.querySelector('header, .header, .topbar, .navbar, nav.top, #topbar');
    if (!hasNav){
      var bar = document.createElement('header');
      bar.className = 'topbar';
      bar.innerHTML = '<div class="brand">Tableau</div><nav class="nav">'+
        '<a href="dashboard.html">Dashboard</a>'+
        '<a href="clients.html">Clients</a>'+
        '<a href="factures.html">Factures</a>'+
        '<a href="devis-view.html">Devis</a>'+
        '<a href="facture-view.html">Facture</a>'+
        '<a href="compta.html">Comptabilité</a>'+
        '<a href="stock.html">Stock</a>'+
        '<a href="stats.html">Statistiques</a>'+
        '<a href="parametres.html">Paramètres</a>'+
      '</nav>';
      document.body.prepend(bar);
    }
  });
})();
