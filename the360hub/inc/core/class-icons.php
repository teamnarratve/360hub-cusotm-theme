<?php
/**
 * SVG icon sprite.
 *
 * Icons are printed once as <symbol>s in the footer and referenced with
 * <use>, so each icon's markup ships once per page regardless of how many
 * times it appears. Paths are from Lucide (ISC licence, https://lucide.dev).
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

defined( 'ABSPATH' ) || exit;

final class Icons {

	/**
	 * Icons used on this request.
	 *
	 * @var array<string, bool>
	 */
	private static $used = array();

	/**
	 * Icon name → inner SVG markup (24×24 viewBox, stroke-based).
	 */
	private static function paths(): array {
		return array(
			'menu'          => '<path d="M4 6h16M4 12h16M4 18h16"/>',
			'search'        => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
			'heart'         => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
			'user'          => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
			'bag'           => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
			'home'          => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
			'grid'          => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
			'close'         => '<path d="M18 6 6 18M6 6l12 12"/>',
			'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
			'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
			'arrow-left'    => '<path d="m12 19-7-7 7-7M19 12H5"/>',
			'arrow-up-left' => '<path d="M7 17V7h10M17 17 7 7"/>',
			'star'          => '<path fill="currentColor" stroke="none" d="M12 2.5l2.94 5.96 6.56.95-4.75 4.63 1.12 6.53L12 17.49l-5.87 3.08 1.12-6.53L2.5 9.41l6.56-.95z"/>',
			'truck'         => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2M15 18H9M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
			'shield'        => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
			'returns'       => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
			'card'          => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/>',
			'chat'          => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
			'clock'         => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
			'history'       => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5M12 7v5l4 2"/>',
			'trending'      => '<path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>',
			'phone'         => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
			'mail'          => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
			'check'         => '<path d="M20 6 9 17l-5-5"/>',
			'zap'           => '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
			'arrow-right'   => '<path d="M5 12h14M12 5l7 7-7 7"/>',
			'chevron-left'  => '<path d="m15 18-6-6 6-6"/>',
			'pause'         => '<rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/>',
			'play'          => '<path d="m6 3 14 9-14 9V3z"/>',
			'map-pin'       => '<path d="M20 10c0 4.99-5.54 10.19-7.4 11.8a1 1 0 0 1-1.2 0C9.54 20.19 4 14.99 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
			'sparkles'      => '<path d="M9.94 15.5A2 2 0 0 0 8.5 14.06l-6.14-1.58a.5.5 0 0 1 0-.96L8.5 9.94A2 2 0 0 0 9.94 8.5l1.58-6.14a.5.5 0 0 1 .96 0L14.06 8.5A2 2 0 0 0 15.5 9.94l6.14 1.58a.5.5 0 0 1 0 .96L15.5 14.06a2 2 0 0 0-1.44 1.44l-1.58 6.14a.5.5 0 0 1-.96 0z"/>',
		);
	}

	/**
	 * Filled brand icons (Simple Icons, CC0), keyed by name.
	 */
	private static function brands(): array {
		static $brands = null;
		if ( null === $brands ) {
			$brands = require __DIR__ . '/social-icons.php';
		}
		return $brands;
	}

	/**
	 * Markup for one icon. Decorative by default (aria-hidden); the caller
	 * provides the accessible name on the surrounding control.
	 */
	public static function get( string $name, string $css_class = '' ): string {
		if ( ! isset( self::paths()[ $name ] ) && ! isset( self::brands()[ $name ] ) ) {
			return '';
		}
		self::$used[ $name ] = true;

		return sprintf(
			'<svg class="t360-icon %1$s" aria-hidden="true" focusable="false"><use href="#i-%2$s"></use></svg>',
			esc_attr( $css_class ),
			esc_attr( $name )
		);
	}

	/**
	 * Print the sprite with only the icons used on this request, plus icons
	 * that scripts create at runtime.
	 */
	public static function sprite(): void {
		$paths  = self::paths();
		$brands = self::brands();
		$names  = array_keys( self::$used + array_fill_keys( array( 'search', 'history', 'arrow-up-left', 'close', 'check', 'heart', 'star', 'bag' ), true ) );

		echo '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">';
		foreach ( $names as $name ) {
			// Static markup defined in this class / generated file; not user input.
			if ( isset( $brands[ $name ] ) ) {
				echo '<symbol id="i-' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="currentColor"><path d="' . esc_attr( $brands[ $name ] ) . '"/></symbol>';
			} else {
				echo '<symbol id="i-' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $name ] . '</symbol>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
		echo '</svg>';
	}
}
