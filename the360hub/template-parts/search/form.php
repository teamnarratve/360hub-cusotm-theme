<?php
/**
 * Header search field.
 *
 * A normal GET form (works without JS). With JS, focusing it opens the
 * full-screen search overlay on mobile / the instant panel on desktop.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$field_id    = 't360-q-' . sanitize_key( $args['id'] ?? 'x' );
$placeholder = (string) ( $args['placeholder'] ?? '' );
?>
<form class="t360-searchfield" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-t360-search-trigger>
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
	<button class="t360-searchfield__submit" type="submit"><?php esc_html_e( 'Search', 'the360hub' ); ?></button>
</form>
