// Browser smoke test for the local site: screenshots + key interactions.
// Usage: node tools/browser-test.mjs [baseUrl] [outDir]
import { chromium } from 'playwright-core';
import { mkdirSync, readdirSync } from 'node:fs';

const base = process.argv[2] || 'http://localhost:8080';
const out = process.argv[3] || 'screenshots';
mkdirSync(out, { recursive: true });

const exe = (() => {
	if (process.env.CHROMIUM_PATH) return process.env.CHROMIUM_PATH;
	const dir = readdirSync('/opt/pw-browsers').find((d) => d.startsWith('chromium-'));
	return `/opt/pw-browsers/${dir}/chrome-linux/chrome`;
})();

const browser = await chromium.launch({ executablePath: exe });
const results = [];
const check = (name, ok, detail = '') => {
	results.push({ name, ok });
	console.log(`${ok ? 'ok  ' : 'FAIL'} ${name}${detail ? ' — ' + detail : ''}`);
};

async function loadAll(p) {
	await p.evaluate(async () => {
		for (let y = 0; y < document.body.scrollHeight; y += 600) {
			window.scrollTo(0, y);
			await new Promise((r) => setTimeout(r, 60));
		}
		window.scrollTo(0, 0);
	});
	await p.waitForTimeout(500);
}

async function page(viewport, mobile) {
	const ctx = await browser.newContext({ viewport, deviceScaleFactor: 2, isMobile: mobile, hasTouch: mobile });
	// WooCommerce built from source ships no minified JS (release zips do);
	// locally those URLs return HTML. Serve real files when they exist, stub
	// the missing ones so every remaining error is attributable to the theme.
	await ctx.route(/\/plugins\/woocommerce\/assets\/js\/.*\.js/, async (route) => {
		const res = await route.fetch({ maxRedirects: 0 }).catch(() => null);
		if (res && res.status() === 200) return route.fulfill({ response: res });
		return route.fulfill({ status: 200, contentType: 'application/javascript', body: '/* unbuilt locally */' });
	});
	const p = await ctx.newPage();
	const errors = [];
	// WooCommerce built from source has no minified JS (release zips do), so
	// its script URLs return HTML locally; ignore errors that come from them.
	const ignored = (text) => /plugins\/woocommerce\/assets\//.test(text);
	p.on('pageerror', (e) => !ignored(String(e.stack)) && errors.push(e.message));
	p.on('console', (m) => m.type() === 'error' && !ignored(m.location().url + ' ' + m.text()) && errors.push(`${m.text()} @ ${m.location().url}`));
	return { ctx, p, errors };
}

