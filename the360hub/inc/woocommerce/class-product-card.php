<?php
/**
 * Product card data and rendering.
 *
 * Prepares everything template-parts/product/card.php displays (prices,
 * savings, rating, stock) from WooCommerce's own product data, so the
 * template contains no business logic.
 *
 * @package The360Hub
 */

namespace The360Hub\WooCommerce;

use WC_Product;

defined( 'ABSPATH' ) || exit;

final class Product_Card {

	/**
	 * Card data for a product.
	 *
	 * @param WC_Product $product Product.
	 * @return array
	 */
	public static function data( WC_Product $product ): array {
		$pricing = self::pricing( $product );

		$gallery  = $product->get_gallery_image_ids();
		$new_days = (int) t360_setting( 'card_new_days' );
		$created  = $product->get_date_created();
		$free_min = (int) t360_setting( 'card_free_delivery' );

		return array(
			'id'           => $product->get_id(),
			'name'         => $product->get_name(),
			'url'          => $product->get_permalink(),
			'image_id'     => (int) $product->get_image_id(),
			'alt_image_id' => ( t360_setting( 'card_hover_image' ) && $gallery ) ? (int) $gallery[0] : 0,
			'brand'        => t360_setting( 'card_brand' ) ? Catalog::product_brand( $product->get_id() ) : '',
			'rating'       => t360_setting( 'card_rating' ) ? (float) $product->get_average_rating() : 0.0,
			'reviews'      => t360_setting( 'card_rating' ) ? (int) $product->get_review_count() : 0,
			'is_new'       => $new_days > 0 && $created && $created->getTimestamp() > time() - $new_days * DAY_IN_SECONDS,
			'is_featured'  => $product->is_featured(),
			'free_ship'    => $free_min > 0 && $pricing['now'] >= $free_min,
			'installment'  => self::installment( $pricing['now'] ),
			'pricing'      => $pricing,
			'stock'        => self::stock( $product ),
			'add_to_cart'  => self::add_to_cart( $product ),
		);
	}

	/**
	 * Instalment line ("or 4 payments of AED 250"), when enabled.
	 *
	 * @param float $price Current display price.
	 */
	public static function installment( float $price ): string {
		$count = (int) t360_setting( 'card_installments_n' );
		if ( ! t360_setting( 'card_installments' ) || $price <= 0 || $count < 2 ) {
			return '';
		}
		$amount = wp_strip_all_tags( wc_price( $price / $count ) );
		// str_replace, not sprintf: the template is admin-edited text and a stray
		// "%" must not break the page.
		return str_replace( '%s', html_entity_decode( $amount, ENT_QUOTES, 'UTF-8' ), (string) t360_setting( 'card_installments_text' ) );
	}

