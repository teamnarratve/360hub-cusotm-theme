<?php
/**
 * Read-only AJAX endpoints (WooCommerce's ?wc-ajax= router).
 *
 * All three endpoints only return public catalogue data or the visitor's own
 * cart count. They take no nonce on purpose so they keep working behind
 * full-page caches; nothing here changes state. Input is length- and
 * count-limited, and search/cards responses are marked cacheable so a CDN
 * can absorb repeated requests.
 *
 * @package The360Hub
 */

namespace The360Hub\WooCommerce;

use WP_Query;

defined( 'ABSPATH' ) || exit;

final class Ajax {

	const SEARCH_MIN   = 2;
	const SEARCH_MAX   = 64;
	const MAX_CARD_IDS = 24;

	public static function init(): void {
		add_action( 'wc_ajax_t360_search', array( __CLASS__, 'search' ) );
		add_action( 'wc_ajax_t360_cards', array( __CLASS__, 'cards' ) );
		add_action( 'wc_ajax_t360_cart_count', array( __CLASS__, 'cart_count' ) );
	}

	/**
	 * Send JSON with explicit cache headers.
	 */
	private static function respond( array $data, int $max_age = 0 ): void {
		nocache_headers();
		if ( $max_age > 0 ) {
			header_remove( 'Expires' );
			header_remove( 'Pragma' );
			header( 'Cache-Control: public, max-age=' . $max_age );
		}
		wp_send_json_success( $data );
	}

	/**
	 * Instant search: products, categories, brands.
	 *
	 * GET q (2–64 chars).
	 */
	public static function search(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public read-only endpoint.
		$q   = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$q   = trim( $q );
		$len = mb_strlen( $q );

		if ( $len < self::SEARCH_MIN || $len > self::SEARCH_MAX ) {
			self::respond(
				array(
					'products'   => array(),
					'categories' => array(),
					'brands'     => array(),
					'total'      => 0,
				)
			);
		}

		$key  = 'search_' . md5( mb_strtolower( $q ) );
		$data = wp_cache_get( $key, 't360' );

		if ( false === $data ) {
			$data             = array(
				'products'   => self::search_products( $q ),
				'categories' => self::search_terms( 'product_cat', $q ),
				'brands'     => Catalog::brand_taxonomy() ? self::search_terms( Catalog::brand_taxonomy(), $q ) : array(),
			);
			$data['total']    = $data['products']['total'];
			$data['products'] = $data['products']['items'];
			$data['url']      = add_query_arg(
				array(
					's'         => $q,
					'post_type' => 'product',
				),
				home_url( '/' )
			);
			wp_cache_set( $key, $data, 't360', 10 * MINUTE_IN_SECONDS );
		}

		self::respond( $data, 300 );
	}

	/**
	 * Product matches via WP_Query, so search plugins that filter the query
	 * (Relevanssi, SearchWP, etc.) apply here as well.
	 */
	private static function search_products( string $q ): array {
		$tax_query = array(
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => array( 'exclude-from-search' ),
				'operator' => 'NOT IN',
			),
		);
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$tax_query[0]['terms'][] = 'outofstock';
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				's'              => $q,
				'posts_per_page' => 6,
				'fields'         => 'ids',
				'tax_query'      => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			)
		);

		$items = array();
		foreach ( $query->posts as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}
			$pricing = Product_Card::pricing( $product );
			$image   = wp_get_attachment_image_src( (int) $product->get_image_id(), 'woocommerce_gallery_thumbnail' );

			$items[] = array(
				'id'    => $product->get_id(),
				'name'  => wp_strip_all_tags( $product->get_name() ),
				'url'   => $product->get_permalink(),
				'image' => $image ? $image[0] : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ),
				'price' => self::plain_price( $pricing['now'], $pricing['from'] ),
				'was'   => $pricing['was'] ? self::plain_price( $pricing['was'] ) : '',
			);
		}

		return array(
			'items' => $items,
			'total' => (int) $query->found_posts,
		);
	}

	/**
	 * Price as plain text (the client renders with textContent, never HTML).
	 */
	private static function plain_price( float $amount, bool $from = false ): string {
		if ( $amount <= 0 ) {
			return '';
		}
		$text = html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ), ENT_QUOTES, 'UTF-8' );
		/* translators: %s: price */
		return $from ? sprintf( __( 'From %s', 'the360hub' ), $text ) : $text;
	}

	private static function search_terms( string $taxonomy, string $q ): array {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'name__like' => $q,
				'hide_empty' => true,
				'number'     => 4,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);
		if ( is_wp_error( $terms ) ) {
			return array();
		}
		return array_map(
			static function ( $term ) {
				return array(
					'name' => $term->name,
					'url'  => get_term_link( $term ),
				);
			},
			$terms
		);
	}

	/**
	 * Rendered product cards for a list of IDs (wishlist, recently viewed).
	 *
	 * GET ids (comma-separated, max 24). Only published, visible products.
	 */
	public static function cards(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public read-only endpoint.
		$raw = isset( $_GET['ids'] ) ? sanitize_text_field( wp_unslash( $_GET['ids'] ) ) : '';
		$ids = array_slice( array_values( array_unique( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) ) ), 0, self::MAX_CARD_IDS );

		// Keep only published products.
		if ( $ids ) {
			_prime_post_caches( $ids, false, false );
			$ids = array_values(
				array_filter(
					$ids,
					static function ( $id ) {
						$post = get_post( $id );
						return $post && 'product' === $post->post_type && 'publish' === $post->post_status && ! post_password_required( $post );
					}
				)
			);
		}

		ob_start();
		Product_Card::render_many( $ids, array( 'heading_tag' => 'h3' ) );
		$html = (string) ob_get_clean();

		self::respond(
			array(
				'html' => $html,
				'ids'  => $ids,
			),
			300
		);
	}

	/**
	 * Cart item count for the badge. Per-visitor, so never cached.
	 */
	public static function cart_count(): void {
		$hash = WC()->cart ? WC()->cart->get_cart_hash() : '';
		self::respond(
			array(
				'count' => t360_cart_count(),
				'hash'  => $hash,
			)
		);
	}
}