// Mobile ---------------------------------------------------------------
{
	const { ctx, p, errors } = await page({ width: 360, height: 740 }, true);
	await p.goto(base + '/', { waitUntil: 'networkidle' });
	await p.screenshot({ path: `${out}/m-home.png` });
	await loadAll(p);
	await p.screenshot({ path: `${out}/m-home-full.png`, fullPage: true });

	const overflow = await p.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
	check('mobile home: no horizontal page scroll', !overflow);

	const smallTargets = await p.evaluate(() =>
		[...document.querySelectorAll('.t360-header a, .t360-header button, .t360-bottomnav a, .t360-card__wish, .t360-card__atc')]
			.filter((el) => el.offsetParent && !el.matches('.t360-searchfield__submit'))
			.filter((el) => {
				const r = el.getBoundingClientRect();
				return r.width < 40 || r.height < 40;
			})
			.map((el) => `${el.className} ${Math.round(el.getBoundingClientRect().width)}x${Math.round(el.getBoundingClientRect().height)}`)
	);
	check('mobile: header/nav/card controls ≥40px', smallTargets.length === 0, smallTargets.join(', '));

	// Hero slider.
	check('hero slider rendered', (await p.locator('[data-t360-slider] .t360-slide').count()) > 1);
	await p.locator('[data-t360-slider-next]').click();
	await p.waitForTimeout(800);
	check('slider next moves to slide 2', (await p.locator('[data-t360-slider-dot="1"]').getAttribute('aria-current')) === 'true');
	check('slider autoplay paused after interaction', (await p.locator('[data-t360-slider-pause]').getAttribute('aria-pressed')) === 'true');

	// Countdown ticks.
	const t1 = await p.locator('[data-t360-countdown] [data-unit="s"]').innerText();
	await p.waitForTimeout(1200);
	const t2 = await p.locator('[data-t360-countdown] [data-unit="s"]').innerText();
	check('flash deal countdown ticks', t1 !== t2, `${t1} → ${t2}`);

	// Product tabs load other panels on demand.
	const tab2 = p.locator('[data-t360-tabs] [role="tab"]').nth(1);
	await tab2.scrollIntoViewIfNeeded();
	await tab2.click();
	const panelId = await tab2.getAttribute('aria-controls');
	await p.waitForSelector(`#${panelId} .t360-card`, { timeout: 5000 });
	check('product tab switch loads cards', (await p.locator(`#${panelId} .t360-card`).count()) > 0);
	await p.keyboard.press('ArrowRight');
	check('tabs arrow-key navigation', (await p.locator('[data-t360-tabs] [role="tab"]').nth(2).getAttribute('aria-selected')) === 'true');
	await p.evaluate(() => window.scrollTo(0, 0));

	check('floating WhatsApp button shown', (await p.locator('.t360-wafloat').count()) === 1);

	// Drawer.
	await p.click('.t360-header [data-t360-open="t360-drawer"]');
	await p.waitForTimeout(300);
	check('drawer opens', await p.evaluate(() => document.getElementById('t360-drawer').open));
	await p.click('#t360-drawer summary >> nth=0');
	await p.screenshot({ path: `${out}/m-drawer.png` });
	await p.keyboard.press('Escape');
	await p.waitForTimeout(300);
	check('drawer closes on Esc', await p.evaluate(() => !document.getElementById('t360-drawer').open));

	// Search.
	await p.click('.t360-header__search input');
	await p.waitForTimeout(200);
	check('search overlay opens', await p.evaluate(() => document.getElementById('t360-search').open));
	await p.screenshot({ path: `${out}/m-search-idle.png` });
	await p.keyboard.type('nova');
	await p.waitForSelector('.t360-search__product', { timeout: 5000 });
	await p.waitForTimeout(200);
	await p.screenshot({ path: `${out}/m-search-results.png` });
	check('instant search shows products', (await p.locator('.t360-search__product').count()) > 0);
	await Promise.all([p.waitForURL(/s=nova/, { timeout: 8000 }).catch(() => {}), p.keyboard.press('Enter')]);
	check('search submit goes to results page', p.url().includes('s=nova'), p.url());
	await p.screenshot({ path: `${out}/m-search-page.png` });
	await p.goto(base + '/', { waitUntil: 'networkidle' });
	await p.click('.t360-header__search input');
	await p.waitForSelector('[data-t360-search-recent-list] li');
	check('recent search remembered', (await p.locator('[data-t360-search-recent-list] li').count()) > 0);
	await p.keyboard.press('Escape');

	// Add to cart from a card.
	await p.locator('[data-t360-atc]').first().scrollIntoViewIfNeeded();
	await p.locator('[data-t360-atc]').first().click();
	await p.waitForSelector('.t360-toast__msg', { timeout: 8000 });
	const badge = await p.locator('.t360-bottomnav [data-t360-cart-count]').innerText();
	check('ajax add to cart updates badge', badge.trim().startsWith('1'), `badge="${badge.trim()}"`);
	await p.screenshot({ path: `${out}/m-added.png` });

	// Wishlist.
	const wish = p.locator('[data-t360-wish]').first();
	await wish.click();
	check('wishlist toggles aria-pressed', (await wish.getAttribute('aria-pressed')) === 'true');
	await p.goto(base + '/wishlist/', { waitUntil: 'networkidle' });
	await p.waitForSelector('[data-t360-list="wishlist"] .t360-card', { timeout: 5000 });
	check('wishlist page renders saved card', (await p.locator('[data-t360-list="wishlist"] .t360-card').count()) === 1);
	await p.screenshot({ path: `${out}/m-wishlist.png` });

	// Recently viewed.
	await p.goto(base + '/product/orbit-x15-pro-5g-smartphone-256gb-titanium/', { waitUntil: 'networkidle' });
	await p.goto(base + '/', { waitUntil: 'networkidle' });
	await p.waitForSelector('[data-t360-list="recent"]:not([hidden]) .t360-card', { timeout: 5000 });
	check('recently viewed section appears', true);

	// Category page + cart count persists across pages (cookie/session sync).
	await p.goto(base + '/product-category/mobiles-tablets/', { waitUntil: 'networkidle' });
	await p.screenshot({ path: `${out}/m-category.png` });
	await p.screenshot({ path: `${out}/m-category-full.png`, fullPage: true });
	const cols = await p.evaluate(() => getComputedStyle(document.querySelector('.t360-grid')).gridTemplateColumns.split(' ').length);
	check('mobile grid: 2 columns', cols === 2, `${cols}`);
	const badge2 = await p.locator('.t360-bottomnav [data-t360-cart-count]').innerText();
	check('cart badge correct after navigation', badge2.trim().startsWith('1'));

	// Scroll: header condenses.
	await p.mouse.wheel(0, 900);
	await p.waitForTimeout(400);
	check('header tucks away on scroll down', await p.evaluate(() => document.querySelector('.t360-header').classList.contains('is-condensed')));
	await p.screenshot({ path: `${out}/m-category-scrolled.png` });

	check('mobile: no JS errors', errors.length === 0, errors.join(' | '));
	await ctx.close();
}

