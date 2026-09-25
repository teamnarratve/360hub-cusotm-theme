<?php
/**
 * Theme setup: supports, menus, image sizes, activation tasks.
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

defined( 'ABSPATH' ) || exit;

final class Theme {

	public static function init(): void {
		add_action( 'after_setup_theme', array( __CLASS__, 'setup' ) );
		add_action( 'after_switch_theme', array( __CLASS__, 'on_activation' ) );
		add_action( 'wp_head', array( __CLASS__, 'js_class' ), 0 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'theme_page_templates', array( __CLASS__, 'page_templates' ) );
	}

	public static function setup(): void {
		load_theme_textdomain( 'the360hub', THE360HUB_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 80,
				'width'       => 320,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		// WooCommerce: 1:1 catalogue images sized for 2-up mobile grids at 3x DPR.
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 400,
				'single_image_width'    => 900,
				'product_grid'          => array(
					'default_columns' => 4,
					'min_columns'     => 2,
					'max_columns'     => 6,
				),
			)
		);

		// Hero banners.
		add_image_size( 't360-hero', 1920, 640, true );
		add_image_size( 't360-hero-mobile', 1080, 1080, true );
		add_image_size( 't360-tile', 240, 240, false );

		register_nav_menus(
			array(
				'drawer'   => __( 'Mobile menu (below categories)', 'the360hub' ),
				'desktop'  => __( 'Desktop category bar', 'the360hub' ),
				'footer_1' => __( 'Footer column 1', 'the360hub' ),
				'footer_2' => __( 'Footer column 2', 'the360hub' ),
			)
		);
	}

	/**
	 * Swap the no-js class before first paint so JS-only controls never flash.
	 */
	public static function js_class(): void {
		echo "<script>document.documentElement.className=document.documentElement.className.replace('no-js','js');</script>\n";
	}

	public static function body_class( array $classes ): array {
		if ( t360_setting( 'bottom_nav' ) ) {
			$classes[] = 'has-bottom-nav';
		}
		if ( ! t360_setting( 'style_animations' ) ) {
			$classes[] = 'no-anim';
		}
		return $classes;
	}

	/**
	 * Register page templates that live in page-templates/.
	 */
	public static function page_templates( array $templates ): array {
		$templates['page-templates/wishlist.php'] = __( 'Wishlist', 'the360hub' );
		return $templates;
	}

	/**
	 * Create the Wishlist page on first activation so the bottom nav works out
	 * of the box. Never overwrites an existing choice.
	 */
	public static function on_activation(): void {
		$current = (int) get_theme_mod( Settings::PREFIX . 'wishlist_page' );
		if ( $current && get_post( $current ) ) {
			return;
		}

		$existing = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off on activation.
				'meta_value'     => 'page-templates/wishlist.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		$page_id = $existing ? (int) $existing[0] : wp_insert_post(
			array(
				'post_type'     => 'page',
				'post_status'   => 'publish',
				'post_title'    => __( 'Wishlist', 'the360hub' ),
				'post_name'     => 'wishlist',
				'page_template' => 'page-templates/wishlist.php',
			)
		);

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			set_theme_mod( Settings::PREFIX . 'wishlist_page', (int) $page_id );
		}
	}
}
