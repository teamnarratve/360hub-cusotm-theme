<?php
/**
 * WooCommerce integration: hook adjustments and styles.
 *
 * The theme removes only WooCommerce's *default* callbacks that its own
 * templates replace. The actions themselves are still fired from the theme's
 * templates, so plugins that hook into them keep working.
 *
 * @package The360Hub
 */

namespace The360Hub\WooCommerce;

defined( 'ABSPATH' ) || exit;

final class Setup {

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'replace_default_hooks' ) );

		add_filter( 'woocommerce_enqueue_styles', array( __CLASS__, 'styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_scripts' ), 100 );
		add_action( 'wp_print_styles', array( __CLASS__, 'dequeue_late_styles' ), 1 );
		add_action( 'wp_footer', array( __CLASS__, 'dequeue_late_styles' ), 1 );

		add_filter( 'woocommerce_show_page_title', '__return_false' );
		add_filter( 'loop_shop_per_page', array( __CLASS__, 'per_page' ), 20 );
		add_filter( 'woocommerce_breadcrumb_defaults', array( __CLASS__, 'breadcrumb_args' ) );
		add_filter( 'woocommerce_currency_symbol', array( __CLASS__, 'currency_symbol' ), 10, 2 );
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'cart_count_fragment' ) );
		add_filter( 'woocommerce_pagination_args', array( __CLASS__, 'pagination_args' ) );

		add_action( 'pre_get_posts', array( __CLASS__, 'merchandising_query' ) );

		// Interim wrapper for WooCommerce templates the theme has not replaced yet
		// (single product until phase 3). Archives render their own container.
		add_action( 'woocommerce_before_main_content', array( __CLASS__, 'wrapper_open' ), 10 );
		add_action( 'woocommerce_after_main_content', array( __CLASS__, 'wrapper_close' ), 10 );
	}

	public static function wrapper_open(): void {
		if ( ! is_product_taxonomy() && ! is_shop() && ! is_search() ) {
			echo '<div class="t360-container t360-page t360-page--wc"><div class="t360-wc-content">';
			woocommerce_breadcrumb();
		}
	}

	public static function wrapper_close(): void {
		if ( ! is_product_taxonomy() && ! is_shop() && ! is_search() ) {
			echo '</div></div>';
		}
	}

	/**
	 * Remove default loop/archive callbacks that the theme renders itself.
	 */
	public static function replace_default_hooks(): void {
		// Wrappers, breadcrumb and sidebar are part of the theme templates.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		// Archive header: title/count/description are rendered by archive-product.php.
		remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10 );
		remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
		remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

		// Product card internals: template-parts/product/card.php.
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	}

	/**
	 * WooCommerce's general stylesheet is replaced by the theme on catalogue
	 * pages. Product, cart, checkout and account pages keep it until their
	 * theme phases ship.
	 */
	public static function styles( array $styles ): array {
		if ( self::needs_wc_scripts() ) {
			return $styles;
		}
		return array();
	}

	/**
	 * Whether the current page renders WooCommerce forms that need
	 * WooCommerce's own (jQuery-based) scripts: product, cart, checkout, account.
	 */
	private static function needs_wc_scripts(): bool {
		return is_product() || is_cart() || is_checkout() || is_account_page();
	}

	/**
	 * Cart fragments polls on every page load and requires jQuery; the theme's
	 * core.js keeps the badge in sync using the cart-hash cookie instead.
	 *
	 * On home and catalogue pages the theme handles add-to-cart itself, so
	 * WooCommerce's general script (and jQuery, blockUI and js-cookie, which
	 * it pulls in) are not loaded there. Plugins that enqueue jQuery for their
	 * own features still get it.
	 */
	public static function dequeue_scripts(): void {
		wp_dequeue_script( 'wc-cart-fragments' );

		if ( ! self::needs_wc_scripts() ) {
			wp_dequeue_script( 'wc-add-to-cart' );
			wp_dequeue_script( 'woocommerce' );
		}
	}

	/**
	 * Block and Brands stylesheets are enqueued late; drop them at print time
	 * on pages whose markup is entirely theme-rendered.
	 */
	public static function dequeue_late_styles(): void {
		if ( self::needs_wc_scripts() || ( is_singular() && ! is_front_page() ) ) {
			return;
		}
		wp_dequeue_style( 'wc-blocks-style' );
		wp_dequeue_style( 'brands-styles' );
	}

	public static function per_page(): int {
		return (int) t360_setting( 'products_per_page' );
	}

	public static function breadcrumb_args( array $args ): array {
		return array_merge(
			$args,
			array(
				'delimiter'   => '<span class="t360-crumbs__sep" aria-hidden="true">/</span>',
				'wrap_before' => '<nav class="t360-crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'the360hub' ) . '"><p>',
				'wrap_after'  => '</p></nav>',
			)
		);
	}

	/**
	 * Show "AED" instead of the Arabic dirham sign, which many Latin fonts
	 * lack and which reads poorly at small sizes.
	 */
	public static function currency_symbol( string $symbol, string $currency ): string {
		$label = (string) t360_setting( 'currency_label' );
		return ( 'AED' === $currency && '' !== $label ) ? $label : $symbol;
	}

	/**
	 * Keep the header/bottom-nav badge correct after WooCommerce add-to-cart
	 * requests (the theme's and any plugin's).
	 */
	public static function cart_count_fragment( array $fragments ): array {
		$fragments['t360_cart_count'] = t360_cart_count();
		return $fragments;
	}

	public static function pagination_args( array $args ): array {
		$args['prev_text'] = '<span aria-hidden="true">‹</span><span class="screen-reader-text">' . esc_html__( 'Previous page', 'the360hub' ) . '</span>';
		$args['next_text'] = '<span aria-hidden="true">›</span><span class="screen-reader-text">' . esc_html__( 'Next page', 'the360hub' ) . '</span>';
		$args['mid_size']  = 1;
		$args['end_size']  = 1;
		return $args;
	}

	/**
	 * Merchandised catalogue views used by homepage "View all" links:
	 * ?deals=1 (on sale) and ?featured=1. Sorting uses WooCommerce's own
	 * orderby values (date, popularity).
	 */
	public static function merchandising_query( \WP_Query $q ): void {
		if ( is_admin() || ! $q->is_main_query() || ! ( $q->is_post_type_archive( 'product' ) || $q->is_tax( get_object_taxonomies( 'product' ) ) ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only catalogue filters.
		if ( ! empty( $_GET['deals'] ) ) {
			$ids = wc_get_product_ids_on_sale();
			$q->set( 'post__in', $ids ? array_map( 'absint', $ids ) : array( 0 ) );
		}

		if ( ! empty( $_GET['featured'] ) ) {
			$tax_query   = (array) $q->get( 'tax_query' );
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			);
			$q->set( 'tax_query', $tax_query );
		}
		// phpcs:enable
	}
}
