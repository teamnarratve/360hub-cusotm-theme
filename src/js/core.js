/**
 * The360Hub core (every page, deferred).
 *
 * - Sheets (<dialog>): open/close, back-button closes, backdrop tap closes
 * - Header: tuck logo row away on scroll down (mobile)
 * - Search: opens the overlay and lazy-loads search.js on first use
 * - Cart badge: synced from WooCommerce's cart-hash cookie (no cart fragments)
 * - Card add-to-cart via WooCommerce's own ?wc-ajax=add_to_cart
 * - Wishlist + recently viewed (localStorage)
 *
 * No dependencies. Exposes a few helpers on window.t360.u for the other modules.
 */
import { $, $$, store, ajaxUrl } from './util.js';

const cfg = window.t360 || {};
const i18n = cfg.i18n || {};

const KEYS = {
	wishlist: 't360_wishlist',
	recent: 't360_recent',
	cart: 't360_cart',
};
const MAX_WISHLIST = 100;
const MAX_RECENT = 12;

/* Toast ------------------------------------------------------------------ */

let toastTimer = 0;
function toast(message, link) {
	// The live region stays in the DOM (hiding it would stop announcements);
	// only its message element comes and goes.
	const region = $('[data-t360-toast]');
	if (!region) return;
	const msg = document.createElement('div');
	msg.className = 't360-toast__msg';
	msg.textContent = message;
	if (link && link.href) {
		const a = document.createElement('a');
		a.href = link.href;
		a.textContent = link.label;
		msg.append(a);
	}
	region.replaceChildren(msg);
	const hide = () => region.replaceChildren();
	clearTimeout(toastTimer);
	toastTimer = setTimeout(hide, 5000);
	// Don't disappear while someone is reading or tabbing into it.
	msg.addEventListener('mouseenter', () => clearTimeout(toastTimer));
	msg.addEventListener('focusin', () => clearTimeout(toastTimer));
	msg.addEventListener('mouseleave', () => {
		toastTimer = setTimeout(hide, 2500);
	});
}

/* Sheets ----------------------------------------------------------------- */

function openSheet(id) {
	const dialog = document.getElementById(id);
	if (!dialog || dialog.open || typeof dialog.showModal !== 'function') return false;
	$$('dialog[open]').forEach((d) => d.close());
	dialog.showModal();
	// A history entry lets the phone's back button/gesture close the sheet.
	history.pushState({ t360Sheet: id }, '');
	dialog.dispatchEvent(new CustomEvent('t360:open'));
	return true;
}

function initSheets() {
	$$('dialog.t360-sheet').forEach((dialog) => {
		dialog.addEventListener('close', () => {
			if (history.state && history.state.t360Sheet === dialog.id) {
				history.back();
			}
		});
		// Taps on the backdrop land on the <dialog> itself.
		dialog.addEventListener('click', (e) => {
			if (e.target === dialog) dialog.close();
		});
	});

	window.addEventListener('popstate', () => {
		$$('dialog.t360-sheet[open]').forEach((d) => d.close());
	});

	document.addEventListener('click', (e) => {
		const opener = e.target.closest('[data-t360-open]');
		if (opener && openSheet(opener.getAttribute('data-t360-open'))) {
			e.preventDefault();
			return;
		}
		const closer = e.target.closest('[data-t360-close]');
		if (closer) {
			const dialog = closer.closest('dialog');
			if (dialog) dialog.close();
		}
	});
}

/* Header ----------------------------------------------------------------- */

function initHeader() {
	const header = $('[data-t360-header][data-t360-condense]');
	if (!header) return;

	let lastY = window.scrollY;
	let ticking = false;
	window.addEventListener(
		'scroll',
		() => {
			if (ticking) return;
			ticking = true;
			requestAnimationFrame(() => {
				const y = window.scrollY;
				if (Math.abs(y - lastY) > 8) {
					header.classList.toggle('is-condensed', y > lastY && y > 120);
					lastY = y;
				}
				ticking = false;
			});
		},
		{ passive: true }
	);
	// Keyboard users tabbing into the hidden row get it back.
	header.addEventListener('focusin', () => header.classList.remove('is-condensed'));
}

/* Search trigger ---------------------------------------------------------- */

let searchLoaded = false;
function openSearch(value) {
	const dialog = document.getElementById('t360-search');
	if (!dialog) return false;
	const input = $('#t360-search-input', dialog);
	if (typeof value === 'string') input.value = value;
	if (!openSheet('t360-search')) return false;
	input.focus();
	if (!searchLoaded && cfg.searchJs) {
		searchLoaded = true;
		const s = document.createElement('script');
		s.src = cfg.searchJs;
		s.defer = true;
		document.head.append(s);
	}
	return true;
}

function initSearchTriggers() {
	$$('[data-t360-search-trigger]').forEach((form) => {
		const input = $('input[type="search"]', form);
		// Tap/click: open the overlay (inside the user gesture so the keyboard opens on iOS).
		input.addEventListener('click', () => {
			if (openSearch(input.value)) input.blur();
		});
		// Keyboard users who start typing in the header field are moved across too.
		input.addEventListener('input', () => {
			const value = input.value;
			input.value = '';
			openSearch(value);
		});
	});
}

/* Cart badge -------------------------------------------------------------- */