	/**
	 * Current / original price, savings and discount badge.
	 *
	 * Prices are "display" prices (respecting the store's tax display
	 * setting). For variable products the cheapest variation drives the price
	 * and the badge shows the largest discount across variations.
	 */
	public static function pricing( WC_Product $product ): array {
		$out = array(
			'from'    => false,
			'now'     => 0.0,
			'was'     => 0.0,
			'save'    => 0.0,
			'percent' => 0,
			'up_to'   => false,
			'html'    => '',
		);

		if ( $product->is_type( 'variable' ) ) {
			$prices = $product->get_variation_prices( true );
			if ( empty( $prices['price'] ) ) {
				return $out;
			}

			$min_id      = array_search( min( $prices['price'] ), $prices['price'], true ); // Cheapest variation.
			$out['now']  = (float) $prices['price'][ $min_id ];
			$out['was']  = (float) $prices['regular_price'][ $min_id ];
			$out['from'] = min( $prices['price'] ) !== max( $prices['price'] );

			$percents = array();
			foreach ( $prices['price'] as $id => $price ) {
				$regular = (float) $prices['regular_price'][ $id ];
				if ( $regular > 0 && (float) $price < $regular ) {
					$percents[] = (int) round( ( $regular - (float) $price ) / $regular * 100 );
				}
			}
			if ( $percents ) {
				$out['percent'] = max( $percents );
				$out['up_to']   = count( array_unique( $percents ) ) > 1;
			}
		} elseif ( $product->is_type( 'grouped' ) ) {
			// Grouped products show a range of child prices; let WooCommerce format it.
			$out['html'] = $product->get_price_html();
			return $out;
		} else {
			if ( '' === $product->get_price() ) {
				return $out;
			}
			$out['now'] = (float) wc_get_price_to_display( $product );
			$out['was'] = $product->is_on_sale()
				? (float) wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) )
				: $out['now'];
			if ( $out['was'] > $out['now'] && $out['was'] > 0 ) {
				$out['percent'] = (int) round( ( $out['was'] - $out['now'] ) / $out['was'] * 100 );
			}
		}

		if ( $out['was'] > $out['now'] ) {
			$out['save'] = $out['was'] - $out['now'];
		} else {
			$out['was'] = 0.0;
		}

		return $out;
	}

	/**
	 * Stock message for the card, or null when nothing needs saying.
	 *
	 * @return array{type:string, text:string}|null
	 */
	public static function stock( WC_Product $product ): ?array {
		if ( ! $product->is_in_stock() ) {
			return array(
				'type' => 'out',
				'text' => __( 'Out of stock', 'the360hub' ),
			);
		}
		if ( $product->is_on_backorder() ) {
			return array(
				'type' => 'warn',
				'text' => __( 'Available on backorder', 'the360hub' ),
			);
		}
		if ( $product->managing_stock() ) {
			$qty = (int) $product->get_stock_quantity();
			$low = (int) wc_get_low_stock_amount( $product );
			if ( $qty > 0 && $qty <= max( 1, $low ) ) {
				/* translators: %d: units left */
				return array(
					'type' => 'warn',
					/* translators: %d: units left in stock */
					'text' => sprintf( _n( 'Only %d left', 'Only %d left', $qty, 'the360hub' ), $qty ),
				);
			}
		}
		if ( t360_setting( 'card_in_stock' ) ) {
			return array(
				'type' => 'ok',
				'text' => __( 'In stock', 'the360hub' ),
			);
		}
		return null;
	}

	/**
	 * Card button: AJAX add-to-cart for simple products, otherwise a link to
	 * the product (variations must be chosen there).
	 *
	 * @return array{ajax:bool, url:string, label:string}|null
	 */
	public static function add_to_cart( WC_Product $product ): ?array {
		if ( 'none' === t360_setting( 'card_add_to_cart' ) || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			return null;
		}
		$ajax = $product->is_type( 'simple' ) && $product->supports( 'ajax_add_to_cart' );

		return array(
			'ajax'  => $ajax,
			'url'   => $ajax ? $product->add_to_cart_url() : $product->get_permalink(),
			'label' => $ajax ? __( 'Add to cart', 'the360hub' ) : __( 'See options', 'the360hub' ),
		);
	}

	/**
	 * Render one card.
	 *
	 * Sets up the global product/post so plugins hooked into WooCommerce's
	 * loop actions see the product they expect.
	 *
	 * @param WC_Product $product Product.
	 * @param array      $args    heading_tag (h2|h3), eager (bool).
	 */
	public static function render( WC_Product $product, array $args = array() ): void {
		$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- WooCommerce loop contract.

		get_template_part(
			'template-parts/product/card',
			null,
			array(
				'card'        => self::data( $product ),
				'product'     => $product,
				'heading_tag' => $args['heading_tag'] ?? 'h3',
				'eager'       => ! empty( $args['eager'] ),
			)
		);
	}

	/**
	 * Render cards for a list of product IDs (home rails, AJAX lists).
	 *
	 * @param int[] $ids  Product IDs, in display order.
	 * @param array $args Passed to render(); 'eager_count' makes the first N images eager.
	 */
	public static function render_many( array $ids, array $args = array() ): void {
		if ( ! $ids ) {
			return;
		}
		_prime_post_caches( $ids, true, true );

		$eager_count = (int) ( $args['eager_count'] ?? 0 );
		$i           = 0;
		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}
			$GLOBALS['post'] = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $GLOBALS['post'] );

			echo '<li class="t360-grid__item">';
			self::render( $product, $args + array( 'eager' => $i < $eager_count ) );
			echo '</li>';
			++$i;
		}
		wp_reset_postdata();
	}

	/**
	 * Compact product data for hero slides and tiles.
	 *
	 * @param int $product_id Product ID.
	 * @return array|null
	 */
	public static function teaser( int $product_id ): ?array {
		$product = $product_id ? wc_get_product( $product_id ) : null;
		if ( ! $product || ! $product->is_visible() ) {
			return null;
		}
		$pricing = self::pricing( $product );
		$price   = $pricing['now'] > 0 ? html_entity_decode( wp_strip_all_tags( wc_price( $pricing['now'] ) ), ENT_QUOTES, 'UTF-8' ) : '';
		$was     = $pricing['was'] > 0 ? html_entity_decode( wp_strip_all_tags( wc_price( $pricing['was'] ) ), ENT_QUOTES, 'UTF-8' ) : '';

		return array(
			'id'       => $product->get_id(),
			'name'     => $product->get_name(),
			'brand'    => Catalog::product_brand( $product->get_id() ),
			'url'      => $product->get_permalink(),
			'image_id' => (int) $product->get_image_id(),
			/* translators: %s: price */
			'price'    => $pricing['from'] && $price ? sprintf( __( 'From %s', 'the360hub' ), $price ) : $price,
			'was'      => $was,
			'percent'  => (int) $pricing['percent'],
		);
	}
}
