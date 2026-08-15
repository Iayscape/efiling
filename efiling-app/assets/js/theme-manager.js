function hslToHex(h, s, l) {
  s /= 100; l /= 100;
  const k = n => (n + h / 30) % 12;
  const a = s * Math.min(l, 1 - l);
  const f = n => l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)));
  const toHex = x => Math.round(255 * x).toString(16).padStart(2, '0');
  return `#${toHex(f(0))}${toHex(f(8))}${toHex(f(4))}`;
}

function srgbToLinear(c) {
  c /= 255;
  return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
}

function relativeLuminance(hex) {
  hex = hex.replace('#', '');
  const r = parseInt(hex.substring(0, 2), 16);
  const g = parseInt(hex.substring(2, 4), 16);
  const b = parseInt(hex.substring(4, 6), 16);
  return 0.2126 * srgbToLinear(r) + 0.7152 * srgbToLinear(g) + 0.0722 * srgbToLinear(b);
}

function contrastRatio(hex1, hex2) {
  const l1 = relativeLuminance(hex1);
  const l2 = relativeLuminance(hex2);
  const lighter = Math.max(l1, l2);
  const darker = Math.min(l1, l2);
  return (lighter + 0.05) / (darker + 0.05);
}

function bestTextColor(bgHex) {
  const withBlack = contrastRatio(bgHex, '#000000');
  const withWhite = contrastRatio(bgHex, '#ffffff');
  return withBlack >= withWhite ? '#000000' : '#ffffff';
}

// Fixed, pre-verified contrast-safe base pairs (all >= 8:1 for text vs bg & surface)
const GROUP_BASE = {
  Hitam:  { bg: '#09090B', surface: '#131316', surfaceHover: '#1c1c20', textMain: '#FFFFFF', textMuted: '#D4D4D8', border: '#2a2a30' },
  Putih:  { bg: '#FAFAFA', surface: '#FFFFFF', surfaceHover: '#F1F1F3', textMain: '#111827', textMuted: '#374151', border: '#E5E7EB' },
  Biru:   { bg: '#0A1128', surface: '#101B3D', surfaceHover: '#172A57', textMain: '#FFFFFF', textMuted: '#CBD5E1', border: '#1E2A4A' },
  Kuning: { bg: '#1C1506', surface: '#2B2209', surfaceHover: '#3A2E0C', textMain: '#FFF7D6', textMuted: '#E8D9A0', border: '#4A3B12' },
  Merah:  { bg: '#420909', surface: '#4a0d0d', surfaceHover: '#551212', textMain: '#FEE2E2', textMuted: '#FCC2C2', border: '#6b1616' },
};

const GROUP_HUE_RANGE = {
  Hitam: [0, 360],
  Putih: [0, 360],
  Biru: [196, 252],
  Kuning: [32, 62],
  Merah: [344, 380],
};

function buildTheme(id, group, label, primary) {
  const base = GROUP_BASE[group];
  return {
    id, group, label,
    vars: {
      '--bg': base.bg, '--surface': base.surface, '--surface-hover': base.surfaceHover,
      '--primary': primary, '--primary-hover': primary, '--primary-text': bestTextColor(primary),
      '--text-main': base.textMain, '--text-muted': base.textMuted, '--border': base.border,
    },
  };
}

function generateThemes() {
  const themes = [];
  Object.keys(GROUP_BASE).forEach(group => {
    const [hueStart, hueEnd] = GROUP_HUE_RANGE[group];
    const count = 24;
    for (let i = 0; i < count; i++) {
      const hue = (hueStart + ((hueEnd - hueStart) / count) * i) % 360;
      const sat = group === 'Putih' ? 62 : 72;
      const light = group === 'Putih' ? 42 : (group === 'Kuning' ? 56 : 52);
      const primary = hslToHex(hue, sat, light);
      themes.push(buildTheme(`${group.toLowerCase()}-${i}`, group, `${group} ${i + 1}`, primary));
    }
  });
  return themes;
}

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
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
    body: `csrf_token=${encodeURIComponent(getCsrfToken())}&theme_id=${encodeURIComponent(theme.id)}&theme_vars=${encodeURIComponent(JSON.stringify(theme.vars))}`
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
  if (!container) return;
  const themes = generateThemes();
  const groups = Object.keys(GROUP_BASE);
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
