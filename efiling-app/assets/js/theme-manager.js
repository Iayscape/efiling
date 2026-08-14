function hslToHex(h, s, l) {
  s /= 100; l /= 100;
  const k = n => (n + h / 30) % 12;
  const a = s * Math.min(l, 1 - l);
  const f = n => l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)));
  const toHex = x => Math.round(255 * x).toString(16).padStart(2, '0');
  return `#${toHex(f(0))}${toHex(f(8))}${toHex(f(4))}`;
}

function buildTheme(id, group, label, bg, surface, surfaceHover, primary, primaryHover, textMain, textMuted, border) {
  return { id, group, label, vars: { '--bg': bg, '--surface': surface, '--surface-hover': surfaceHover, '--primary': primary, '--primary-hover': primaryHover, '--text-main': textMain, '--text-muted': textMuted, '--border': border } };
}

function generateThemes() {
  const themes = [];

  // HITAM group - dark backgrounds, varying accent hues (24)
  for (let i = 0; i < 24; i++) {
    const hue = Math.round((360 / 24) * i);
    const primary = hslToHex(hue, 70, 55);
    themes.push(buildTheme(`hitam-${i}`, 'Hitam', `Hitam ${i + 1}`, '#0d0f13', '#161920', '#1e222b', primary, hslToHex(hue, 70, 45), '#f1f2f4', '#8b93a1', '#262b35'));
  }

  // PUTIH group - light backgrounds, varying accent hues (24)
  for (let i = 0; i < 24; i++) {
    const hue = Math.round((360 / 24) * i);
    const primary = hslToHex(hue, 65, 45);
    themes.push(buildTheme(`putih-${i}`, 'Putih', `Putih ${i + 1}`, '#fafafa', '#ffffff', '#f1f2f4', primary, hslToHex(hue, 65, 35), '#111318', '#6b7280', '#e2e4e9'));
  }

  // BIRU group - blue dominant, dark & light variants (26)
  for (let i = 0; i < 13; i++) {
    const hue = 200 + i * 3;
    themes.push(buildTheme(`biru-dark-${i}`, 'Biru', `Biru Gelap ${i + 1}`, '#0a1120', '#111a2e', '#182238', hslToHex(hue, 80, 58), hslToHex(hue, 80, 48), '#eef2ff', '#8b9bc0', '#1f2a44'));
    themes.push(buildTheme(`biru-light-${i}`, 'Biru', `Biru Terang ${i + 1}`, '#f4f7fd', '#ffffff', '#e9f0fb', hslToHex(hue, 75, 45), hslToHex(hue, 75, 35), '#0c1a33', '#5b6c8f', '#dbe4f5'));
  }

  // KUNING group - amber/yellow dominant (24)
  for (let i = 0; i < 12; i++) {
    const hue = 38 + i * 3;
    themes.push(buildTheme(`kuning-dark-${i}`, 'Kuning', `Kuning Gelap ${i + 1}`, '#1a1405', '#241c08', '#332a0e', hslToHex(hue, 85, 55), hslToHex(hue, 85, 45), '#fff8e1', '#b8a978', '#3d3212'));
    themes.push(buildTheme(`kuning-light-${i}`, 'Kuning', `Kuning Terang ${i + 1}`, '#fffbeb', '#ffffff', '#fef3c7', hslToHex(hue, 80, 42), hslToHex(hue, 80, 32), '#241c08', '#8a7a44', '#f5e6b8'));
  }

  // MERAH group - red/crimson dominant (24)
  for (let i = 0; i < 12; i++) {
    const hue = 350 + i * 2;
    themes.push(buildTheme(`merah-dark-${i}`, 'Merah', `Merah Gelap ${i + 1}`, '#1a0808', '#240d0d', '#331414', hslToHex(hue % 360, 78, 55), hslToHex(hue % 360, 78, 45), '#fef2f2', '#c99', '#3d1717'));
    themes.push(buildTheme(`merah-light-${i}`, 'Merah', `Merah Terang ${i + 1}`, '#fef6f6', '#ffffff', '#fde8e8', hslToHex(hue % 360, 72, 45), hslToHex(hue % 360, 72, 35), '#240d0d', '#946060', '#f6d5d5'));
  }

  return themes;
}

function applyTheme(theme) {
  const root = document.documentElement.style;
  Object.entries(theme.vars).forEach(([k, v]) => root.setProperty(k, v));
  document.querySelectorAll('.swatch').forEach(s => s.classList.toggle('active', s.dataset.themeId === theme.id));
  localStorage.setItem('theme_id', theme.id);
  localStorage.setItem('theme_vars', JSON.stringify(theme.vars));
  document.cookie = `theme_vars=${encodeURIComponent(JSON.stringify(theme.vars))}; path=/; max-age=31536000`;
  fetch('/admin/save_theme.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `csrf_token=${encodeURIComponent(window.CSRF_TOKEN || '')}&theme_id=${encodeURIComponent(theme.id)}&theme_vars=${encodeURIComponent(JSON.stringify(theme.vars))}`
  });
}

function applyStoredThemeOnLoad() {
  const stored = localStorage.getItem('theme_vars');
  if (stored) {
    try {
      const vars = JSON.parse(stored);
      Object.entries(vars).forEach(([k, v]) => document.documentElement.style.setProperty(k, v));
    } catch (e) {}
  }
}

function renderThemeGrid(container) {
  const themes = generateThemes();
  const groups = ['Hitam', 'Putih', 'Biru', 'Kuning', 'Merah'];
  const activeId = localStorage.getItem('theme_id');
  groups.forEach(group => {
    const title = document.createElement('div');
    title.className = 'swatch-group-title';
    title.textContent = group;
    container.appendChild(title);
    const grid = document.createElement('div');
    grid.className = 'swatch-grid';
    themes.filter(t => t.group === group).forEach(theme => {
      const btn = document.createElement('button');
      btn.className = 'swatch' + (theme.id === activeId ? ' active' : '');
      btn.style.background = theme.vars['--primary'];
      btn.title = theme.label;
      btn.dataset.themeId = theme.id;
      btn.setAttribute('data-testid', `theme-swatch-${theme.id}`);
      btn.type = 'button';
      btn.addEventListener('click', () => applyTheme(theme));
      grid.appendChild(btn);
    });
    container.appendChild(grid);
  });
}

applyStoredThemeOnLoad();
