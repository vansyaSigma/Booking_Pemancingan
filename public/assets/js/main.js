// assets/js/main.js — interaksi UI

function rupiah(n) {
  return 'Rp' + Number(n).toLocaleString('id-ID');
}

// Render grid jam dari endpoint JSON
async function renderSlotGrid(kolamId, tanggal, containerId) {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.innerHTML = '<p class="hint">Memuat slot…</p>';
  try {
    const res = await fetch(`/Booking_Pemancingan/public/api/ketersediaan.php?kolam=${kolamId}&tanggal=${tanggal}`);
    const data = await res.json();
    el.innerHTML = '';
    const row = document.createElement('div');
    row.className = 'float-row';
data.slots.forEach(s => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'float-slot ' + (s.status === 'penuh' ? 'taken' : (s.status === 'tutup' || s.status === 'lewat') ? 'closed' : '');
        btn.dataset.jam = s.jam;
        btn.innerHTML = `<span class="bobber"></span><span class="jam">${s.jam}</span>`;
        if (s.status === 'lewat') btn.title = 'Jam ini sudah lewat.';
        if (s.status === 'kosong') {
        btn.addEventListener('click', () => {
          el.querySelectorAll('.float-slot').forEach(b => b.classList.remove('selected'));
          btn.classList.add('selected');
          const inp = document.getElementById('slot-terpilih');
          if (inp) inp.value = s.jam;
          updateTotal();
        });
      }
      row.appendChild(btn);
    });
    el.appendChild(row);
      if (!data.slots.length) {
        el.innerHTML = '<p class="hint">Belum ada jadwal untuk tanggal ini. Silakan pilih tanggal lain.</p>';
      } else if (data.lewat) {
        const catatan = document.createElement('p');
        catatan.className = 'hint';
        catatan.textContent = 'Jam yang sudah lewat ditandai abu-abu dan tidak bisa dipilih.';
        el.appendChild(catatan);
      }
  } catch (e) {
    el.innerHTML = '<p class="hint">Gagal memuat slot. Coba lagi.</p>';
  }
}

function updateTotal() {
  const harga = parseFloat(document.getElementById('harga')?.value || 0);
  const orang = parseInt(document.getElementById('jumlah_orang')?.value || 1);
  const el = document.getElementById('total-harga');
  if (el) el.textContent = rupiah(harga * orang);
}

document.addEventListener('DOMContentLoaded', () => {
  const j = document.getElementById('jumlah_orang');
  if (j) j.addEventListener('input', updateTotal);
  const tgl = document.getElementById('tanggal');
  if (tgl) tgl.addEventListener('change', () => {
    const kolam = document.getElementById('kolam-id')?.value;
    if (kolam) renderSlotGrid(kolam, tgl.value, 'slot-grid');
  });
});
