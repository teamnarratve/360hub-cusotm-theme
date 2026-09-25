// Verifies WCAG contrast for the default token pairs in src/css/tokens.css.
import { readFileSync } from 'node:fs';

const css = readFileSync('src/css/tokens.css', 'utf8');
const token = (name) => {
	const m = css.match(new RegExp(`--t360-${name}:\\s*(#[0-9a-fA-F]{6})`));
	if (!m) throw new Error(`token ${name} not found`);
	return m[1];
};
const lum = (hex) => {
	const c = [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16) / 255)
		.map((v) => (v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4));
	return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
};
const ratio = (a, b) => {
	const [x, y] = [lum(a), lum(b)].sort((p, q) => q - p);
	return (x + 0.05) / (y + 0.05);
};

// [foreground, background, minimum, note]
const pairs = [
	['text', 'bg', 4.5, 'body text'],
	['text-2', 'bg', 4.5, 'secondary text'],
	['text-2', 'surface', 4.5, 'secondary text on surface'],
	['text-3', 'bg', 4.5, 'meta text'],
	['text-3', 'surface', 4.5, 'meta text on surface'],
	['secondary', 'bg', 4.5, 'links'],
	['on-cta', 'cta', 4.5, 'CTA button'],
	['on-primary', 'primary', 4.5, 'primary surfaces'],
	['deal', 'bg', 4.5, 'deal text'],
	['save', 'bg', 4.5, 'savings text'],
	['warn', 'bg', 4.5, 'low stock text'],
	['deal', 'deal-soft', 4.5, 'error notice'],
	['save', 'save-soft', 4.5, 'success notice'],
	['border-strong', 'bg', 3, 'input borders (non-text)'],
	['focus', 'bg', 3, 'focus ring (non-text)'],
];

let failed = false;
for (const [fg, bg, min, note] of pairs) {
	const fgHex = fg === 'focus' ? token('secondary') : token(fg);
	const r = ratio(fgHex, token(bg));
	const ok = r >= min;
	failed ||= !ok;
	console.log(`${ok ? 'ok  ' : 'FAIL'} ${r.toFixed(2).padStart(5)}:1  ≥${min}  ${fg} on ${bg}  (${note})`);
}
// Badge text is white on deal red.
const badge = ratio('#FFFFFF', token('deal'));
failed ||= badge < 4.5;
console.log(`${badge >= 4.5 ? 'ok  ' : 'FAIL'} ${badge.toFixed(2).padStart(5)}:1  ≥4.5  white on deal  (discount badge)`);
process.exit(failed ? 1 : 0);
