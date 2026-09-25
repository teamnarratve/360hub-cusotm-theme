<?php
/**
 * Header search field.
 *
 * A normal GET form (works without JS). With JS, tapping it opens the
 * search overlay with instant results. On desktop it can include a category
 * picker that narrows the search (WooCommerce's product_cat query var).
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$field_id    = 't360-q-' . sanitize_key( $args['id'] ?? 'x' );
$placeholder = (string) ( $args['placeholder'] ?? '' );
$tree        = ( ! empty( $args['categories'] ) && t360_has_wc() ) ? The360Hub\WooCommerce\Catalog::category_tree() : array();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
$current_cat = isset( $_GET['product_cat'] ) ? sanitize_title( wp_unslash( $_GET['product_cat'] ) ) : '';
?>
<form class="t360-searchfield<?php echo $tree ? ' has-cats' : ''; ?>" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-t360-search-trigger>
	<?php if ( $tree ) : ?>
		<label class="screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>-cat"><?php esc_html_e( 'Search in', 'the360hub' ); ?></label>
		<select class="t360-searchfield__cat" id="<?php echo esc_attr( $field_id ); ?>-cat" name="product_cat">
			<option value=""><?php esc_html_e( 'All', 'the360hub' ); ?></option>
			<?php foreach ( $tree as $category ) : ?>
				<option value="<?php echo esc_attr( $category['slug'] ); ?>" <?php selected( $current_cat, $category['slug'] ); ?>><?php echo esc_html( $category['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
	<?php endif; ?>
	<label class="screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>"><?php esc_html_e( 'Search products', 'the360hub' ); ?></label>
	<?php t360_the_icon( 'search', 't360-searchfield__icon' ); ?>
	<input
		class="t360-searchfield__input"
		type="search"
		id="<?php echo esc_attr( $field_id ); ?>"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php echo esc_attr( $placeholder ); ?>"
		autocomplete="off"
		enterkeyhint="search"
	>
	<?php if ( t360_has_wc() ) : ?>
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<button class="t360-searchfield__submit" type="submit">
		<?php t360_the_icon( 'search' ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'the360hub' ); ?></span>
	</button>
</form>
