const BULAN_NAMA = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

function rupiahFmt(n) {
  n = parseFloat(n) || 0;
  return 'Rp' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ',-';
}

function recalcTotal() {
  var total = 0;
  document.querySelectorAll('.item-harga').forEach(function (input) {
    var v = parseFloat((input.value || '0').replace(/\./g, '').replace(',', '.')) || 0;
    total += v;
  });
  var el = document.getElementById('total-display');
  if (el) el.textContent = rupiahFmt(total);
}

function addItemRow() {
  var tpl = document.getElementById('item-row-template');
  var clone = tpl.content.cloneNode(true);
  document.getElementById('item-rows').appendChild(clone);
  bindRowEvents();
}

function bindRowEvents() {
  document.querySelectorAll('.item-harga').forEach(function (input) {
    input.removeEventListener('input', recalcTotal);
    input.addEventListener('input', recalcTotal);
  });
  document.querySelectorAll('.btn-remove').forEach(function (btn) {
    btn.onclick = function () {
      btn.closest('.item-row').remove();
      recalcTotal();
    };
  });
}

function fillTokens(str) {
  var instansiSel = document.getElementById('instansi_id');
  var mediaSel = document.getElementById('media_id');
  var tanggal = document.getElementById('tanggal').value;
  var bulanLabel = document.getElementById('bulan_label').value;
  var tahun = document.getElementById('tahun').value;
  var instansi = window.INSTANSI_NAMES[instansiSel.value] || '';
  var media = window.MEDIA_NAMES[mediaSel.value] || '';
  var bulanIdx = tanggal ? parseInt(tanggal.split('-')[1], 10) : 0;
  var bulan = bulanLabel || BULAN_NAMA[bulanIdx] || '';
  return str
    .replace(/\{instansi\}/g, instansi).replace(/\{INSTANSI\}/g, instansi.toUpperCase())
    .replace(/\{media\}/g, media).replace(/\{MEDIA\}/g, media.toUpperCase())
    .replace(/\{bulan\}/g, bulan).replace(/\{BULAN\}/g, bulan.toUpperCase())
    .replace(/\{tahun\}/g, tahun);
}

function refillFromTemplate() {
  var jenisId = document.getElementById('jenis_id').value;
  var tpl = window.JENIS_TEMPLATES[jenisId];
  if (!tpl) return;
  document.getElementById('hal').value = fillTokens(tpl.template_hal || '');
  document.getElementById('body').value = fillTokens(tpl.template_body || '');
}

document.addEventListener('DOMContentLoaded', function () {
  bindRowEvents();
  recalcTotal();

  var addBtn = document.getElementById('btn-add-item');
  if (addBtn) addBtn.addEventListener('click', addItemRow);

  var refillBtn = document.getElementById('btn-refill');
  if (refillBtn) refillBtn.addEventListener('click', refillFromTemplate);

  var jenisSel = document.getElementById('jenis_id');
  if (jenisSel) jenisSel.addEventListener('change', refillFromTemplate);

  var tanggalInput = document.getElementById('tanggal');
  if (tanggalInput) {
    tanggalInput.addEventListener('change', function () {
      var parts = tanggalInput.value.split('-');
      if (parts.length === 3) {
        document.getElementById('bulan_label').value = BULAN_NAMA[parseInt(parts[1], 10)];
        document.getElementById('tahun').value = parts[0];
      }
    });
  }

  if (document.getElementById('item-rows') && document.getElementById('item-rows').children.length === 0) {
    addItemRow();
  }
});
