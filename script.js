/* Veros Djaya Steel — frontend interactions */
(function () {
  'use strict';

  // Banner "Website Dijual" — tombol tutup
  var fsClose = document.getElementById('forsaleClose');
  var fsBar = document.getElementById('forsaleBar');
  if (fsClose && fsBar) {
    fsClose.addEventListener('click', function () { fsBar.classList.add('hidden'); });
  }

  // Sticky header shadow
  var header = document.getElementById('siteHeader');
  if (header) {
    var onScroll = function () {
      if (window.scrollY > 10) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Mobile nav toggle
  var toggle = document.getElementById('navToggle');
  if (toggle && header) {
    toggle.addEventListener('click', function () {
      header.classList.toggle('nav-open');
    });
    // Tutup menu saat klik link
    header.querySelectorAll('#siteNav a').forEach(function (a) {
      a.addEventListener('click', function () { header.classList.remove('nav-open'); });
    });
  }

  // Smooth scroll untuk anchor internal (#) di halaman yang sama
  var currentFile = location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('a[href*="#"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var href = link.getAttribute('href');
      var hashIndex = href.indexOf('#');
      if (hashIndex < 0) return;
      var linkPath = href.slice(0, hashIndex);
      // Hanya intersepsi bila tautan menuju halaman yang sedang dibuka.
      var samePage = linkPath === '' || linkPath === currentFile;
      var id = href.slice(hashIndex + 1);
      var target = id ? document.getElementById(id) : null;
      if (samePage && target) {
        e.preventDefault();
        var top = target.getBoundingClientRect().top + window.scrollY - 80;
        window.scrollTo({ top: top, behavior: 'smooth' });
        if (header) header.classList.remove('nav-open');
      }
    });
  });
})();