// Tablet / desktop -----------------------------------------------------
for (const [name, vp, expectCols] of [['t', { width: 768, height: 1024 }, 3], ['d', { width: 1100, height: 800 }, 4], ['w', { width: 1440, height: 900 }, 4]]) {
	const { ctx, p, errors } = await page(vp, false);
	await p.goto(base + '/', { waitUntil: 'networkidle' });
	await p.screenshot({ path: `${out}/${name}-home.png` });
	await loadAll(p);
	await p.screenshot({ path: `${out}/${name}-home-full.png`, fullPage: true });
	await p.goto(base + '/shop/', { waitUntil: 'networkidle' });
	await p.screenshot({ path: `${out}/${name}-shop.png` });
	const cols = await p.evaluate(() => getComputedStyle(document.querySelector('.t360-grid')).gridTemplateColumns.split(' ').length);
	check(`${vp.width}px grid: ${expectCols} columns`, cols === expectCols, `${cols}`);
	const bottomNav = await p.evaluate(() => getComputedStyle(document.querySelector('.t360-bottomnav')).display);
	check(`${vp.width}px bottom nav ${vp.width >= 1024 ? 'hidden' : 'shown'}`, (bottomNav === 'none') === vp.width >= 1024);
	if (vp.width >= 1024) {
		await p.goto(base + '/', { waitUntil: 'networkidle' });
		await p.hover('.t360-mega__toggle');
		await p.waitForTimeout(400);
		check(`${vp.width}px mega menu opens on hover`, await p.locator('#t360-mega-panel').isVisible());
		await p.screenshot({ path: `${out}/${name}-mega.png` });
		await p.mouse.move(5, 700);
		await p.waitForTimeout(200);
		const card = p.locator('.t360-card.has-alt').first();
		await card.scrollIntoViewIfNeeded();
		await card.hover();
		await p.waitForTimeout(700);
		check(`${vp.width}px hover image swaps in`, await card.evaluate((el) => el.classList.contains('alt-ready')));
		await p.evaluate(() => window.scrollTo(0, 0));
		await p.click('.t360-header__search-lg input');
		await p.keyboard.type('arcadia');
		await p.waitForSelector('.t360-search__product', { timeout: 5000 });
		await p.waitForTimeout(200);
		await p.screenshot({ path: `${out}/${name}-search.png` });
		check(`${vp.width}px search panel works`, true);
	}
	check(`${vp.width}px: no JS errors`, errors.length === 0, errors.join(' | '));
	await ctx.close();
}

// Keyboard: skip link + focus visibility.
{
	const { ctx, p } = await page({ width: 1280, height: 800 }, false);
	await p.goto(base + '/', { waitUntil: 'networkidle' });
	await p.keyboard.press('Tab');
	const skip = await p.evaluate(() => document.activeElement.className);
	check('first Tab reaches skip link', skip.includes('t360-skip'));
	await ctx.close();
}

await browser.close();
const failed = results.filter((r) => !r.ok).length;
console.log(`\n${results.length - failed}/${results.length} checks passed`);
process.exit(failed ? 1 : 0);
