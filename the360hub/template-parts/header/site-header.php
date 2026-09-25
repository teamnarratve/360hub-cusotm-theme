<?php
/**
 * Site header.
 *
 * Mobile: [menu] [logo] ........ [wishlist] [cart]
 *         [ search field (opens the search overlay) ]
 * ≥lg:    [logo] [ search field ] [account] [wishlist] [cart]
 *         [ category bar ]
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$notice       = (string) t360_setting( 'header_notice' );
$notice_url   = (string) t360_setting( 'header_notice_url' );
$wishlist_url = t360_wishlist_url();
$has_wc       = t360_has_wc();
$cart_count   = t360_cart_count();
$placeholder  = (string) t360_setting( 'header_search_ph' );
?>
<?php if ( $notice ) : ?>
	<div class="t360-notice">
		<?php if ( $notice_url ) : ?>
			<a href="<?php echo esc_url( $notice_url ); ?>"><?php echo esc_html( $notice ); ?></a>
		<?php else : ?>
			<p><?php echo esc_html( $notice ); ?></p>
		<?php endif; ?>
	</div>
<?php endif; ?>

<header class="t360-header" data-t360-header <?php echo t360_setting( 'header_hide_scroll' ) ? 'data-t360-condense' : ''; ?>>
	<div class="t360-header__bar t360-container">
		<a class="t360-iconbtn lg:hidden" href="<?php echo esc_url( $has_wc ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' ) ); ?>" data-t360-open="t360-drawer" aria-haspopup="dialog" aria-controls="t360-drawer">
			<?php t360_the_icon( 'menu' ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'the360hub' ); ?></span>
		</a>

		<?php if ( is_front_page() ) : ?>
			<h1 class="screen-reader-text"><?php echo esc_html( (string) t360_setting( 'home_h1' ) ); ?></h1>
		<?php endif; ?>
		<?php t360_the_logo( 't360-logo' ); ?>

		<div class="t360-header__search-lg">
			<?php
			get_template_part(
				'template-parts/search/form',
				null,
				array(
					'id'          => 'lg',
					'placeholder' => $placeholder,
				)
			);
			?>
		</div>

		<nav class="t360-header__actions" aria-label="<?php esc_attr_e( 'Shortcuts', 'the360hub' ); ?>">
			<?php if ( $has_wc ) : ?>
				<a class="t360-iconbtn max-lg:hidden" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
					<?php t360_the_icon( 'user' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Account', 'the360hub' ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( $wishlist_url ) : ?>
				<a class="t360-iconbtn" href="<?php echo esc_url( $wishlist_url ); ?>">
					<?php t360_the_icon( 'heart' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Wishlist', 'the360hub' ); ?></span>
					<span class="t360-badge" data-t360-wish-count hidden></span>
				</a>
			<?php endif; ?>
			<?php if ( $has_wc ) : ?>
				<a class="t360-iconbtn" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
					<?php t360_the_icon( 'bag' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Cart', 'the360hub' ); ?></span>
					<span class="t360-badge" data-t360-cart-count <?php echo $cart_count ? '' : 'hidden'; ?>><?php echo esc_html( (string) $cart_count ); ?><span class="screen-reader-text"> <?php esc_html_e( 'items', 'the360hub' ); ?></span></span>
				</a>
			<?php endif; ?>
		</nav>
	</div>

	<div class="t360-header__search t360-container lg:hidden">
		<?php
		get_template_part(
			'template-parts/search/form',
			null,
			array(
				'id'          => 'sm',
				'placeholder' => $placeholder,
			)
		);
		?>
	</div>

	<?php get_template_part( 'template-parts/navigation/category-bar' ); ?>
</header>

<?php
get_template_part( 'template-parts/navigation/drawer' );
get_template_part( 'template-parts/search/overlay', null, array( 'placeholder' => $placeholder ) );
