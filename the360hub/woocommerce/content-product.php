<?php
/**
 * Product in a loop — delegates to the theme's product card.
 *
 * Overrides woocommerce/templates/content-product.php.
 *
 * @package The360Hub
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

// The first row of a 2-column mobile grid is above the fold: load it eagerly.
static $t360_loop_index = 0;
++$t360_loop_index;
?>
<li <?php wc_product_class( 't360-grid__item', $product ); ?>>
	<?php
	The360Hub\WooCommerce\Product_Card::render(
		$product,
		array(
			'heading_tag' => 'h2',
			'eager'       => $t360_loop_index <= 2,
		)
	);
	?>
</li>
