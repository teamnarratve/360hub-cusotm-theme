<?php
/**
 * Mobile menu drawer: account, category tree, extra menu, support.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$tree     = t360_has_wc() ? The360Hub\WooCommerce\Catalog::category_tree() : array();
$whatsapp = t360_whatsapp_url();
?>
<dialog class="t360-sheet t360-sheet--drawer" id="t360-drawer" aria-labelledby="t360-drawer-title">
	<div class="t360-sheet__inner">
		<div class="t360-sheet__head">
			<h2 class="t360-sheet__title" id="t360-drawer-title"><?php esc_html_e( 'Menu', 'the360hub' ); ?></h2>
			<button type="button" class="t360-iconbtn" data-t360-close>
				<?php t360_the_icon( 'close' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'the360hub' ); ?></span>
			</button>
		</div>

		<div class="t360-sheet__body">
			<?php if ( t360_has_wc() ) : ?>
				<a class="t360-drawer__account" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
					<?php t360_the_icon( 'user' ); ?>
					<span><?php echo is_user_logged_in() ? esc_html__( 'My account', 'the360hub' ) : esc_html__( 'Sign in or register', 'the360hub' ); ?></span>
					<?php t360_the_icon( 'chevron-right', 't360-drawer__chev' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $tree ) : ?>
				<nav aria-labelledby="t360-drawer-cats">
					<h3 class="t360-drawer__heading" id="t360-drawer-cats"><?php esc_html_e( 'Shop by category', 'the360hub' ); ?></h3>
					<ul class="t360-drawer__list">
						<?php foreach ( $tree as $category ) : ?>
							<li>
								<?php if ( $category['children'] ) : ?>
									<details class="t360-drawer__group">
										<summary class="t360-drawer__row">
											<span><?php echo esc_html( $category['name'] ); ?></span>
											<?php t360_the_icon( 'chevron-down', 't360-drawer__chev' ); ?>
										</summary>
										<ul class="t360-drawer__sub">
											<li>
												<a href="<?php echo esc_url( $category['url'] ); ?>">
													<?php
													/* translators: %s: category name */
													echo esc_html( sprintf( __( 'All %s', 'the360hub' ), $category['name'] ) );
													?>
												</a>
											</li>
											<?php foreach ( $category['children'] as $child ) : ?>
												<li><a href="<?php echo esc_url( $child['url'] ); ?>"><?php echo esc_html( $child['name'] ); ?></a></li>
											<?php endforeach; ?>
										</ul>
									</details>
								<?php else : ?>
									<a class="t360-drawer__row" href="<?php echo esc_url( $category['url'] ); ?>">
										<span><?php echo esc_html( $category['name'] ); ?></span>
										<?php t360_the_icon( 'chevron-right', 't360-drawer__chev' ); ?>
									</a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'drawer' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'More', 'the360hub' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'drawer',
							'container'      => false,
							'menu_class'     => 't360-drawer__list t360-drawer__list--menu',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php if ( $whatsapp ) : ?>
				<a class="t360-drawer__support" href="<?php echo esc_url( $whatsapp ); ?>" rel="noopener" target="_blank">
					<?php t360_the_icon( 'chat' ); ?>
					<span>
						<strong><?php esc_html_e( 'Need help choosing?', 'the360hub' ); ?></strong>
						<?php esc_html_e( 'Chat with us on WhatsApp', 'the360hub' ); ?>
					</span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</dialog>
