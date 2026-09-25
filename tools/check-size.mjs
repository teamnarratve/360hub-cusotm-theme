// Fails the build if global assets exceed the phase budgets (gzip sizes).
import { readFileSync } from 'node:fs';
import { gzipSync } from 'node:zlib';

const budgets = [
	['the360hub/assets/css/app.css', 30 * 1024],
	['the360hub/assets/js/core.js', 10 * 1024],
	['the360hub/assets/js/search.js', 6 * 1024],
	['the360hub/assets/js/lists.js', 4 * 1024],
	['the360hub/assets/js/home.js', 4 * 1024],
];

let failed = false;
for (const [file, max] of budgets) {
	const raw = readFileSync(file);
	const gz = gzipSync(raw, { level: 9 }).length;
	const ok = gz <= max;
	failed ||= !ok;
	console.log(`${ok ? 'ok  ' : 'FAIL'} ${file}  ${(raw.length / 1024).toFixed(1)}KB raw  ${(gz / 1024).toFixed(1)}KB gz  (budget ${(max / 1024).toFixed(0)}KB)`);
}
process.exit(failed ? 1 : 0);
