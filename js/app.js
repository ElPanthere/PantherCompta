console.log('App.js ready');

// === Refonte toolbar actions ===
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var themeBtn = document.getElementById('btnTheme');
    var printBtn = document.getElementById('btnPrint');
    var pngBtn = document.getElementById('btnPng');

    // Theme toggle (persist)
    try {
      var st = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      if (st === 'dark') document.documentElement.classList.add('dark');
      if (themeBtn) themeBtn.addEventListener('click', function(){
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
      });
    } catch(e){}

    // Print
    if (printBtn) printBtn.addEventListener('click', function(){ window.print(); });

    // PNG export via html2canvas if available
    if (pngBtn && window.html2canvas) {
      pngBtn.addEventListener('click', function(){
        var el = document.getElementById('facture');
        pngBtn.disabled = true;
        html2canvas(el, {scale: 2}).then(function(canvas){
          pngBtn.disabled = false;
          var a = document.createElement('a');
          a.href = canvas.toDataURL('image/png');
          a.download = (document.title || 'document') + '.png';
          a.click();
        }).catch(function(){ pngBtn.disabled = false; alert('Export PNG impossible.'); });
      });
    }
  });
})();
