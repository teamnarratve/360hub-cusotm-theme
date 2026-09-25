<?php
/**
 * Central theme configuration.
 *
 * Every configurable value is declared once in schema(): its type, default,
 * Customizer section and label. The Customizer registers controls from this
 * schema and templates read values through t360_setting(), so no visual value
 * needs to be hard-coded in a template.
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

defined( 'ABSPATH' ) || exit;

final class Settings {

	/** Theme-mod prefix, keeps our values apart from core/plugin mods. */
	const PREFIX = 't360_';

	/**
	 * Default order of homepage modules. Keys map to template-parts/home/{key}.php
	 * via Home\Sections.
	 */
	const HOME_ORDER = 'hero,quick_categories,hot_deals,featured,promo_banners,new_arrivals,category_grid,trending,brands,trust,recently_viewed';

	/**
	 * Memoized schema.
	 *
	 * @var array<string, array>|null
	 */
	private static $schema = null;

	/**
	 * Setting definitions.
	 *
	 * @return array<string, array{type:string, default:mixed, section:string, label:string, description?:string, choices?:array}>
	 */
	public static function schema(): array {
		if ( null !== self::$schema ) {
			return self::$schema;
		}

		$s = array(
			// Brand.
			'color_primary'       => array( 'color', '#0B1220', 'brand', __( 'Primary color', 'the360hub' ), __( 'Headings, brand surfaces and the Buy now button.', 'the360hub' ) ),
			'color_secondary'     => array( 'color', '#1D4ED8', 'brand', __( 'Secondary color', 'the360hub' ), __( 'Links, active navigation and selected states.', 'the360hub' ) ),
			'color_cta'           => array( 'color', '#1238C4', 'brand', __( 'Call-to-action color', 'the360hub' ), __( 'Add to cart and primary buttons. Text colour on top is chosen automatically for contrast.', 'the360hub' ) ),
			'currency_label'      => array( 'text', 'AED', 'brand', __( 'Currency label for AED', 'the360hub' ), __( 'Replaces WooCommerce\'s Arabic dirham symbol for AED prices. Leave empty to keep the WooCommerce symbol.', 'the360hub' ) ),

			// Typography.
			'font_family'         => array(
				'select',
				'brand',
				'typography',
				__( 'Font family', 'the360hub' ),
				__( 'Brand fonts are self-hosted (about 70KB, cached). System fonts download nothing.', 'the360hub' ),
				array(
					'brand'  => __( 'Brand: Rubik + Nunito Sans', 'the360hub' ),
					'system' => __( 'System fonts (fastest)', 'the360hub' ),
				),
			),

			// Header.
			'header_notice'       => array( 'text', '', 'header', __( 'Announcement bar text', 'the360hub' ), __( 'Short message above the header. Leave empty to hide.', 'the360hub' ) ),
			'header_notice_url'   => array( 'url', '', 'header', __( 'Announcement bar link', 'the360hub' ) ),
			'header_search_ph'    => array( 'text', __( 'Search products and brands…', 'the360hub' ), 'header', __( 'Search placeholder', 'the360hub' ) ),
			'header_hide_scroll'  => array( 'checkbox', true, 'header', __( 'Tuck the logo row away while scrolling down (mobile)', 'the360hub' ), __( 'The search bar stays pinned.', 'the360hub' ) ),

			// Mobile navigation.
			'bottom_nav'          => array( 'checkbox', true, 'mobile_nav', __( 'Show bottom navigation on mobile', 'the360hub' ) ),
			'categories_page'     => array( 'page', 0, 'mobile_nav', __( 'Categories tab destination', 'the360hub' ), __( 'Leave empty to open the category menu instead.', 'the360hub' ) ),
			'wishlist_page'       => array( 'page', 0, 'mobile_nav', __( 'Wishlist page', 'the360hub' ), __( 'A page using the "Wishlist" template. Created automatically on theme activation.', 'the360hub' ) ),
			'whatsapp_number'     => array( 'text', '', 'mobile_nav', __( 'WhatsApp number', 'the360hub' ), __( 'International format without + or spaces, e.g. 9715XXXXXXXX. Shown in the menu and footer.', 'the360hub' ) ),

			// Search.
			'popular_searches'    => array( 'textarea', "iPhone\nSamsung Galaxy\nAirPods\nPlayStation 5\nMacBook\nSmart watch", 'search', __( 'Popular searches', 'the360hub' ), __( 'One per line. Shown when the search box is empty.', 'the360hub' ) ),

			// Product cards & catalogue.
			'card_add_to_cart'    => array( 'checkbox', true, 'catalog', __( 'Show Add to cart on product cards', 'the360hub' ) ),
			'card_in_stock'       => array( 'checkbox', false, 'catalog', __( 'Show "In stock" on cards', 'the360hub' ), __( 'Low stock and out of stock are always shown.', 'the360hub' ) ),
			'card_brand'          => array( 'checkbox', true, 'catalog', __( 'Show brand name on cards', 'the360hub' ) ),
			'products_per_page'   => array( 'number', 24, 'catalog', __( 'Products per page', 'the360hub' ) ),

			// Homepage layout.
			'home_order'          => array(
				'text',
				self::HOME_ORDER,
				'home',
				__( 'Section order', 'the360hub' ),
				sprintf(
										/* translators: %s: list of section keys */
					__( 'Comma-separated. Available: %s. Remove a key to hide that section.', 'the360hub' ),
					str_replace( ',', ', ', self::HOME_ORDER )
				),
			),
			'home_h1'             => array( 'text', __( 'The360Hub — electronics and technology store in the UAE', 'the360hub' ), 'home', __( 'Homepage H1 (visually hidden, for SEO and screen readers)', 'the360hub' ) ),

			// Hero.
			'hero_heading'        => array( 'text', __( 'The latest tech, delivered across the UAE', 'the360hub' ), 'home_hero', __( 'Heading', 'the360hub' ), __( 'Shown when no banner images are set, and used as the first banner\'s alt text fallback.', 'the360hub' ) ),
			'hero_text'           => array( 'text', __( 'Phones, laptops, gaming and smart home from brands you trust.', 'the360hub' ), 'home_hero', __( 'Sub-heading', 'the360hub' ) ),
			'hero_cta_label'      => array( 'text', __( 'Shop now', 'the360hub' ), 'home_hero', __( 'Button label', 'the360hub' ) ),
			'hero_cta_url'        => array( 'url', '', 'home_hero', __( 'Button link', 'the360hub' ), __( 'Defaults to the shop page.', 'the360hub' ) ),

			// Product sections.
			'deals_title'         => array( 'text', __( 'Hot deals', 'the360hub' ), 'home_products', __( 'Hot deals title', 'the360hub' ) ),
			'featured_title'      => array( 'text', __( 'Featured', 'the360hub' ), 'home_products', __( 'Featured title', 'the360hub' ) ),
			'new_title'           => array( 'text', __( 'New arrivals', 'the360hub' ), 'home_products', __( 'New arrivals title', 'the360hub' ) ),
			'trending_title'      => array( 'text', __( 'Trending now', 'the360hub' ), 'home_products', __( 'Trending title', 'the360hub' ) ),
			'home_products_count' => array( 'number', 10, 'home_products', __( 'Products per section', 'the360hub' ) ),
			'quick_cats_title'    => array( 'text', __( 'Categories', 'the360hub' ), 'home_products', __( 'Quick categories title (visually hidden)', 'the360hub' ) ),
			'category_grid_title' => array( 'text', __( 'Shop by category', 'the360hub' ), 'home_products', __( 'Shop by category title', 'the360hub' ) ),
			'brands_title'        => array( 'text', __( 'Top brands', 'the360hub' ), 'home_products', __( 'Brands title', 'the360hub' ) ),
			'recent_title'        => array( 'text', __( 'Recently viewed', 'the360hub' ), 'home_products', __( 'Recently viewed title', 'the360hub' ) ),

			// Trust / services.
			'trust_1_title'       => array( 'text', __( 'Fast UAE delivery', 'the360hub' ), 'home_trust', __( 'Item 1 title', 'the360hub' ) ),
			'trust_1_text'        => array( 'text', __( 'To every emirate', 'the360hub' ), 'home_trust', __( 'Item 1 text', 'the360hub' ) ),
			'trust_2_title'       => array( 'text', __( 'Genuine products', 'the360hub' ), 'home_trust', __( 'Item 2 title', 'the360hub' ) ),
			'trust_2_text'        => array( 'text', __( 'With official warranty', 'the360hub' ), 'home_trust', __( 'Item 2 text', 'the360hub' ) ),
			'trust_3_title'       => array( 'text', __( 'Easy returns', 'the360hub' ), 'home_trust', __( 'Item 3 title', 'the360hub' ) ),
			'trust_3_text'        => array( 'text', __( 'Hassle-free process', 'the360hub' ), 'home_trust', __( 'Item 3 text', 'the360hub' ) ),
			'trust_4_title'       => array( 'text', __( 'Flexible payment', 'the360hub' ), 'home_trust', __( 'Item 4 title', 'the360hub' ) ),
			'trust_4_text'        => array( 'text', __( 'Card, instalments or cash on delivery', 'the360hub' ), 'home_trust', __( 'Item 4 text', 'the360hub' ) ),

			// Footer.
			'footer_about'        => array( 'textarea', __( 'The360Hub is a UAE online store for electronics and technology.', 'the360hub' ), 'footer', __( 'About text', 'the360hub' ) ),
			'footer_phone'        => array( 'text', '', 'footer', __( 'Phone', 'the360hub' ) ),
			'footer_email'        => array( 'email', '', 'footer', __( 'Email', 'the360hub' ) ),
			'footer_payments'     => array( 'text', 'Visa, Mastercard, Apple Pay, Tabby, Tamara, Cash on delivery', 'footer', __( 'Payment methods (comma-separated)', 'the360hub' ) ),
			'footer_copyright'    => array( 'text', '', 'footer', __( 'Copyright text', 'the360hub' ), __( 'Defaults to "© {year} {site name}".', 'the360hub' ) ),

			// Performance.
			'images_webp'         => array( 'checkbox', true, 'performance', __( 'Save new JPEG uploads as WebP', 'the360hub' ), __( 'Applies to image sizes generated after this is enabled, when the server supports WebP.', 'the360hub' ) ),
		);

		// Banner slots: hero (3) and promotional banners (2).
		for ( $i = 1; $i <= 3; $i++ ) {
			/* translators: %d: slot number */
			$s[ "hero_{$i}_image" ] = array( 'image', 0, 'home_hero', sprintf( __( 'Banner %d image (desktop, 1920×640)', 'the360hub' ), $i ) );
			/* translators: %d: slot number */
			$s[ "hero_{$i}_mobile" ] = array( 'image', 0, 'home_hero', sprintf( __( 'Banner %d image (mobile, 1080×1080)', 'the360hub' ), $i ) );
			/* translators: %d: slot number */
			$s[ "hero_{$i}_url" ] = array( 'url', '', 'home_hero', sprintf( __( 'Banner %d link', 'the360hub' ), $i ) );
			/* translators: %d: slot number */
			$s[ "hero_{$i}_alt" ] = array( 'text', '', 'home_hero', sprintf( __( 'Banner %d description (alt text)', 'the360hub' ), $i ) );
		}
		for ( $i = 1; $i <= 2; $i++ ) {
			/* translators: %d: slot number */
			$s[ "promo_{$i}_image" ] = array( 'image', 0, 'home_promos', sprintf( __( 'Promotion %d image (1200×600)', 'the360hub' ), $i ) );
			/* translators: %d: slot number */
			$s[ "promo_{$i}_url" ] = array( 'url', '', 'home_promos', sprintf( __( 'Promotion %d link', 'the360hub' ), $i ) );
			/* translators: %d: slot number */
			$s[ "promo_{$i}_alt" ] = array( 'text', '', 'home_promos', sprintf( __( 'Promotion %d description (alt text)', 'the360hub' ), $i ) );
		}

		self::$schema = array();
		foreach ( $s as $key => $def ) {
			self::$schema[ $key ] = array(
				'type'        => $def[0],
				'default'     => $def[1],
				'section'     => $def[2],
				'label'       => $def[3],
				'description' => $def[4] ?? '',
				'choices'     => $def[5] ?? array(),
			);
		}

		/**
		 * Filters the theme settings schema. Child themes and plugins can add
		 * or adjust settings here.
		 *
		 * @param array $schema Setting definitions keyed by setting name.
		 */
		self::$schema = apply_filters( 'the360hub_settings_schema', self::$schema );

		return self::$schema;
	}

	/**
	 * Read a setting, falling back to its schema default.
	 *
	 * @param string $key Setting key without prefix.
	 * @return mixed
	 */
	public static function get( string $key ) {
		$schema  = self::schema();
		$default = $schema[ $key ]['default'] ?? null;

		return get_theme_mod( self::PREFIX . $key, $default );
	}

	/**
	 * Sanitize a value for a given setting type. Used by the Customizer and
	 * safe to call anywhere a setting is written.
	 *
	 * @param mixed  $value Raw value.
	 * @param string $key   Setting key without prefix.
	 * @return mixed
	 */
	public static function sanitize( $value, string $key ) {
		$def = self::schema()[ $key ] ?? null;
		if ( ! $def ) {
			return null;
		}

		switch ( $def['type'] ) {
			case 'color':
				$color = sanitize_hex_color( $value );
				return $color ? $color : $def['default'];
			case 'checkbox':
				return (bool) $value;
			case 'number':
				return max( 1, min( 100, absint( $value ) ) );
			case 'image':
			case 'page':
				return absint( $value );
			case 'url':
				return esc_url_raw( $value );
			case 'email':
				return sanitize_email( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'select':
				return array_key_exists( $value, $def['choices'] ) ? $value : $def['default'];
			default:
				return sanitize_text_field( $value );
		}
	}
}
