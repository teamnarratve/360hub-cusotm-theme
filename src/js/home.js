/**
 * Homepage behaviour (loaded on the front page only):
 * - Hero slider: scroll-snap track + dots, arrows, optional autoplay with a
 *   pause button. Autoplay stops for reduced motion, on hover/focus, and for
 *   good after the visitor swipes or uses the controls.
 * - Product tabs (ARIA tabs); other tabs load their cards on first use.
 * - Flash-deal countdown.
 */
import { $, $$, ajaxUrl } from './util.js';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* Slider ---------------------------------------------------------------- */

function initSlider(root) {
	const track = $('[data-t360-slider-track]', root);
	const slides = Array.from(track.children);
	const dots = $$('[data-t360-slider-dot]', root);
	const pauseBtn = $('[data-t360-slider-pause]', root);
	if (slides.length < 2) return;

	let index = 0;
	let timer = 0;
	let userPaused = false;
	let hovering = false;
	const delay = parseInt(root.getAttribute('data-autoplay') || '0', 10);

	const go = (i) => {
		index = (i + slides.length) % slides.length;
		track.scrollTo({ left: slides[index].offsetLeft, behavior: reducedMotion ? 'auto' : 'smooth' });
	};

	// Keep dots in sync with swipes as well as button presses.
	const io = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting && entry.intersectionRatio > 0.6) {
					index = slides.indexOf(entry.target);
					dots.forEach((d, n) => d.setAttribute('aria-current', String(n === index)));
				}
			});
		},
		{ root: track, threshold: [0.6] }
	);
	slides.forEach((s) => io.observe(s));

	const stop = () => {
		clearInterval(timer);
		timer = 0;
	};
	const start = () => {
		if (!delay || reducedMotion || userPaused || hovering || timer) return;
		timer = setInterval(() => go(index + 1), delay);
	};
	const pauseForGood = () => {
		userPaused = true;
		stop();
		if (pauseBtn) {
			pauseBtn.setAttribute('aria-pressed', 'true');
		}
	};

	$('[data-t360-slider-prev]', root).addEventListener('click', () => {
		pauseForGood();
		go(index - 1);
	});
	$('[data-t360-slider-next]', root).addEventListener('click', () => {
		pauseForGood();
		go(index + 1);
	});
	dots.forEach((dot, n) =>
		dot.addEventListener('click', () => {
			pauseForGood();
			go(n);
		})
	);
	if (pauseBtn) {
		if (!delay || reducedMotion) {
			pauseBtn.hidden = true;
		}
		pauseBtn.addEventListener('click', () => {
			const paused = pauseBtn.getAttribute('aria-pressed') !== 'true';
			pauseBtn.setAttribute('aria-pressed', String(paused));
			userPaused = paused;
			if (paused) stop();
			else start();
		});
	}
	track.addEventListener('pointerdown', pauseForGood, { passive: true });
	root.addEventListener('mouseenter', () => {
		hovering = true;
		stop();
	});
	root.addEventListener('mouseleave', () => {
		hovering = false;
		start();
	});
	root.addEventListener('focusin', stop);
	root.addEventListener('focusout', (e) => {
		if (!root.contains(e.relatedTarget)) start();
	});
	document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));

	start();
}

/* Product tabs ------------------------------------------------------------- */

function initTabs(root) {
	const tabs = $$('[role="tab"]', root);
	const panels = tabs.map((t) => document.getElementById(t.getAttribute('aria-controls')));

	const load = async (panel) => {
		const list = $('.t360-rail', panel);
		if (list.children.length || panel.dataset.loading) return;
		panel.dataset.loading = '1';
		panel.setAttribute('aria-busy', 'true');
		try {
			const res = await fetch(ajaxUrl('t360_cards') + '&ids=' + panel.getAttribute('data-ids'), { credentials: 'same-origin' });
			const json = await res.json();
			if (json && json.success) {
				// Trusted: rendered by the theme's own endpoint with escaped output.
				list.innerHTML = json.data.html;
				window.t360.u.syncWishlist(list);
			}
		} catch (e) {
			delete panel.dataset.loading;
		} finally {
			panel.removeAttribute('aria-busy');
		}
	};

	const select = (tab, focus) => {
		tabs.forEach((t, n) => {
			const on = t === tab;
			t.setAttribute('aria-selected', String(on));
			t.tabIndex = on ? 0 : -1;
			panels[n].hidden = !on;
			if (on) load(panels[n]);
		});
		if (focus) tab.focus();
	};

	tabs.forEach((tab, n) => {
		tab.addEventListener('click', () => select(tab));
		tab.addEventListener('keydown', (e) => {
			const map = { ArrowRight: n + 1, ArrowLeft: n - 1, Home: 0, End: tabs.length - 1 };
			if (!(e.key in map)) return;
			e.preventDefault();
			select(tabs[(map[e.key] + tabs.length) % tabs.length], true);
		});
	});

	// Warm the next tab when the section comes into view, so switching is instant.
	const io = new IntersectionObserver((entries) => {
		if (entries.some((en) => en.isIntersecting)) {
			io.disconnect();
			if (panels[1]) load(panels[1]);
		}
	});
	io.observe(root);
}

/* Countdown --------------------------------------------------------------- */

function initCountdown(root) {
	const end = parseInt(root.getAttribute('data-t360-countdown'), 10) * 1000;
	const cells = {};
	$$('[data-unit]', root).forEach((b) => {
		cells[b.getAttribute('data-unit')] = b;
	});
	const pad = (n) => String(n).padStart(2, '0');

	const tick = () => {
		let left = Math.max(0, Math.floor((end - Date.now()) / 1000));
		const d = Math.floor(left / 86400);
		left %= 86400;
		const h = Math.floor(left / 3600);
		left %= 3600;
		if (cells.d) {
			cells.d.textContent = pad(d);
			cells.d.parentElement.hidden = d === 0;
		}
		if (cells.h) cells.h.textContent = pad(h);
		if (cells.m) cells.m.textContent = pad(Math.floor(left / 60));
		if (cells.s) cells.s.textContent = pad(left % 60);
		if (end <= Date.now()) clearInterval(timer);
	};
	const timer = setInterval(tick, 1000);
	tick();
}

$$('[data-t360-slider]').forEach(initSlider);
$$('[data-t360-tabs]').forEach(initTabs);
$$('[data-t360-countdown]').forEach(initCountdown);
