    </main>
  </div>
</div>
<script>
(function(){
  var sb = document.getElementById('sidebar');
  var bd = document.getElementById('backdrop');
  var hb = document.getElementById('hamburger');
  function toggle(){ sb.classList.toggle('open'); bd.classList.toggle('show'); }
  if (hb) hb.addEventListener('click', toggle);
  if (bd) bd.addEventListener('click', toggle);

  // Auto-generate slug dari nama (jika ada field terkait & slug kosong)
  var nameEl = document.querySelector('[data-slug-source]');
  var slugEl = document.querySelector('[data-slug-target]');
  if (nameEl && slugEl) {
    nameEl.addEventListener('input', function(){
      if (slugEl.dataset.touched === '1') return;
      slugEl.value = nameEl.value.toLowerCase()
        .replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
    });
    slugEl.addEventListener('input', function(){ slugEl.dataset.touched = '1'; });
  }

  // Preview gambar sebelum upload
  var fileEl = document.querySelector('[data-image-input]');
  var prevEl = document.querySelector('[data-image-preview]');
  if (fileEl && prevEl) {
    fileEl.addEventListener('change', function(){
      if (fileEl.files && fileEl.files[0]) {
        prevEl.src = URL.createObjectURL(fileEl.files[0]);
      }
    });
  }

  // Konfirmasi hapus
  document.querySelectorAll('form[data-confirm]').forEach(function(f){
    f.addEventListener('submit', function(e){
      if (!confirm(f.getAttribute('data-confirm'))) e.preventDefault();
    });
  });
})();
</script>
</body>
</html>
