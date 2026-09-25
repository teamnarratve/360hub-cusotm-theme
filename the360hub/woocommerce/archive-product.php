<?php
/**
 * Product archive: shop, categories, tags, brands, product search.
 *
 * Overrides woocommerce/templates/archive-product.php. All of WooCommerce's
 * archive actions are still fired; the theme removed only the default
 * callbacks it renders itself (see WooCommerce\Setup::replace_default_hooks).
 *
 * @package The360Hub
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$current_term = is_product_taxonomy() ? get_queried_object() : null;
$total        = (int) wc_get_loop_prop( 'total' );
$children     = array();
if ( $current_term instanceof WP_Term && is_taxonomy_hierarchical( $current_term->taxonomy ) ) {
	$children = get_terms(
		array(
			'taxonomy'   => $current_term->taxonomy,
			'parent'     => $current_term->term_id,
			'hide_empty' => true,
			'menu_order' => 'ASC',
		)
	);
	$children = is_wp_error( $children ) ? array() : $children;
}
?>
<div class="t360-container t360-archive">
	<?php
	/** This action is documented in woocommerce/templates/archive-product.php */
	do_action( 'woocommerce_before_main_content' );

	woocommerce_breadcrumb();
	?>

	<header class="t360-archive__head">
		<h1 class="t360-archive__title"><?php woocommerce_page_title(); ?></h1>
	</header>

	<?php if ( $children ) : ?>
		<nav class="t360-subcats" aria-label="<?php esc_attr_e( 'Subcategories', 'the360hub' ); ?>">
			<ul class="t360-chips t360-chips--scroll">
				<?php foreach ( $children as $child ) : ?>
					<li><a class="t360-chip" href="<?php echo esc_url( get_term_link( $child ) ); ?>"><?php echo esc_html( $child->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<?php
	/** This action is documented in woocommerce/templates/archive-product.php */
	do_action( 'woocommerce_shop_loop_header' );

	if ( woocommerce_product_loop() ) :
		?>
		<div class="t360-toolbar">
			<p class="t360-archive__count">
				<?php
				/* translators: %s: number of products */
				echo esc_html( sprintf( _n( '%s product', '%s products', $total, 'the360hub' ), number_format_i18n( $total ) ) );
				?>
			</p>
			<?php woocommerce_catalog_ordering(); ?>
		</div>
		<?php
		/** This action is documented in woocommerce/templates/archive-product.php */
		do_action( 'woocommerce_before_shop_loop' );

		woocommerce_product_loop_start();

		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();

				/** This action is documented in woocommerce/templates/archive-product.php */
				do_action( 'woocommerce_shop_loop' );

				wc_get_template_part( 'content', 'product' );
			}
		}

		woocommerce_product_loop_end();

		/** This action is documented in woocommerce/templates/archive-product.php */
		do_action( 'woocommerce_after_shop_loop' );
	else :
		/** This action is documented in woocommerce/templates/archive-product.php */
		do_action( 'woocommerce_no_products_found' );
	endif;
	?>

	<?php
	// Category / shop description below the grid: shoppers see products first,
	// search engines still get the copy.
	ob_start();
	woocommerce_taxonomy_archive_description();
	woocommerce_product_archive_description();
	$description = trim( (string) ob_get_clean() );
	if ( $description ) :
		?>
		<section class="t360-archive__desc t360-prose">
			<?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce-escaped term/page description. ?>
		</section>
	<?php endif; ?>

	<?php
	/** This action is documented in woocommerce/templates/archive-product.php */
	do_action( 'woocommerce_archive_description' );

	/** This action is documented in woocommerce/templates/archive-product.php */
	do_action( 'woocommerce_after_main_content' );
	?>
</div>
<?php
get_footer( 'shop' );
