<?php
/**
 * Fixed mobile bottom navigation (hidden ≥lg).
 *
 * Home · Categories · Wishlist · Account · Cart
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

if ( ! t360_setting( 'bottom_nav' ) ) {
	return;
}

$has_wc   = t360_has_wc();
$cats_pid = (int) t360_setting( 'categories_page' );
$wish_url = t360_wishlist_url();

$items = array(
	'home'       => array(
		'label'   => __( 'Home', 'the360hub' ),
		'icon'    => 'home',
		'url'     => home_url( '/' ),
		'current' => is_front_page(),
	),
	'categories' => array(
		'label'   => __( 'Categories', 'the360hub' ),
		'icon'    => 'grid',
		// Without a Categories page, the link goes to the shop and JS opens the drawer instead.
		'url'     => $cats_pid ? get_permalink( $cats_pid ) : ( $has_wc ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' ) ),
		'current' => ( $cats_pid && is_page( $cats_pid ) ) || ( $has_wc && ( is_product_category() || is_shop() ) ),
		'drawer'  => ! $cats_pid,
	),
	'wishlist'   => array(
		'label'   => __( 'Wishlist', 'the360hub' ),
		'icon'    => 'heart',
		'url'     => $wish_url,
		'current' => is_page_template( 'page-templates/wishlist.php' ),
		'badge'   => 'wish',
	),
	'account'    => array(
		'label'   => __( 'Account', 'the360hub' ),
		'icon'    => 'user',
		'url'     => $has_wc ? wc_get_page_permalink( 'myaccount' ) : wp_login_url(),
		'current' => $has_wc && is_account_page(),
	),
	'cart'       => array(
		'label'   => __( 'Cart', 'the360hub' ),
		'icon'    => 'bag',
		'url'     => $has_wc ? wc_get_cart_url() : '',
		'current' => $has_wc && is_cart(),
		'badge'   => 'cart',
	),
);

/**
 * Filters bottom navigation items.
 *
 * @param array $items Items keyed by id.
 */
$items      = apply_filters( 'the360hub_bottom_nav_items', $items );
$cart_count = t360_cart_count();
?>
<nav class="t360-bottomnav" aria-label="<?php esc_attr_e( 'Main', 'the360hub' ); ?>">
	<ul class="t360-bottomnav__list">
		<?php foreach ( $items as $nav_item ) : ?>
			<?php
			if ( empty( $nav_item['url'] ) ) {
				continue;
			}
			?>
			<li>
				<a
					class="t360-bottomnav__item"
					href="<?php echo esc_url( $nav_item['url'] ); ?>"
					<?php echo ! empty( $nav_item['current'] ) ? 'aria-current="page"' : ''; ?>
					<?php echo ! empty( $nav_item['drawer'] ) ? 'data-t360-open="t360-drawer" aria-haspopup="dialog"' : ''; ?>
				>
					<span class="t360-bottomnav__icon">
						<?php t360_the_icon( $nav_item['icon'] ); ?>
						<?php if ( 'cart' === ( $nav_item['badge'] ?? '' ) ) : ?>
							<span class="t360-badge" data-t360-cart-count <?php echo $cart_count ? '' : 'hidden'; ?>><?php echo esc_html( (string) $cart_count ); ?><span class="screen-reader-text"> <?php esc_html_e( 'items', 'the360hub' ); ?></span></span>
						<?php elseif ( 'wish' === ( $nav_item['badge'] ?? '' ) ) : ?>
							<span class="t360-badge" data-t360-wish-count hidden></span>
						<?php endif; ?>
					</span>
					<span class="t360-bottomnav__label"><?php echo esc_html( $nav_item['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
