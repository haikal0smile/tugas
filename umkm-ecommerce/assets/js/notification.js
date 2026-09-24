/**
 * Polling notifikasi untuk dashboard admin.
 * Mengambil daftar notifikasi (pesanan baru, stok menipis) setiap 8 detik
 * sehingga admin melihat notifikasi baru tanpa reload halaman.
 */
(function () {
  const bell = document.getElementById('notifBell');
  const dot = document.getElementById('notifDot');
  const panel = document.getElementById('notifPanel');
  const list = document.getElementById('notifList');
  if (!bell || !panel || !list) return;

  const baseUrl = bell.dataset.base || '';

  function render(items) {
    if (!items.length) {
      list.innerHTML = '<div class="notif-empty">Belum ada notifikasi.</div>';
      return;
    }
    list.innerHTML = items.map(function (n) {
      return (
        '<div class="notif-item ' + (n.is_read == 0 ? 'unread' : '') + '">' +
          escapeHtml(n.message) +
          '<span class="t">' + escapeHtml(n.created_at) + '</span>' +
        '</div>'
      );
    }).join('');
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  function poll() {
    fetch(baseUrl + '/api/get-notifications.php')
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.unread > 0) {
          dot.textContent = data.unread;
          dot.style.display = 'inline-block';
        } else {
          dot.style.display = 'none';
        }
        render(data.items || []);
      })
      .catch(function () { /* silent fail; will retry next interval */ });
  }

  bell.addEventListener('click', function (e) {
    e.stopPropagation();
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) {
      fetch(baseUrl + '/api/mark-notifications-read.php', { method: 'POST' })
        .then(function () {
          dot.style.display = 'none';
        });
    }
  });

  document.addEventListener('click', function (e) {
    if (!panel.contains(e.target) && e.target !== bell) {
      panel.classList.remove('open');
    }
  });

  poll();
  setInterval(poll, 8000);
})();
