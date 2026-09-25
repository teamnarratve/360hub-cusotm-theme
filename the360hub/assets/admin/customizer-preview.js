/**
 * The360Hub live preview (Customizer preview frame only).
 * Colour and width settings update CSS variables without a reload.
 */
(function (api) {
	'use strict';
	var cfg = window.t360Presets || {};
	var names = ['brand', 'cta', 'deal', 'highlight', 'header', 'topbar', 'footer', 'page'];

	function luminance(hex) {
		var c = [1, 3, 5].map(function (i) {
			var v = parseInt(hex.slice(i, i + 2), 16) / 255;
			return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
		});
		return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
	}
	function onColor(hex) {
		var l = luminance(hex);
		return 1.05 / (l + 0.05) >= (l + 0.05) / 0.0563 ? '#FFFFFF' : '#0B1220';
	}

	function apply() {
		var preset = cfg.presets[api(cfg.prefix + 'color_preset').get()] || cfg.presets.electric;
		var root = document.documentElement.style;
		names.forEach(function (n) {
			var override = api(cfg.prefix + 'color_' + n) ? api(cfg.prefix + 'color_' + n).get() : '';
			var hex = override || preset[n];
			root.setProperty('--t360-c-' + n, hex);
			root.setProperty('--t360-on-' + n, onColor(hex));
		});
	}

	api.bind('preview-ready', function () {
		['color_preset'].concat(names.map(function (n) { return 'color_' + n; })).forEach(function (id) {
			api(cfg.prefix + id, function (setting) { setting.bind(apply); });
		});
		api(cfg.prefix + 'style_container', function (setting) {
			setting.bind(function (v) {
				document.documentElement.style.setProperty('--t360-max', v + 'px');
			});
		});
	});
})(wp.customize);
