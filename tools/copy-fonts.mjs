// Copies the self-hosted variable fonts (OFL-1.1) from npm into the theme.
import { copyFileSync, mkdirSync } from 'node:fs';

const out = 'the360hub/assets/fonts';
mkdirSync(out, { recursive: true });

const files = [
	['@fontsource-variable/nunito-sans/files/nunito-sans-latin-wght-normal.woff2', 'nunito-sans-latin-wght-normal.woff2'],
	['@fontsource-variable/rubik/files/rubik-latin-wght-normal.woff2', 'rubik-latin-wght-normal.woff2'],
	['@fontsource-variable/nunito-sans/LICENSE', 'OFL-nunito-sans.txt'],
	['@fontsource-variable/rubik/LICENSE', 'OFL-rubik.txt'],
];
for (const [from, to] of files) {
	copyFileSync(`node_modules/${from}`, `${out}/${to}`);
	console.log(`fonts: ${to}`);
}