function setCartCount(count) {
	const n = Math.max(0, parseInt(count, 10) || 0);
	$$('[data-t360-cart-count]').forEach((el) => {
		if (el.firstChild && el.firstChild.nodeType === 3) {
			el.firstChild.nodeValue = String(n);
		} else {
			el.prepend(String(n));
		}
		el.hidden = n === 0;
	});
}

function getCookie(name) {
	const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
	return match ? decodeURIComponent(match[1]) : '';
}

function syncCart() {
	if (!cfg.ajax) return;
	const hash = getCookie('woocommerce_cart_hash');
	if (!hash) {
		setCartCount(0);
		return;
	}
	let cached = null;
	try {
		cached = JSON.parse(sessionStorage.getItem(KEYS.cart) || 'null');
	} catch (e) {
		/* storage unavailable */
	}
	if (cached && cached.hash === hash) {
		setCartCount(cached.count);
		return;
	}
	fetch(ajaxUrl('t360_cart_count'), { credentials: 'same-origin' })
		.then((r) => r.json())
		.then((res) => {
			if (!res || !res.success) return;
			setCartCount(res.data.count);
			try {
				sessionStorage.setItem(KEYS.cart, JSON.stringify({ hash: res.data.hash || hash, count: res.data.count }));
			} catch (e) {
				/* storage unavailable */
			}
		})
		.catch(() => {});
}

/* Add to cart ------------------------------------------------------------- */

function initAddToCart() {
	document.addEventListener('click', async (e) => {
		const btn = e.target.closest('[data-t360-atc]');
		if (!btn || !cfg.ajax) return;
		e.preventDefault();
		if (btn.getAttribute('aria-busy') === 'true') return;

		btn.setAttribute('aria-busy', 'true');
		const body = new FormData();
		body.append('product_id', btn.getAttribute('data-t360-atc'));
		body.append('quantity', '1');

		try {
			const res = await fetch(ajaxUrl('add_to_cart'), { method: 'POST', body, credentials: 'same-origin' });
			const data = await res.json();
			if (!data || data.error) {
				// WooCommerce sends the product URL when options/validation are needed.
				if (data && data.product_url) {
					window.location.href = data.product_url;
					return;
				}
				throw new Error('add_to_cart');
			}
			const count = data.fragments && data.fragments.t360_cart_count;
			if (count !== undefined) {
				setCartCount(count);
				try {
					sessionStorage.setItem(KEYS.cart, JSON.stringify({ hash: data.cart_hash, count }));
				} catch (err) {
					/* storage unavailable */
				}
			}
			toast(i18n.added, { href: cfg.cartUrl, label: i18n.viewCart });
			// Let plugins that listen for WooCommerce's jQuery event (analytics, pixels) react.
			if (window.jQuery) {
				window.jQuery(document.body).trigger('added_to_cart', [data.fragments, data.cart_hash, window.jQuery(btn)]);
			}
		} catch (err) {
			toast(i18n.addFailed);
		} finally {
			btn.removeAttribute('aria-busy');
		}
	});
}

/* Wishlist + recently viewed ---------------------------------------------- */

const wishlist = {
	get: () => store.get(KEYS.wishlist, []).map(Number).filter(Boolean),
	set(ids) {
		store.set(KEYS.wishlist, ids.slice(0, MAX_WISHLIST));
		syncWishlist();
		document.dispatchEvent(new CustomEvent('t360:wishlist', { detail: { ids } }));
	},
	has: (id) => wishlist.get().includes(Number(id)),
	toggle(id) {
		id = Number(id);
		const ids = wishlist.get().filter((x) => x !== id);
		const added = ids.length === wishlist.get().length;
		if (added) ids.unshift(id);
		wishlist.set(ids);
		return added;
	},
};

function syncWishlist(root = document) {
	const ids = wishlist.get();
	$$('[data-t360-wish]', root).forEach((btn) => {
		btn.setAttribute('aria-pressed', String(ids.includes(Number(btn.getAttribute('data-t360-wish')))));
	});
	$$('[data-t360-wish-count]').forEach((el) => {
		el.textContent = String(ids.length);
		el.hidden = ids.length === 0;
	});
}

function initWishlist() {
	document.addEventListener('click', (e) => {
		const btn = e.target.closest('[data-t360-wish]');
		if (!btn) return;
		e.preventDefault();
		const added = wishlist.toggle(btn.getAttribute('data-t360-wish'));
		toast(added ? i18n.wishAdded : i18n.wishRemoved);
	});
	// Keep tabs in sync.
	window.addEventListener('storage', (e) => {
		if (e.key === KEYS.wishlist) syncWishlist();
	});
	syncWishlist();
}

function recordRecentlyViewed() {
	const id = Number(cfg.productId);
	if (!id) return;
	const ids = store.get(KEYS.recent, []).map(Number).filter((x) => x && x !== id);
	ids.unshift(id);
	store.set(KEYS.recent, ids.slice(0, MAX_RECENT));
}

/* Footer ------------------------------------------------------------------ */

function initFooter() {
	const mq = window.matchMedia('(min-width: 1024px)');
	const apply = () => $$('[data-t360-footer-col]').forEach((d) => {
		d.open = mq.matches;
	});
	apply();
	mq.addEventListener('change', apply);
}

/* Boot -------------------------------------------------------------------- */

window.t360 = Object.assign(cfg, {
	u: { $, $$, store, ajaxUrl, toast, openSheet, syncWishlist, wishlist, KEYS },
});

initSheets();
initHeader();
initSearchTriggers();
initAddToCart();
initWishlist();
initFooter();
recordRecentlyViewed();
syncCart();
