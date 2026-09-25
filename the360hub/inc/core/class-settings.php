<?php
/**
 * Central theme configuration.
 *
 * Every configurable value is declared once in schema(): its type, default,
 * Customizer section and label. The Customizer builds its controls from this
 * schema and templates read values through t360_setting(), so no visual value
 * is hard-coded in a template.
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

defined( 'ABSPATH' ) || exit;

final class Settings {

	/** Theme-mod prefix, keeps our values apart from core/plugin mods. */
	const PREFIX = 't360_';

	/**
	 * Homepage modules shown by default, in order. All available modules are
	 * listed in Home\Sections::registry().
	 */
	const HOME_ORDER = 'hero,categories,flash_deals,bento,product_tabs,spotlight_1,brands,new_arrivals,cta_banner,spotlight_2,trust,recently_viewed';

	/**
	 * Memoized schema.
	 *
	 * @var array<string, array>|null
	 */
	private static $schema = null;

	/**
	 * Colour presets. Each one is a complete, contrast-checked palette
	 * (tools/check-presets.php); individual colour settings override single
	 * values on top of the chosen preset.
	 *
	 * @return array<string, array{label:string, colors:array<string,string>}>
	 */
	public static function presets(): array {
		return array(
			'electric' => array(
				'label'  => __( 'Electric blue', 'the360hub' ),
				'colors' => array(
					'brand'     => '#0B1220',
					'cta'       => '#2563EB',
					'deal'      => '#E11D48',
					'highlight' => '#FACC15',
					'header'    => '#0B1220',
					'topbar'    => '#1E293B',
					'footer'    => '#0B1220',
					'page'      => '#F2F4F8',
				),
			),
			'midnight' => array(
				'label'  => __( 'Midnight violet', 'the360hub' ),
				'colors' => array(
					'brand'     => '#111827',
					'cta'       => '#7C3AED',
					'deal'      => '#DC2626',
					'highlight' => '#A3E635',
					'header'    => '#111827',
					'topbar'    => '#312E81',
					'footer'    => '#111827',
					'page'      => '#F3F3F7',
				),
			),
			'emerald'  => array(
				'label'  => __( 'Emerald', 'the360hub' ),
				'colors' => array(
					'brand'     => '#052E2B',
					'cta'       => '#047857',
					'deal'      => '#DC2626',
					'highlight' => '#FBBF24',
					'header'    => '#052E2B',
					'topbar'    => '#064E3B',
					'footer'    => '#052E2B',
					'page'      => '#F1F5F3',
				),
			),
			'sunset'   => array(
				'label'  => __( 'Sunset orange', 'the360hub' ),
				'colors' => array(
					'brand'     => '#1C1917',
					'cta'       => '#C2410C',
					'deal'      => '#B91C1C',
					'highlight' => '#FDBA74',
					'header'    => '#FFFFFF',
					'topbar'    => '#1C1917',
					'footer'    => '#1C1917',
					'page'      => '#F7F5F2',
				),
			),
			'ocean'    => array(
				'label'  => __( 'Ocean', 'the360hub' ),
				'colors' => array(
					'brand'     => '#082F49',
					'cta'       => '#0369A1',
					'deal'      => '#E11D48',
					'highlight' => '#67E8F9',
					'header'    => '#0C4A6E',
					'topbar'    => '#082F49',
					'footer'    => '#082F49',
					'page'      => '#F0F5F9',
				),
			),
			'mono'     => array(
				'label'  => __( 'Monochrome', 'the360hub' ),
				'colors' => array(
					'brand'     => '#0A0A0A',
					'cta'       => '#0A0A0A',
					'deal'      => '#DC2626',
					'highlight' => '#E5E5E5',
					'header'    => '#FFFFFF',
					'topbar'    => '#0A0A0A',
					'footer'    => '#0A0A0A',
					'page'      => '#F5F5F5',
				),
			),
		);
	}

	/**
	 * Font pairings. Only the selected pairing's files are loaded.
	 *
	 * @return array<string, array{label:string, heading:array, body:array}>
	 */
	public static function fonts(): array {
		return array(
			'jakarta' => array(
				'label'   => 'Plus Jakarta Sans',
				'heading' => array( 'Plus Jakarta Sans', 'plus-jakarta-sans-latin-wght-normal.woff2', '200 800' ),
				'body'    => array( 'Plus Jakarta Sans', 'plus-jakarta-sans-latin-wght-normal.woff2', '200 800' ),
			),
			'outfit'  => array(
				'label'   => 'Outfit + Inter',
				'heading' => array( 'Outfit', 'outfit-latin-wght-normal.woff2', '100 900' ),
				'body'    => array( 'Inter', 'inter-latin-wght-normal.woff2', '100 900' ),
			),
			'sora'    => array(
				'label'   => 'Sora + Inter',
				'heading' => array( 'Sora', 'sora-latin-wght-normal.woff2', '100 800' ),
				'body'    => array( 'Inter', 'inter-latin-wght-normal.woff2', '100 900' ),
			),
			'manrope' => array(
				'label'   => 'Manrope',
				'heading' => array( 'Manrope', 'manrope-latin-wght-normal.woff2', '200 800' ),
				'body'    => array( 'Manrope', 'manrope-latin-wght-normal.woff2', '200 800' ),
			),
			'rubik'   => array(
				'label'   => 'Rubik + Nunito Sans',
				'heading' => array( 'Rubik', 'rubik-latin-wght-normal.woff2', '300 900' ),
				'body'    => array( 'Nunito Sans', 'nunito-sans-latin-wght-normal.woff2', '200 1000' ),
			),
		);
	}

	/**
	 * Setting definitions.
	 *
	 * Row format: [ type, default, section, label, description, extra ]
	 * where extra is choices (select) or [min, max, step] (range).
	 *
	 * @return array<string, array>
	 */
	public static function schema(): array {
		if ( null !== self::$schema ) {
			return self::$schema;
		}

		$presets = array();
		foreach ( self::presets() as $key => $preset ) {
			$presets[ $key ] = $preset['label'];
		}
		$fonts = array();
		foreach ( self::fonts() as $key => $pair ) {
			$fonts[ $key ] = $pair['label'];
		}
		$fonts['system'] = __( 'System fonts (fastest, no download)', 'the360hub' );

		$light_dark = array(
			'light' => __( 'Light text', 'the360hub' ),
			'dark'  => __( 'Dark text', 'the360hub' ),
		);
		$override   = __( 'Leave empty to use the colour preset.', 'the360hub' );

		$s = array(
			/* ---- Colours ---------------------------------------------------- */
			'color_preset'            => array( 'select', 'electric', 'colors', __( 'Colour preset', 'the360hub' ), __( 'A complete palette. The colours below override single values.', 'the360hub' ), $presets ),
			'color_brand'             => array( 'color', '', 'colors', __( 'Brand (dark) colour', 'the360hub' ), __( 'Headings, dark sections, Buy now.', 'the360hub' ) . ' ' . $override ),
			'color_cta'               => array( 'color', '', 'colors', __( 'Accent / button colour', 'the360hub' ), __( 'Add to cart, links, active states. Text on top is picked automatically for contrast.', 'the360hub' ) . ' ' . $override ),
			'color_deal'              => array( 'color', '', 'colors', __( 'Sale colour', 'the360hub' ), __( 'Discount badges and sale prices.', 'the360hub' ) . ' ' . $override ),
			'color_highlight'         => array( 'color', '', 'colors', __( 'Highlight colour', 'the360hub' ), __( 'Countdown, "Hot" badges, highlight links.', 'the360hub' ) . ' ' . $override ),
			'color_header'            => array( 'color', '', 'colors', __( 'Header background', 'the360hub' ), $override ),
			'color_topbar'            => array( 'color', '', 'colors', __( 'Top bar background', 'the360hub' ), $override ),
			'color_footer'            => array( 'color', '', 'colors', __( 'Footer background', 'the360hub' ), $override ),
			'color_page'              => array( 'color', '', 'colors', __( 'Page background', 'the360hub' ), $override ),

			/* ---- Style ------------------------------------------------------ */
			'style_radius'            => array(
				'select',
				'soft',
				'style',
				__( 'Corner style', 'the360hub' ),
				'',
				array(
					'sharp'   => __( 'Sharp (4px)', 'the360hub' ),
					'rounded' => __( 'Rounded (10px)', 'the360hub' ),
					'soft'    => __( 'Soft (16px)', 'the360hub' ),
					'round'   => __( 'Extra round (22px)', 'the360hub' ),
				),
			),
			'style_buttons'           => array(
				'select',
				'rounded',
				'style',
				__( 'Button shape', 'the360hub' ),
				'',
				array(
					'square'  => __( 'Square', 'the360hub' ),
					'rounded' => __( 'Rounded (follows corner style)', 'the360hub' ),
					'pill'    => __( 'Pill', 'the360hub' ),
				),
			),
			'style_shadow'            => array(
				'select',
				'subtle',
				'style',
				__( 'Shadows', 'the360hub' ),
				'',
				array(
					'none'   => __( 'None (flat)', 'the360hub' ),
					'subtle' => __( 'Subtle', 'the360hub' ),
					'medium' => __( 'Medium', 'the360hub' ),
				),
			),
			'style_container'         => array( 'range', 1400, 'style', __( 'Content width (px)', 'the360hub' ), '', array( 1100, 1680, 20 ) ),
			'style_animations'        => array( 'checkbox', true, 'style', __( 'Hover and entrance animations', 'the360hub' ), __( 'Always off for visitors who ask their device for reduced motion.', 'the360hub' ) ),

			/* ---- Typography ------------------------------------------------- */
			'font_family'             => array( 'select', 'jakarta', 'typography', __( 'Font pairing', 'the360hub' ), __( 'Self-hosted; only the chosen pairing is downloaded (25–50KB, cached).', 'the360hub' ), $fonts ),
			'font_size'               => array(
				'select',
				'16',
				'typography',
				__( 'Base text size', 'the360hub' ),
				'',
				array(
					'15' => '15px',
					'16' => __( '16px (recommended)', 'the360hub' ),
					'17' => '17px',
				),
			),
			'font_heading_weight'     => array(
				'select',
				'700',
				'typography',
				__( 'Heading weight', 'the360hub' ),
				'',
				array(
					'600' => __( 'Semi-bold', 'the360hub' ),
					'700' => __( 'Bold', 'the360hub' ),
					'800' => __( 'Extra bold', 'the360hub' ),
				),
			),

			/* ---- Top bar ---------------------------------------------------- */
			'topbar_enable'           => array( 'checkbox', true, 'topbar', __( 'Show top bar', 'the360hub' ) ),
			'header_notice'           => array( 'text', __( 'Free delivery across the UAE on orders over AED 100', 'the360hub' ), 'topbar', __( 'Announcement', 'the360hub' ), __( 'Shown on all screen sizes. Leave empty to hide.', 'the360hub' ) ),
			'header_notice_url'       => array( 'url', '', 'topbar', __( 'Announcement link', 'the360hub' ) ),
			'topbar_links'            => array( 'textarea', "Track order|/my-account/orders/\nHelp centre|/contact/", 'topbar', __( 'Top bar links (desktop)', 'the360hub' ), __( 'One per line: Label|URL', 'the360hub' ) ),

			/* ---- Header ----------------------------------------------------- */
			'header_style'            => array(
				'select',
				'dark',
				'header',
				__( 'Header style', 'the360hub' ),
				'',
				array(
					'dark'  => __( 'Dark (preset header colour)', 'the360hub' ),
					'light' => __( 'Light (white)', 'the360hub' ),
					'brand' => __( 'Accent colour', 'the360hub' ),
				),
			),
			'header_sticky'           => array( 'checkbox', true, 'header', __( 'Sticky header', 'the360hub' ) ),
			'header_hide_scroll'      => array( 'checkbox', true, 'header', __( 'Tuck the logo row away while scrolling down (mobile)', 'the360hub' ), __( 'The search bar stays pinned.', 'the360hub' ) ),
			'header_search_ph'        => array( 'text', __( 'What are you looking for?', 'the360hub' ), 'header', __( 'Search placeholder', 'the360hub' ) ),
			'header_account_label'    => array( 'checkbox', true, 'header', __( 'Show text labels next to header icons (desktop)', 'the360hub' ) ),
			'logo_height_mobile'      => array( 'range', 30, 'header', __( 'Logo height on mobile (px)', 'the360hub' ), '', array( 20, 56, 1 ) ),
			'logo_height_desktop'     => array( 'range', 40, 'header', __( 'Logo height on desktop (px)', 'the360hub' ), '', array( 24, 80, 1 ) ),
			'mega_menu'               => array( 'checkbox', true, 'header', __( 'Desktop "All categories" mega menu', 'the360hub' ) ),
			'nav_highlight_label'     => array( 'text', __( 'Hot deals', 'the360hub' ), 'header', __( 'Highlighted nav link label', 'the360hub' ), __( 'Shown at the end of the desktop category bar. Leave empty to hide.', 'the360hub' ) ),
			'nav_highlight_url'       => array( 'url', '', 'header', __( 'Highlighted nav link URL', 'the360hub' ), __( 'Defaults to the on-sale products view.', 'the360hub' ) ),

			/* ---- Mobile navigation ------------------------------------------ */
			'bottom_nav'              => array( 'checkbox', true, 'mobile_nav', __( 'Show bottom navigation on mobile', 'the360hub' ) ),
			'categories_page'         => array( 'page', 0, 'mobile_nav', __( 'Categories tab destination', 'the360hub' ), __( 'Leave empty to open the category menu instead.', 'the360hub' ) ),
			'wishlist_page'           => array( 'page', 0, 'mobile_nav', __( 'Wishlist page', 'the360hub' ), __( 'A page using the "Wishlist" template. Created automatically on theme activation.', 'the360hub' ) ),
			'whatsapp_number'         => array( 'text', '', 'mobile_nav', __( 'WhatsApp number', 'the360hub' ), __( 'International format without + or spaces, e.g. 9715XXXXXXXX.', 'the360hub' ) ),
			'whatsapp_float'          => array( 'checkbox', true, 'mobile_nav', __( 'Floating WhatsApp button', 'the360hub' ), __( 'Needs a WhatsApp number.', 'the360hub' ) ),
			'whatsapp_message'        => array( 'text', __( 'Hi, I have a question about a product.', 'the360hub' ), 'mobile_nav', __( 'WhatsApp pre-filled message', 'the360hub' ) ),

			/* ---- Search ----------------------------------------------------- */
			'popular_searches'        => array( 'textarea', "iPhone\nSamsung Galaxy\nAirPods\nPlayStation 5\nMacBook\nSmart watch", 'search', __( 'Popular searches', 'the360hub' ), __( 'One per line. Shown when the search box is empty.', 'the360hub' ) ),

			/* ---- Product cards ---------------------------------------------- */
			'card_style'              => array(
				'select',
				'elevated',
				'cards',
				__( 'Card style', 'the360hub' ),
				'',
				array(
					'elevated' => __( 'White card, lifts on hover', 'the360hub' ),
					'bordered' => __( 'Outlined', 'the360hub' ),
					'minimal'  => __( 'Minimal (no card)', 'the360hub' ),
				),
			),
			'card_image_ratio'        => array(
				'select',
				'square',
				'cards',
				__( 'Image shape', 'the360hub' ),
				'',
				array(
					'square'   => __( 'Square 1:1', 'the360hub' ),
					'portrait' => __( 'Portrait 4:5', 'the360hub' ),
				),
			),
			'card_image_fit'          => array(
				'select',
				'contain',
				'cards',
				__( 'Image fit', 'the360hub' ),
				__( 'Contain shows the whole product; Cover fills the frame.', 'the360hub' ),
				array(
					'contain' => __( 'Contain', 'the360hub' ),
					'cover'   => __( 'Cover', 'the360hub' ),
				),
			),
			'card_hover_image'        => array( 'checkbox', true, 'cards', __( 'Show second gallery image on hover (desktop)', 'the360hub' ) ),
			'card_add_to_cart'        => array(
				'select',
				'button',
				'cards',
				__( 'Add to cart', 'the360hub' ),
				'',
				array(
					'button' => __( 'Full-width button', 'the360hub' ),
					'icon'   => __( 'Round icon button', 'the360hub' ),
					'none'   => __( 'Hidden', 'the360hub' ),
				),
			),
			'card_brand'              => array( 'checkbox', true, 'cards', __( 'Show brand', 'the360hub' ) ),
			'card_rating'             => array( 'checkbox', true, 'cards', __( 'Show rating', 'the360hub' ) ),
			'card_in_stock'           => array( 'checkbox', false, 'cards', __( 'Show "In stock"', 'the360hub' ), __( 'Low stock and out of stock are always shown.', 'the360hub' ) ),
			'card_new_days'           => array( 'range', 30, 'cards', __( '"New" badge for products added in the last N days', 'the360hub' ), __( '0 turns the badge off.', 'the360hub' ), array( 0, 120, 1 ) ),
			'card_free_delivery'      => array( 'range', 100, 'cards', __( '"Free delivery" label from this price (AED)', 'the360hub' ), __( '0 turns the label off.', 'the360hub' ), array( 0, 1000, 10 ) ),
			'card_installments'       => array( 'checkbox', false, 'cards', __( 'Show instalment price', 'the360hub' ), __( 'Only enable when an instalment gateway such as Tabby or Tamara is active.', 'the360hub' ) ),
			'card_installments_n'     => array( 'range', 4, 'cards', __( 'Number of instalments', 'the360hub' ), '', array( 2, 12, 1 ) ),
			/* translators: %s: instalment amount (keep the placeholder) */
			'card_installments_text'  => array( 'text', __( 'or 4 payments of %s', 'the360hub' ), 'cards', __( 'Instalment text', 'the360hub' ), __( '%s is replaced by the amount.', 'the360hub' ) ),
			'currency_label'          => array( 'text', 'AED', 'cards', __( 'Currency label for AED', 'the360hub' ), __( 'Replaces WooCommerce\'s Arabic dirham symbol. Leave empty to keep it.', 'the360hub' ) ),

			/* ---- Catalogue -------------------------------------------------- */
			'products_per_page'       => array( 'range', 24, 'catalog', __( 'Products per page', 'the360hub' ), '', array( 8, 96, 4 ) ),
			'shop_columns'            => array(
				'select',
				'4',
				'catalog',
				__( 'Columns on desktop', 'the360hub' ),
				__( 'Mobile always shows 2, tablet 3.', 'the360hub' ),
				array(
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
			),

			/* ---- Homepage layout -------------------------------------------- */
			'home_order'              => array( 'sortable', self::HOME_ORDER, 'home', __( 'Homepage sections', 'the360hub' ), __( 'Tick to show, use the arrows to reorder.', 'the360hub' ) ),
			'home_h1'                 => array( 'text', __( 'The360Hub — electronics and gadgets store in the UAE', 'the360hub' ), 'home', __( 'Homepage H1 (hidden, for SEO and screen readers)', 'the360hub' ) ),
			'home_products_count'     => array( 'range', 10, 'home', __( 'Products per homepage row', 'the360hub' ), '', array( 4, 20, 1 ) ),

			/* ---- Hero ------------------------------------------------------- */
			'hero_layout'             => array(
				'select',
				'bento',
				'home_hero',
				__( 'Hero layout', 'the360hub' ),
				'',
				array(
					'bento' => __( 'Slider + two side tiles', 'the360hub' ),
					'full'  => __( 'Full-width slider', 'the360hub' ),
				),
			),
			'hero_autoplay'           => array( 'checkbox', true, 'home_hero', __( 'Auto-advance slides', 'the360hub' ), __( 'Pauses on hover, focus and for reduced-motion visitors; a pause button is always shown.', 'the360hub' ) ),
			'hero_interval'           => array( 'range', 6, 'home_hero', __( 'Seconds per slide', 'the360hub' ), '', array( 3, 15, 1 ) ),

			/* ---- Flash deals ------------------------------------------------ */
			'flash_title'             => array( 'text', __( 'Flash deals', 'the360hub' ), 'home_flash', __( 'Title', 'the360hub' ) ),
			'flash_subtitle'          => array( 'text', __( 'Limited stock at these prices', 'the360hub' ), 'home_flash', __( 'Subtitle', 'the360hub' ) ),
			'flash_timer'             => array(
				'select',
				'daily',
				'home_flash',
				__( 'Countdown', 'the360hub' ),
				__( 'Daily resets at midnight in the site timezone.', 'the360hub' ),
				array(
					'daily' => __( 'Ends at midnight every day', 'the360hub' ),
					'date'  => __( 'Ends at a set date and time', 'the360hub' ),
					'off'   => __( 'No countdown', 'the360hub' ),
				),
			),
			'flash_end'               => array( 'datetime', '', 'home_flash', __( 'End date and time', 'the360hub' ), __( 'Used when the countdown ends at a set date. The section hides itself after it ends.', 'the360hub' ) ),
			'flash_category'          => array( 'category', 0, 'home_flash', __( 'Only on-sale products from', 'the360hub' ), __( 'Leave on "All" for every on-sale product.', 'the360hub' ) ),

			/* ---- Product tabs ----------------------------------------------- */
			'tabs_title'              => array( 'text', __( 'Shop our picks', 'the360hub' ), 'home_tabs', __( 'Title', 'the360hub' ) ),
			'tabs_items'              => array( 'text', 'featured,bestsellers,new,top_rated', 'home_tabs', __( 'Tabs', 'the360hub' ), __( 'Comma-separated, in order. Available: featured, bestsellers, new, top_rated, deals.', 'the360hub' ) ),

			/* ---- Section titles --------------------------------------------- */
			'categories_title'        => array( 'text', __( 'Shop by category', 'the360hub' ), 'home_titles', __( 'Categories', 'the360hub' ) ),
			'categories_style'        => array(
				'select',
				'circle',
				'home_titles',
				__( 'Category icon style', 'the360hub' ),
				'',
				array(
					'circle' => __( 'Circles', 'the360hub' ),
					'tile'   => __( 'Rounded tiles', 'the360hub' ),
				),
			),
			'deals_title'             => array( 'text', __( 'Hot deals', 'the360hub' ), 'home_titles', __( 'Hot deals', 'the360hub' ) ),
			'featured_title'          => array( 'text', __( 'Featured', 'the360hub' ), 'home_titles', __( 'Featured', 'the360hub' ) ),
			'new_title'               => array( 'text', __( 'New arrivals', 'the360hub' ), 'home_titles', __( 'New arrivals', 'the360hub' ) ),
			'trending_title'          => array( 'text', __( 'Best sellers', 'the360hub' ), 'home_titles', __( 'Best sellers', 'the360hub' ) ),
			'top_rated_title'         => array( 'text', __( 'Top rated', 'the360hub' ), 'home_titles', __( 'Top rated', 'the360hub' ) ),
			'brands_title'            => array( 'text', __( 'Shop by brand', 'the360hub' ), 'home_titles', __( 'Brands', 'the360hub' ) ),
			'brands_grayscale'        => array( 'checkbox', false, 'home_titles', __( 'Grey brand logos (colour on hover)', 'the360hub' ) ),
			'recent_title'            => array( 'text', __( 'Recently viewed', 'the360hub' ), 'home_titles', __( 'Recently viewed', 'the360hub' ) ),

			/* ---- CTA banner ------------------------------------------------- */
			'cta_title'               => array( 'text', __( 'Not sure which one to pick?', 'the360hub' ), 'home_cta', __( 'Title', 'the360hub' ) ),
			'cta_text'                => array( 'text', __( 'Our tech team answers on WhatsApp in minutes — compare specs, check stock and get advice.', 'the360hub' ), 'home_cta', __( 'Text', 'the360hub' ) ),
			'cta_button'              => array( 'text', __( 'Chat with an expert', 'the360hub' ), 'home_cta', __( 'Button label', 'the360hub' ) ),
			'cta_url'                 => array( 'url', '', 'home_cta', __( 'Button link', 'the360hub' ), __( 'Defaults to your WhatsApp number. The banner hides when neither is set.', 'the360hub' ) ),
			'cta_image'               => array( 'image', 0, 'home_cta', __( 'Image (optional)', 'the360hub' ) ),
			'cta_bg'                  => array( 'color', '', 'home_cta', __( 'Background', 'the360hub' ), __( 'Defaults to the accent colour.', 'the360hub' ) ),

			/* ---- Trust ------------------------------------------------------ */
			'trust_1_title'           => array( 'text', __( 'Fast UAE delivery', 'the360hub' ), 'home_trust', __( 'Item 1 title', 'the360hub' ) ),
			'trust_1_text'            => array( 'text', __( 'To every emirate', 'the360hub' ), 'home_trust', __( 'Item 1 text', 'the360hub' ) ),
			'trust_2_title'           => array( 'text', __( 'Genuine products', 'the360hub' ), 'home_trust', __( 'Item 2 title', 'the360hub' ) ),
			'trust_2_text'            => array( 'text', __( 'With official warranty', 'the360hub' ), 'home_trust', __( 'Item 2 text', 'the360hub' ) ),
			'trust_3_title'           => array( 'text', __( 'Easy returns', 'the360hub' ), 'home_trust', __( 'Item 3 title', 'the360hub' ) ),
			'trust_3_text'            => array( 'text', __( 'Hassle-free process', 'the360hub' ), 'home_trust', __( 'Item 3 text', 'the360hub' ) ),
			'trust_4_title'           => array( 'text', __( 'Flexible payment', 'the360hub' ), 'home_trust', __( 'Item 4 title', 'the360hub' ) ),
			'trust_4_text'            => array( 'text', __( 'Card, instalments or cash on delivery', 'the360hub' ), 'home_trust', __( 'Item 4 text', 'the360hub' ) ),

			/* ---- Footer ----------------------------------------------------- */
			'footer_style'            => array(
				'select',
				'dark',
				'footer',
				__( 'Footer style', 'the360hub' ),
				'',
				array(
					'dark'  => __( 'Dark', 'the360hub' ),
					'light' => __( 'Light', 'the360hub' ),
				),
			),
			'footer_about'            => array( 'textarea', __( 'The360Hub is a UAE online store for phones, laptops, gaming, audio and smart home tech.', 'the360hub' ), 'footer', __( 'About text', 'the360hub' ) ),
			'footer_phone'            => array( 'text', '', 'footer', __( 'Phone', 'the360hub' ) ),
			'footer_email'            => array( 'email', '', 'footer', __( 'Email', 'the360hub' ) ),
			'footer_address'          => array( 'text', '', 'footer', __( 'Address', 'the360hub' ) ),
			'footer_newsletter'       => array( 'text', '', 'footer', __( 'Newsletter form shortcode', 'the360hub' ), __( 'e.g. a Mailchimp for WP or MailPoet shortcode. Leave empty to hide.', 'the360hub' ) ),
			'footer_newsletter_title' => array( 'text', __( 'Get deals in your inbox', 'the360hub' ), 'footer', __( 'Newsletter title', 'the360hub' ) ),
			'footer_payments'         => array( 'text', 'Visa, Mastercard, Apple Pay, Tabby, Tamara, Cash on delivery', 'footer', __( 'Payment methods (comma-separated)', 'the360hub' ) ),
			'footer_copyright'        => array( 'text', '', 'footer', __( 'Copyright text', 'the360hub' ), __( 'Defaults to "© {year} {site name}".', 'the360hub' ) ),

			/* ---- Performance ------------------------------------------------ */
			'images_webp'             => array( 'checkbox', true, 'performance', __( 'Save new JPEG uploads as WebP', 'the360hub' ), __( 'Applies to image sizes generated after this is enabled, when the server supports WebP.', 'the360hub' ) ),
		);

		// Social profiles.
		foreach ( self::socials() as $network => $label ) {
			/* translators: %s: social network name */
			$s[ 'social_' . $network ] = array( 'url', '', 'footer', sprintf( __( '%s URL', 'the360hub' ), $label ) );
		}

		// Hero slides: auto-built from featured products unless customised.
		// Empty = follow the colour preset (slide 1 brand, slide 2 accent).
		$slide_bg = array( '', '', '#EEF2FF' );
		for ( $i = 1; $i <= 3; $i++ ) {
			/* translators: %d: slide number */
			$n                        = sprintf( __( 'Slide %d', 'the360hub' ), $i );
			$s[ "hero_{$i}_enable" ]  = array( 'checkbox', true, 'home_hero', $n . ': ' . __( 'show', 'the360hub' ) );
			$s[ "hero_{$i}_image" ]   = array( 'image', 0, 'home_hero', $n . ': ' . __( 'banner image, desktop 1920×640 (optional)', 'the360hub' ), __( 'A finished banner image replaces the designed slide.', 'the360hub' ) );
			$s[ "hero_{$i}_mobile" ]  = array( 'image', 0, 'home_hero', $n . ': ' . __( 'banner image, mobile 1080×1080 (optional)', 'the360hub' ) );
			$s[ "hero_{$i}_product" ] = array( 'number', 0, 'home_hero', $n . ': ' . __( 'product ID to feature', 'the360hub' ), __( '0 = pick a featured product automatically.', 'the360hub' ) );
			$s[ "hero_{$i}_eyebrow" ] = array( 'text', '', 'home_hero', $n . ': ' . __( 'small label', 'the360hub' ), __( 'Empty = product brand.', 'the360hub' ) );
			$s[ "hero_{$i}_title" ]   = array( 'text', '', 'home_hero', $n . ': ' . __( 'title', 'the360hub' ), __( 'Empty = product name.', 'the360hub' ) );
			$s[ "hero_{$i}_text" ]    = array( 'text', '', 'home_hero', $n . ': ' . __( 'text', 'the360hub' ), __( 'Empty = product price.', 'the360hub' ) );
			$s[ "hero_{$i}_cta" ]     = array( 'text', __( 'Shop now', 'the360hub' ), 'home_hero', $n . ': ' . __( 'button label', 'the360hub' ) );
			$s[ "hero_{$i}_url" ]     = array( 'url', '', 'home_hero', $n . ': ' . __( 'link', 'the360hub' ), __( 'Empty = product page.', 'the360hub' ) );
			$s[ "hero_{$i}_bg" ]      = array( 'color', $slide_bg[ $i - 1 ], 'home_hero', $n . ': ' . __( 'background', 'the360hub' ), __( 'Empty = colour preset.', 'the360hub' ) );
			$s[ "hero_{$i}_tone" ]    = array( 'select', 3 === $i ? 'dark' : 'light', 'home_hero', $n . ': ' . __( 'text colour', 'the360hub' ), '', $light_dark );
		}

		// Hero side tiles (bento hero layout): products picked automatically.
		$sides = array(
			1 => array( __( 'Deal of the day', 'the360hub' ), '#FFF4E5' ),
			2 => array( __( 'Just launched', 'the360hub' ), '#E8F1FF' ),
		);
		foreach ( $sides as $i => [ $label, $bg ] ) {
			/* translators: %d: side tile number */
			$n                             = sprintf( __( 'Side tile %d', 'the360hub' ), $i );
			$s[ "hero_side_{$i}_label" ]   = array( 'text', $label, 'home_hero', $n . ': ' . __( 'label', 'the360hub' ) );
			$s[ "hero_side_{$i}_product" ] = array( 'number', 0, 'home_hero', $n . ': ' . __( 'product ID', 'the360hub' ), 1 === $i ? __( '0 = the biggest current discount.', 'the360hub' ) : __( '0 = the newest product.', 'the360hub' ) );
			$s[ "hero_side_{$i}_bg" ]      = array( 'color', $bg, 'home_hero', $n . ': ' . __( 'background', 'the360hub' ) );
		}

		// Promo grid tiles: auto-built from top categories unless customised.
		$tile_bg = array( '#FFF4E5', '#E8F1FF', '#ECFDF3', '#F4EEFF' );
		for ( $i = 1; $i <= 4; $i++ ) {
			/* translators: %d: tile number */
			$n                         = sprintf( __( 'Tile %d', 'the360hub' ), $i );
			$s[ "tile_{$i}_title" ]    = array( 'text', '', 'home_tiles', $n . ': ' . __( 'title', 'the360hub' ), __( 'Empty = a top category name.', 'the360hub' ) );
			$s[ "tile_{$i}_text" ]     = array( 'text', '', 'home_tiles', $n . ': ' . __( 'text', 'the360hub' ) );
			$s[ "tile_{$i}_url" ]      = array( 'url', '', 'home_tiles', $n . ': ' . __( 'link', 'the360hub' ), __( 'Empty = the category page.', 'the360hub' ) );
			$s[ "tile_{$i}_image" ]    = array( 'image', 0, 'home_tiles', $n . ': ' . __( 'image', 'the360hub' ), __( 'Empty = the category image. Transparent PNG/WebP cut-outs look best.', 'the360hub' ) );
			$s[ "tile_{$i}_category" ] = array( 'category', 0, 'home_tiles', $n . ': ' . __( 'category', 'the360hub' ), __( 'Empty = next top-level category.', 'the360hub' ) );
			$s[ "tile_{$i}_bg" ]       = array( 'color', $tile_bg[ $i - 1 ], 'home_tiles', $n . ': ' . __( 'background', 'the360hub' ) );
			$s[ "tile_{$i}_tone" ]     = array( 'select', 'dark', 'home_tiles', $n . ': ' . __( 'text colour', 'the360hub' ), '', $light_dark );
		}

		// Category spotlights: a category banner + its products.
		for ( $i = 1; $i <= 2; $i++ ) {
			/* translators: %d: spotlight number */
			$n                              = sprintf( __( 'Spotlight %d', 'the360hub' ), $i );
			$s[ "spotlight_{$i}_category" ] = array( 'category', 0, 'home_spotlight', $n . ': ' . __( 'category', 'the360hub' ), __( 'Empty = the largest top-level categories.', 'the360hub' ) );
			$s[ "spotlight_{$i}_title" ]    = array( 'text', '', 'home_spotlight', $n . ': ' . __( 'title', 'the360hub' ), __( 'Empty = category name.', 'the360hub' ) );
			$s[ "spotlight_{$i}_text" ]     = array( 'text', '', 'home_spotlight', $n . ': ' . __( 'banner text', 'the360hub' ) );
			$s[ "spotlight_{$i}_image" ]    = array( 'image', 0, 'home_spotlight', $n . ': ' . __( 'banner image (desktop side panel)', 'the360hub' ) );
			$s[ "spotlight_{$i}_bg" ]       = array( 'color', '', 'home_spotlight', $n . ': ' . __( 'banner background', 'the360hub' ), __( 'Empty = colour preset.', 'the360hub' ) );
		}

		self::$schema = array();
		foreach ( $s as $key => $def ) {
			self::$schema[ $key ] = array(
				'type'        => $def[0],
				'default'     => $def[1],
				'section'     => $def[2],
				'label'       => $def[3],
				'description' => $def[4] ?? '',
				'choices'     => 'range' === $def[0] ? array() : ( $def[5] ?? array() ),
				'range'       => 'range' === $def[0] ? ( $def[5] ?? array( 0, 100, 1 ) ) : array(),
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
	 * Supported social networks.
	 *
	 * @return array<string, string>
	 */
	public static function socials(): array {
		return array(
			'instagram' => 'Instagram',
			'tiktok'    => 'TikTok',
			'facebook'  => 'Facebook',
			'x'         => 'X',
			'youtube'   => 'YouTube',
			'snapchat'  => 'Snapchat',
		);
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
	 * Resolved palette: preset values with any per-colour overrides applied.
	 *
	 * @return array<string, string>
	 */
	public static function palette(): array {
		$presets = self::presets();
		$preset  = $presets[ (string) self::get( 'color_preset' ) ] ?? $presets['electric'];
		$colors  = $preset['colors'];
		foreach ( array_keys( $colors ) as $name ) {
			$override = sanitize_hex_color( (string) self::get( 'color_' . $name ) );
			if ( $override ) {
				$colors[ $name ] = $override;
			}
		}
		return $colors;
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
				// Empty is allowed: it means "use the preset / default".
				return '' === $value ? '' : (string) sanitize_hex_color( $value );
			case 'checkbox':
				return (bool) $value;
			case 'range':
				[ $min, $max ] = $def['range'];
				return max( $min, min( $max, (int) $value ) );
			case 'number':
			case 'image':
			case 'page':
			case 'category':
				return absint( $value );
			case 'url':
				return esc_url_raw( $value );
			case 'email':
				return sanitize_email( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'select':
				return array_key_exists( $value, $def['choices'] ) ? $value : $def['default'];
			case 'datetime':
				return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) $value ) ? (string) $value : '';
			case 'sortable':
				$keys = array_filter( array_map( 'sanitize_key', explode( ',', (string) $value ) ) );
				return implode( ',', array_unique( $keys ) );
			default:
				return sanitize_text_field( $value );
		}
	}
}
