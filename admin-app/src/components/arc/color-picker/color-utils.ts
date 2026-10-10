/**
 * Utilidades matemáticas y de color para el ColorPicker de Arc UI
 * Conversiones puras sin dependencias externas: HEX <-> RGB <-> HSV y cálculo de contraste WCAG.
 */

export interface RGB {
  r: number;
  g: number;
  b: number;
}

export interface HSV {
  h: number; // 0 - 360
  s: number; // 0 - 100
  v: number; // 0 - 100
}

/**
 * Convierte cualquier formato HEX (#RGB o #RRGGBB) a valores RGB numéricos.
 */
export function hexToRgb(hex: string): RGB {
  let clean = hex.replace("#", "").trim();
  if (clean.length === 3) {
    clean = clean[0] + clean[0] + clean[1] + clean[1] + clean[2] + clean[2];
  }
  const num = parseInt(clean, 16);
  if (isNaN(num) || clean.length !== 6) {
    return { r: 119, g: 71, b: 255 }; // Fallback violet
  }
  return {
    r: (num >> 16) & 255,
    g: (num >> 8) & 255,
    b: num & 255,
  };
}

/**
 * Convierte valores RGB a formato HEX estándar en mayúsculas (#RRGGBB).
 */
export function rgbToHex(r: number, g: number, b: number): string {
  const clamp = (v: number) => Math.max(0, Math.min(255, Math.round(v)));
  const toHex = (v: number) => clamp(v).toString(16).padStart(2, "0");
  return `#${toHex(r)}${toHex(g)}${toHex(b)}`.toUpperCase();
}

/**
 * Convierte valores RGB a espacio de color HSV (Hue, Saturation, Value).
 */
export function rgbToHsv(r: number, g: number, b: number): HSV {
  r /= 255;
  g /= 255;
  b /= 255;
  const max = Math.max(r, g, b);
  const min = Math.min(r, g, b);
  const d = max - min;
  let h = 0;
  const s = max === 0 ? 0 : d / max;
  const v = max;

  if (max !== min) {
    switch (max) {
      case r:
        h = (g - b) / d + (g < b ? 6 : 0);
        break;
      case g:
        h = (b - r) / d + 2;
        break;
      case b:
        h = (r - g) / d + 4;
        break;
    }
    h /= 6;
  }
  return {
    h: Math.round(h * 360),
    s: Math.round(s * 100),
    v: Math.round(v * 100),
  };
}

/**
 * Convierte espacio HSV a valores RGB estándar.
 */
export function hsvToRgb(h: number, s: number, v: number): RGB {
  s /= 100;
  v /= 100;
  const i = Math.floor((h / 60) % 6);
  const f = h / 60 - i;
  const p = v * (1 - s);
  const q = v * (1 - f * s);
  const t = v * (1 - (1 - f) * s);

  let r = 0, g = 0, b = 0;
  switch (i) {
    case 0: r = v; g = t; b = p; break;
    case 1: r = q; g = v; b = p; break;
    case 2: r = p; g = v; b = t; break;
    case 3: r = p; g = q; b = v; break;
    case 4: r = t; g = p; b = v; break;
    case 5: r = v; g = p; b = q; break;
  }
  return {
    r: Math.round(r * 255),
    g: Math.round(g * 255),
    b: Math.round(b * 255),
  };
}

/**
 * Calcula la luminosidad relativa según la fórmula estándar de accesibilidad WCAG 2.1.
 * Retorna un valor flotante entre 0.0 (negro absoluto) y 1.0 (blanco absoluto).
 */
export function getRelativeLuminance(r: number, g: number, b: number): number {
  const a = [r, g, b].map((v) => {
    v /= 255;
    return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
  });
  return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
}

/**
 * Determina el color de texto con máximo contraste legible para cualquier fondo:
 * - Retorna '#090a0f' (texto oscuro) si el color es claro/brillante (ej: #C7F804, amarillo, blanco, etc.).
 * - Retorna '#ffffff' (texto blanco) si el color es oscuro o saturado (ej: #7747FF, azul, etc.).
 */
export function getContrastForeground(hex: string): "#ffffff" | "#090a0f" {
  const { r, g, b } = hexToRgb(hex);
  const lum = getRelativeLuminance(r, g, b);
  return lum > 0.44 ? "#090a0f" : "#ffffff";
}

/**
 * Calcula el ratio de contraste WCAG entre dos colores (ej: 4.5:1 o 12:1).
 */
export function getContrastRatio(hexBg: string, hexFg: string): number {
  const bg = hexToRgb(hexBg);
  const fg = hexToRgb(hexFg);
  const lumBg = getRelativeLuminance(bg.r, bg.g, bg.b);
  const lumFg = getRelativeLuminance(fg.r, fg.g, fg.b);
  const brighter = Math.max(lumBg, lumFg);
  const darker = Math.min(lumBg, lumFg);
  return (brighter + 0.05) / (darker + 0.05);
}
