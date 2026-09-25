// Customizer smoke test: panels render, section reorder control works,
// colour preset previews live. Usage: node tools/customizer-test.mjs [base] [outDir]
import { chromium } from 'playwright-core';
import { readdirSync, mkdirSync } from 'node:fs';

const base = process.argv[2] || 'http://localhost:8080';
const out = process.argv[3] || 'screenshots';
mkdirSync(out, { recursive: true });
const dir = readdirSync('/opt/pw-browsers').find((d) => d.startsWith('chromium-'));
const browser = await chromium.launch({ executablePath: `/opt/pw-browsers/${dir}/chrome-linux/chrome` });
const ctx = await browser.newContext({ viewport: { width: 1400, height: 900 } });
await ctx.route(/\/plugins\/woocommerce\/assets\/(js|client)\/.*\.js/, (r) => r.fulfill({ status: 200, contentType: 'application/javascript', body: '' }));
const p = await ctx.newPage();
const errors = [];
p.on('pageerror', (e) => errors.push(e.message));

let failed = 0;
const check = (name, ok, detail = '') => {
	failed += ok ? 0 : 1;
	console.log(`${ok ? 'ok  ' : 'FAIL'} ${name}${detail ? ' — ' + detail : ''}`);
};

await p.goto(base + '/wp-login.php');
await p.fill('#user_login', 'admin');
await p.fill('#user_pass', 'admin');
await p.click('#wp-submit');
await p.waitForLoadState('networkidle');

await p.goto(base + '/wp-admin/customize.php', { waitUntil: 'networkidle' });
await p.waitForSelector('#accordion-panel-the360hub_design', { timeout: 20000 });
for (const panel of ['the360hub_design', 'the360hub_layout', 'the360hub_home', 'the360hub_advanced']) {
	check(`panel ${panel} present`, (await p.locator(`#accordion-panel-${panel}`).count()) === 1);
}

// Homepage sections: move the 2nd item up and untick one.
await p.click('#accordion-panel-the360hub_home h3');
await p.click('#accordion-section-the360hub_home h3');
const list = p.locator('[data-t360-sortable]');
await list.waitFor();
const before = await list.locator('.t360-sortable__item').nth(1).getAttribute('data-key');
await list.locator('.t360-sortable__item').nth(1).locator('button[data-dir="-1"]').click();
const after = await list.locator('.t360-sortable__item').nth(0).getAttribute('data-key');
check('sortable: move up reorders', before === after, `${before} → first`);
const hidden = await p.locator('#customize-control-t360_home_order input[type="hidden"]').inputValue();
check('sortable: hidden value updated', hidden.startsWith(after + ','), hidden.slice(0, 60));
await p.screenshot({ path: `${out}/customizer-sections.png` });

// Colour preset: live preview updates CSS variables without reload.
await p.click('.customize-section-back:visible');
await p.click('.customize-panel-back:visible');
await p.click('#accordion-panel-the360hub_design h3');
await p.click('#accordion-section-the360hub_colors h3');
await p.selectOption('#_customize-input-t360_color_preset', 'emerald');
const frame = p.frameLocator('#customize-preview iframe');
await p.waitForTimeout(800);
const cta = await frame.locator('html').evaluate((el) => getComputedStyle(el).getPropertyValue('--t360-c-cta').trim());
check('colour preset previews live', cta.toLowerCase() === '#047857', cta);
await p.screenshot({ path: `${out}/customizer-colors.png` });

check('customizer: no JS errors', errors.length === 0, errors.join(' | '));
await browser.close();
process.exit(failed ? 1 : 0);
