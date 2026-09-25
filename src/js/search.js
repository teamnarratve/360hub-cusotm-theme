/**
 * Instant search (lazy-loaded by core.js the first time search opens).
 *
 * Idle: recent searches (localStorage) + popular searches (server-rendered).
 * Typing: debounced ?wc-ajax=t360_search → categories, brands, products.
 * All result text is inserted with textContent; no HTML from the response
 * is ever injected.
 */
import { $, $$, store, ajaxUrl } from './util.js';

(() => {
	const dialog = document.getElementById('t360-search');
	if (!dialog || dialog.dataset.ready) return;
	dialog.dataset.ready = '1';

	const cfg = window.t360 || {};
	const i18n = cfg.i18n || {};
	const form = $('form', dialog);
	const input = $('#t360-search-input', dialog);
	const clearBtn = $('[data-t360-search-clear]', dialog);
	const idle = $('[data-t360-search-idle]', dialog);
	const results = $('[data-t360-search-results]', dialog);
	const status = $('[data-t360-search-status]', dialog);
	const recentBox = $('[data-t360-search-recent]', dialog);
	const recentList = $('[data-t360-search-recent-list]', dialog);

	const RECENT_KEY = 't360_recent_searches';
	const MAX_RECENT = 8;
	const MIN_CHARS = 2;
	const cache = new Map();
	let controller = null;
	let timer = 0;

	/* Helpers -------------------------------------------------------------- */

	const el = (tag, className, text) => {
		const node = document.createElement(tag);
		if (className) node.className = className;
		if (text !== undefined) node.textContent = text;
		return node;
	};

	const icon = (name) => {
		const ns = 'http://www.w3.org/2000/svg';
		const svg = document.createElementNS(ns, 'svg');
		svg.setAttribute('class', 't360-icon');
		svg.setAttribute('aria-hidden', 'true');
		const use = document.createElementNS(ns, 'use');
		use.setAttribute('href', '#i-' + name);
		svg.append(use);
		return svg;
	};

	const searchUrl = (term) => {
		const url = new URL(form.action, window.location.href);
		url.searchParams.set('s', term);
		const pt = $('input[name="post_type"]', form);
		if (pt) url.searchParams.set('post_type', pt.value);
		return url.toString();
	};

	const heading = (text) => el('h2', 't360-search__title', text);

	/* Recent searches -------------------------------------------------------- */

	const getRecent = () => store.get(RECENT_KEY, []).filter((t) => typeof t === 'string');

	function saveRecent(term) {
		term = (term || '').trim();
		if (term.length < MIN_CHARS) return;
		const list = getRecent().filter((t) => t.toLowerCase() !== term.toLowerCase());
		list.unshift(term);
		store.set(RECENT_KEY, list.slice(0, MAX_RECENT));
	}

	function renderRecent() {
		const list = getRecent();
		recentList.replaceChildren();
		recentBox.hidden = list.length === 0;
		list.forEach((term) => {
			const li = el('li');
			const row = el('div', 't360-search__row');
			const link = el('a', 't360-search__row-text');
			link.href = searchUrl(term);
			link.textContent = term;
			link.setAttribute('data-t360-search-term', term);
			const fill = el('button', 't360-search__fill');
			fill.type = 'button';
			fill.setAttribute('aria-label', (i18n.fillIn || 'Edit search: %s').replace('%s', term));
			fill.append(icon('arrow-up-left'));
			fill.addEventListener('click', () => {
				input.value = term;
				input.focus();
				onInput();
			});
			row.append(icon('history'), link, fill);
			li.append(row);
			recentList.append(li);
		});
	}

	$('[data-t360-search-recent-clear]', dialog).addEventListener('click', () => {
		store.set(RECENT_KEY, []);
		renderRecent();
		input.focus();
	});

	/* Results ---------------------------------------------------------------- */

	function showIdle() {
		idle.hidden = false;
		results.hidden = true;
		results.replaceChildren();
		status.textContent = '';
	}

	function renderSkeleton() {
		idle.hidden = true;
		results.hidden = false;
		const box = el('div');
		for (let i = 0; i < 4; i++) box.append(el('div', 't360-search__skeleton'));
		results.replaceChildren(box);
		status.textContent = i18n.searching || '';
	}

	function chipGroup(title, items) {
		const section = el('section', 't360-search__group');
		section.append(heading(title));
		const ul = el('ul', 't360-chips');
		items.forEach((item) => {
			const li = el('li');
			const a = el('a', 't360-chip', item.name);
			a.href = item.url;
			li.append(a);
			ul.append(li);
		});
		section.append(ul);
		return section;
	}

	function render(q, data) {
		idle.hidden = true;
		results.hidden = false;
		const frag = document.createDocumentFragment();

		if (data.categories && data.categories.length) frag.append(chipGroup(i18n.categories, data.categories));
		if (data.brands && data.brands.length) frag.append(chipGroup(i18n.brands, data.brands));

		if (data.products && data.products.length) {
			const section = el('section', 't360-search__group');
			section.append(heading(i18n.products));
			const ul = el('ul', 't360-search__list');
			data.products.forEach((p) => {
				const li = el('li');
				const a = el('a', 't360-search__product');
				a.href = p.url;
				const img = el('img', 't360-search__thumb');
				img.src = p.image;
				img.alt = '';
				img.width = 56;
				img.height = 56;
				img.loading = 'lazy';
				const text = el('span');
				text.append(el('span', 't360-search__pname', p.name));
				if (p.price) {
					const price = el('span', 't360-search__pprice', p.price);
					if (p.was) price.append(el('del', '', p.was));
					text.append(price);
				}
				a.append(img, text);
				li.append(a);
				ul.append(li);
			});
			section.append(ul);
			frag.append(section);
		}

		const total = Number(data.total) || 0;
		if (total > 0) {
			const all = el('a', 't360-btn t360-btn--cta t360-search__all', (i18n.seeAll || '') + ' (' + total + ')');
			all.href = data.url || searchUrl(q);
			frag.append(all);
			status.textContent = (i18n.results || '%d').replace('%d', String(total));
		} else if (!frag.childNodes.length) {
			frag.append(el('p', 't360-search__empty', i18n.noResults));
			status.textContent = i18n.noResults || '';
		} else {
			status.textContent = '';
		}

		results.replaceChildren(frag);
	}

	async function run(q) {
		const key = q.toLowerCase();
		if (cache.has(key)) {
			render(q, cache.get(key));
			return;
		}
		if (controller) controller.abort();
		controller = new AbortController();
		const skeletonTimer = setTimeout(renderSkeleton, 150);
		try {
			const res = await fetch(ajaxUrl('t360_search') + '&q=' + encodeURIComponent(q), {
				signal: controller.signal,
				credentials: 'same-origin',
			});
			const json = await res.json();
			clearTimeout(skeletonTimer);
			if (!json || !json.success) throw new Error('search');
			cache.set(key, json.data);
			if (input.value.trim().toLowerCase() === key) render(q, json.data);
		} catch (err) {
			clearTimeout(skeletonTimer);
			if (err.name !== 'AbortError') {
				// Fall back to the normal results page on network errors.
				results.replaceChildren(el('p', 't360-search__empty', i18n.noResults));
			}
		}
	}

	function onInput() {
		const q = input.value.trim();
		clearBtn.hidden = input.value === '';
		clearTimeout(timer);
		if (q.length < MIN_CHARS) {
			if (controller) controller.abort();
			showIdle();
			return;
		}
		timer = setTimeout(() => run(q), 200);
	}

	/* Events ----------------------------------------------------------------- */

	input.addEventListener('input', onInput);

	clearBtn.addEventListener('click', () => {
		input.value = '';
		onInput();
		input.focus();
	});

	form.addEventListener('submit', (e) => {
		const q = input.value.trim();
		if (!q) {
			e.preventDefault();
			return;
		}
		saveRecent(q);
	});

	// Any result/suggestion link records the query as a recent search.
	dialog.addEventListener('click', (e) => {
		const link = e.target.closest('a');
		if (!link) return;
		saveRecent(link.getAttribute('data-t360-search-term') || input.value);
	});

	// Arrow keys move between the field and result links.
	dialog.addEventListener('keydown', (e) => {
		if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
		const links = $$('.t360-search__body a', dialog).filter((a) => a.offsetParent !== null);
		if (!links.length) return;
		const index = links.indexOf(document.activeElement);
		e.preventDefault();
		if (e.key === 'ArrowDown') {
			(links[index + 1] || links[0]).focus();
		} else if (index <= 0) {
			input.focus();
		} else {
			links[index - 1].focus();
		}
	});

	dialog.addEventListener('t360:open', () => {
		renderRecent();
		onInput();
	});

	// First load happens right after the first open.
	renderRecent();
	onInput();
})();
