<?php
/**
 * Front-end assets and design-token output.
 *
 * - One global stylesheet (built by Tailwind), versioned by file mtime.
 * - core.js everywhere (deferred). search.js is lazy-loaded by core.js the
 *   first time search opens. lists.js only where wishlist / recently viewed
 *   lists are rendered.
 * - Brand tokens from the Customizer are printed as a tiny :root block.
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

defined( 'ABSPATH' ) || exit;

final class Assets {

	/** Font files shipped in assets/fonts. */
	const FONT_BODY    = 'nunito-sans-latin-wght-normal.woff2';
	const FONT_HEADING = 'rubik-latin-wght-normal.woff2';

	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'preload_font' ), 1 );
	}

	/**
	 * Cache-busting version for a theme asset.
	 */
	private static function ver( string $rel ): string {
		$path = THE360HUB_DIR . '/' . $rel;
		return is_readable( $path ) ? (string) filemtime( $path ) : THE360HUB_VERSION;
	}

	private static function uri( string $rel ): string {
		return THE360HUB_URI . '/' . $rel;
	}

	public static function enqueue(): void {
		wp_enqueue_style( 't360-app', self::uri( 'assets/css/app.css' ), array(), self::ver( 'assets/css/app.css' ) );
		wp_add_inline_style( 't360-app', self::token_css() );

		wp_register_script( 't360-search', self::uri( 'assets/js/search.js' ), array(), self::ver( 'assets/js/search.js' ), array( 'strategy' => 'defer' ) );

		wp_enqueue_script( 't360-core', self::uri( 'assets/js/core.js' ), array(), self::ver( 'assets/js/core.js' ), array( 'strategy' => 'defer' ) );
		wp_add_inline_script( 't360-core', 'window.t360=' . wp_json_encode( self::config() ) . ';', 'before' );

		if ( is_front_page() || is_page_template( 'page-templates/wishlist.php' ) ) {
			wp_enqueue_script( 't360-lists', self::uri( 'assets/js/lists.js' ), array( 't360-core' ), self::ver( 'assets/js/lists.js' ), array( 'strategy' => 'defer' ) );
		}
	}

	/**
	 * Data passed to front-end scripts. Public, non-sensitive values only.
	 */
	private static function config(): array {
		$has_wc = t360_has_wc();

		$config = array(
			'ajax'      => $has_wc ? \WC_AJAX::get_endpoint( '%%endpoint%%' ) : '',
			'searchJs'  => add_query_arg( 'ver', self::ver( 'assets/js/search.js' ), self::uri( 'assets/js/search.js' ) ),
			'productId' => ( $has_wc && is_product() ) ? get_queried_object_id() : 0,
			'i18n'      => array(
				'added'       => __( 'Added to cart', 'the360hub' ),
				'addFailed'   => __( 'Could not add to cart. Please try again.', 'the360hub' ),
				'viewCart'    => __( 'View cart', 'the360hub' ),
				'wishAdded'   => __( 'Saved to wishlist', 'the360hub' ),
				'wishRemoved' => __( 'Removed from wishlist', 'the360hub' ),
				'noResults'   => __( 'No matches found. Try a different spelling or a more general term.', 'the360hub' ),
				/* translators: %d: number of products */
				'results'     => __( '%d products found', 'the360hub' ),
				'searching'   => __( 'Searching…', 'the360hub' ),
				'seeAll'      => __( 'See all results', 'the360hub' ),
				'clear'       => __( 'Clear', 'the360hub' ),
				'recent'      => __( 'Recent searches', 'the360hub' ),
				'products'    => __( 'Products', 'the360hub' ),
				'categories'  => __( 'Categories', 'the360hub' ),
				'brands'      => __( 'Brands', 'the360hub' ),
				/* translators: %s: a previous search term */
				'fillIn'      => __( 'Edit search: %s', 'the360hub' ),
				/* translators: %d: number of saved products */
				'saved'       => __( '%d saved', 'the360hub' ),
			),
			'cartUrl'   => $has_wc ? wc_get_cart_url() : '',
		);

		return $config;
	}

	/**
	 * Pick readable text (white or ink) for a background colour.
	 */
	private static function on_color( string $hex ): string {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$lum = 0;
		foreach ( array( 0.2126, 0.7152, 0.0722 ) as $i => $weight ) {
			$c    = hexdec( substr( $hex, $i * 2, 2 ) ) / 255;
			$c    = $c <= 0.03928 ? $c / 12.92 : ( ( $c + 0.055 ) / 1.055 ) ** 2.4;
			$lum += $weight * $c;
		}
		// Contrast against white vs against ink (#0B1220, L≈0.006).
		$vs_white = 1.05 / ( $lum + 0.05 );
		$vs_ink   = ( $lum + 0.05 ) / 0.0563;
		return $vs_white >= $vs_ink ? '#FFFFFF' : '#0B1220';
	}

	/**
	 * :root overrides for Customizer-controlled tokens, plus @font-face.
	 */
	private static function token_css(): string {
		$vars = array();
		foreach ( array( 'primary', 'secondary', 'cta' ) as $name ) {
			$hex = sanitize_hex_color( (string) t360_setting( 'color_' . $name ) );
			if ( $hex ) {
				$vars[] = "--t360-{$name}:{$hex}";
				$vars[] = "--t360-on-{$name}:" . self::on_color( $hex );
			}
		}

		$css = '';
		if ( 'brand' === t360_setting( 'font_family' ) ) {
			$fonts  = self::uri( 'assets/fonts/' );
			$css   .= "@font-face{font-family:'Nunito Sans';font-style:normal;font-display:swap;font-weight:200 1000;src:url({$fonts}" . self::FONT_BODY . ") format('woff2')}";
			$css   .= "@font-face{font-family:'Rubik';font-style:normal;font-display:swap;font-weight:300 900;src:url({$fonts}" . self::FONT_HEADING . ") format('woff2')}";
			$vars[] = "--t360-font-body:'Nunito Sans',var(--t360-font-system)";
			$vars[] = "--t360-font-heading:'Rubik',var(--t360-font-system)";
		}

		return $css . ':root{' . implode( ';', $vars ) . '}';
	}

	/**
	 * Preload the body font (it renders the LCP text on most pages).
	 */
	public static function preload_font(): void {
		if ( 'brand' !== t360_setting( 'font_family' ) ) {
			return;
		}
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( self::uri( 'assets/fonts/' . self::FONT_BODY ) )
		);
	}
}
