/**
 * The360Hub Customizer controls (Customizer only).
 * Sortable section list: checkboxes + up/down buttons → CSV in a hidden input.
 */
(function () {
	'use strict';

	function serialize(list) {
		var keys = [];
		list.querySelectorAll('.t360-sortable__item').forEach(function (item) {
			if (item.querySelector('input[type="checkbox"]').checked) keys.push(item.getAttribute('data-key'));
		});
		var hidden = list.parentNode.querySelector('input[type="hidden"]');
		hidden.value = keys.join(',');
		hidden.dispatchEvent(new Event('change', { bubbles: true }));
	}

	document.addEventListener('change', function (e) {
		var list = e.target.closest('[data-t360-sortable]');
		if (list && e.target.type === 'checkbox') serialize(list);
	});

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-t360-sortable] button[data-dir]');
		if (!btn) return;
		var item = btn.closest('.t360-sortable__item');
		var list = item.parentNode;
		if (btn.getAttribute('data-dir') === '-1' && item.previousElementSibling) {
			list.insertBefore(item, item.previousElementSibling);
		} else if (btn.getAttribute('data-dir') === '1' && item.nextElementSibling) {
			list.insertBefore(item.nextElementSibling, item);
		}
		btn.focus();
		serialize(list);
	});

	// Live number next to range sliders.
	document.addEventListener('input', function (e) {
		if (e.target.matches('.t360-range input[type="range"]')) {
			e.target.nextElementSibling.textContent = e.target.value;
		}
	});
})();
