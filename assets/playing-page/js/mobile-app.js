// Mobile App JavaScript
(function() {
  'use strict';

  // Toast notification
  window.showToast = function(msg, duration) {
    duration = duration || 2500;
    var el = document.createElement('div');
    el.className = 'app-toast';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(function() {
      el.remove();
    }, duration);
  };

  // Enroll in course (AJAX)
  window.enrollCourse = function(courseId, btn) {
    if (!btn) btn = document.querySelector('.btn-enroll');
    if (btn) {
      btn.textContent = 'Memproses...';
      btn.disabled = true;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', baseUrl + 'mobile/enroll/' + courseId, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = function() {
      if (xhr.status === 200) {
        try {
          var resp = JSON.parse(xhr.responseText);
          if (resp.success) {
            window.showToast('Berhasil mendaftar!');
            setTimeout(function() { location.reload(); }, 800);
          } else {
            window.showToast(resp.message || 'Gagal mendaftar');
            if (btn) { btn.textContent = 'Daftar Kelas'; btn.disabled = false; }
          }
        } catch(e) {
          window.showToast('Terjadi kesalahan');
          if (btn) { btn.textContent = 'Daftar Kelas'; btn.disabled = false; }
        }
      } else {
        window.showToast('Silakan login terlebih dahulu');
        setTimeout(function() { location.href = baseUrl + 'login'; }, 1000);
      }
    };
    xhr.send();
  };

  // Search
  var searchInput = document.getElementById('mobileSearch');
  if (searchInput) {
    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        var q = searchInput.value.trim();
        if (q) {
          location.href = baseUrl + 'mobile/cari?q=' + encodeURIComponent(q);
        }
      }
    });
  }
})();
