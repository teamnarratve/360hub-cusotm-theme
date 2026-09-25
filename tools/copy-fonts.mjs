// Copies the self-hosted variable fonts (OFL-1.1) from npm into the theme.
// Only the pairing chosen in the Customizer is ever downloaded by visitors.
import { copyFileSync, mkdirSync } from 'node:fs';

const out = 'the360hub/assets/fonts';
mkdirSync(out, { recursive: true });

const families = ['plus-jakarta-sans', 'outfit', 'sora', 'inter', 'manrope', 'rubik', 'nunito-sans'];
for (const family of families) {
	const base = `node_modules/@fontsource-variable/${family}`;
	copyFileSync(`${base}/files/${family}-latin-wght-normal.woff2`, `${out}/${family}-latin-wght-normal.woff2`);
	copyFileSync(`${base}/LICENSE`, `${out}/OFL-${family}.txt`);
	console.log(`fonts: ${family}`);
}
