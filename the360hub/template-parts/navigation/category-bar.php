<?php
/**
 * Desktop category bar (≥lg). Uses the "Desktop category bar" menu when set,
 * otherwise the top-level product categories.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

if ( has_nav_menu( 'desktop' ) ) {
	echo '<nav class="t360-catbar" aria-label="' . esc_attr__( 'Categories', 'the360hub' ) . '"><div class="t360-container">';
	wp_nav_menu(
		array(
			'theme_location' => 'desktop',
			'container'      => false,
			'menu_class'     => 't360-catbar__list',
			'depth'          => 1,
		)
	);
	echo '</div></nav>';
	return;
}

$tree = t360_has_wc() ? The360Hub\WooCommerce\Catalog::category_tree() : array();
if ( ! $tree ) {
	return;
}
$current = is_tax( 'product_cat' ) ? get_queried_object_id() : 0;
?>
<nav class="t360-catbar" aria-label="<?php esc_attr_e( 'Categories', 'the360hub' ); ?>">
	<div class="t360-container">
		<ul class="t360-catbar__list">
			<?php foreach ( array_slice( $tree, 0, 10 ) as $category ) : ?>
				<li><a href="<?php echo esc_url( $category['url'] ); ?>" <?php echo $current === $category['id'] ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $category['name'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
