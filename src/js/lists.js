/**
 * Browser-stored product lists rendered as cards:
 * - Recently viewed (homepage section)
 * - Wishlist page
 *
 * Cards come from ?wc-ajax=t360_cards, the same PHP template as every other
 * card, so markup and prices never drift. Loaded only where these lists exist.
 */
import { $, ajaxUrl } from './util.js';

(() => {
	const cfg = window.t360 || {};
	const u = cfg.u;
	if (!u) return;

	async function fetchCards(ids) {
		if (!ids.length) return { html: '', ids: [] };
		const res = await fetch(ajaxUrl('t360_cards') + '&ids=' + ids.join(','), { credentials: 'same-origin' });
		const json = await res.json();
		if (!json || !json.success) throw new Error('cards');
		return json.data;
	}

	/* Recently viewed ------------------------------------------------------ */

	const recent = $('[data-t360-list="recent"]');
	if (recent) {
		const ids = u.store.get(u.KEYS.recent, []).map(Number).filter(Boolean).slice(0, 12);
		if (ids.length) {
			fetchCards(ids)
				.then((data) => {
					if (!data.ids.length) return;
					// Trusted: rendered by the theme's own endpoint with escaped output.
					$('[data-t360-list-items]', recent).innerHTML = data.html;
					recent.hidden = false;
					u.syncWishlist(recent);
				})
				.catch(() => {});
		}
	}

	/* Wishlist page -------------------------------------------------------- */

	const wish = $('[data-t360-list="wishlist"]');
	if (wish) {
		const items = $('[data-t360-list-items]', wish);
		const empty = $('[data-t360-list-empty]', wish);
		const count = $('[data-t360-list-count]');
		const i18n = cfg.i18n || {};

		const setCount = (n) => {
			if (count) count.textContent = n ? (i18n.saved || '%d').replace('%d', String(n)) : '';
		};

		const showEmpty = () => {
			items.replaceChildren();
			items.hidden = true;
			empty.hidden = false;
			setCount(0);
		};

		const ids = u.wishlist.get().slice(0, 24);
		if (!ids.length) {
			showEmpty();
		} else {
			fetchCards(ids)
				.then((data) => {
					// Forget products that were deleted or unpublished.
					if (data.ids.length < ids.length) {
						const keep = new Set(data.ids.map(Number));
						u.store.set(u.KEYS.wishlist, u.wishlist.get().filter((id) => keep.has(id) || !ids.includes(id)));
					}
					if (!data.ids.length) {
						showEmpty();
						return;
					}
					items.innerHTML = data.html;
					setCount(data.ids.length);
					u.syncWishlist();
				})
				.catch(showEmpty)
				.finally(() => items.removeAttribute('aria-busy'));
		}

		// Un-saving a product on this page removes its card.
		document.addEventListener('t360:wishlist', (e) => {
			const saved = new Set(e.detail.ids.map(Number));
			items.querySelectorAll('[data-t360-wish]').forEach((btn) => {
				if (!saved.has(Number(btn.getAttribute('data-t360-wish')))) {
					const li = btn.closest('li');
					if (li) li.remove();
				}
			});
			const left = items.children.length;
			if (!left) showEmpty();
			else setCount(left);
		});
	}
})();
