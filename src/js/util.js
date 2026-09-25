/**
 * Shared helpers (bundled into each entry by esbuild; tiny).
 */

export const $ = (sel, root = document) => root.querySelector(sel);
export const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

/** localStorage JSON wrapper that never throws (private mode, quota, disabled). */
export const store = {
	get(key, fallback) {
		try {
			const value = JSON.parse(localStorage.getItem(key));
			return value === null ? fallback : value;
		} catch (e) {
			return fallback;
		}
	},
	set(key, value) {
		try {
			localStorage.setItem(key, JSON.stringify(value));
		} catch (e) {
			/* storage unavailable: feature degrades to per-page */
		}
	},
};

/** WooCommerce AJAX endpoint URL (?wc-ajax=...). */
export const ajaxUrl = (endpoint) => String((window.t360 || {}).ajax || '').replace('%%endpoint%%', endpoint);
