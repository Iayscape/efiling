const KONSEP_SAMPLE = {
  '{instansi}': 'Dinas Komunikasi dan Informatika Kabupaten Barito Kuala',
  '{INSTANSI}': 'DINAS KOMUNIKASI DAN INFORMATIKA KABUPATEN BARITO KUALA',
  '{media}': 'Koran Barito',
  '{MEDIA}': 'KORAN BARITO',
  '{bulan}': 'Agustus',
  '{BULAN}': 'AGUSTUS',
  '{tahun}': '2026',
  '{TAHUN}': '2026',
};

function fillTokens(text) {
  let out = text || '';
  for (const [token, val] of Object.entries(KONSEP_SAMPLE)) {
    out = out.split(token).join(val);
  }
  return out;
}

function updatePreview(card) {
  const hal = card.querySelector('.konsep-input-hal').value;
  const body = card.querySelector('.konsep-input-body').value;
  const bayar = card.querySelector('.konsep-input-bayar').value;
  card.querySelector('.konsep-preview-hal').textContent = fillTokens(hal) || '-';
  card.querySelector('.konsep-preview-body').textContent = fillTokens(body) || '-';
  card.querySelector('.konsep-preview-bayar').textContent = fillTokens(bayar) || '-';
}

document.querySelectorAll('.konsep-card').forEach(function (card) {
  updatePreview(card);
  card.querySelectorAll('.konsep-input-hal, .konsep-input-body, .konsep-input-bayar').forEach(function (input) {
    input.addEventListener('input', function () { updatePreview(card); });
  });
});
