<?php
/**
 * Desktop navigation bar (≥lg): "All categories" mega menu, category links
 * (or the "Desktop category bar" menu) and a highlighted link.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$tree      = t360_has_wc() ? The360Hub\WooCommerce\Catalog::category_tree() : array();
$current   = is_tax( 'product_cat' ) ? get_queried_object_id() : 0;
$highlight = (string) t360_setting( 'nav_highlight_label' );
$hl_url    = (string) t360_setting( 'nav_highlight_url' );
if ( ! $hl_url && t360_has_wc() ) {
	$hl_url = add_query_arg( 'deals', 1, get_permalink( wc_get_page_id( 'shop' ) ) );
}
if ( ! $tree && ! has_nav_menu( 'desktop' ) ) {
	return;
}
?>
<nav class="t360-navbar" aria-label="<?php esc_attr_e( 'Categories', 'the360hub' ); ?>">
	<div class="t360-container t360-navbar__inner">
		<?php if ( $tree && t360_setting( 'mega_menu' ) ) : ?>
			<div class="t360-mega" data-t360-mega>
				<button type="button" class="t360-mega__toggle" aria-expanded="false" aria-controls="t360-mega-panel">
					<?php t360_the_icon( 'menu' ); ?>
					<?php esc_html_e( 'All categories', 'the360hub' ); ?>
					<?php t360_the_icon( 'chevron-down', 't360-mega__chev' ); ?>
				</button>
				<div class="t360-mega__panel" id="t360-mega-panel" hidden>
					<div class="t360-mega__grid">
						<?php foreach ( $tree as $category ) : ?>
							<div class="t360-mega__col">
								<a class="t360-mega__head" href="<?php echo esc_url( $category['url'] ); ?>">
									<span class="t360-mega__thumb">
										<?php if ( $category['image'] ) : ?>
											<?php
											echo wp_get_attachment_image(
												$category['image'],
												't360-tile',
												false,
												array(
													'alt' => '',
													'loading' => 'lazy',
													'sizes' => '48px',
												)
											);
											?>
										<?php endif; ?>
									</span>
									<?php echo esc_html( $category['name'] ); ?>
								</a>
								<?php if ( $category['children'] ) : ?>
									<ul>
										<?php foreach ( array_slice( $category['children'], 0, 8 ) as $child ) : ?>
											<li><a href="<?php echo esc_url( $child['url'] ); ?>"><?php echo esc_html( $child['name'] ); ?></a></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php
		if ( has_nav_menu( 'desktop' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'desktop',
					'container'      => false,
					'menu_class'     => 't360-navbar__list',
					'depth'          => 1,
				)
			);
		} else {
			?>
			<ul class="t360-navbar__list">
				<?php foreach ( array_slice( $tree, 0, 8 ) as $category ) : ?>
					<li><a href="<?php echo esc_url( $category['url'] ); ?>" <?php echo $current === $category['id'] ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $category['name'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php
		}
		?>

		<?php if ( $highlight && $hl_url ) : ?>
			<a class="t360-navbar__highlight" href="<?php echo esc_url( $hl_url ); ?>"><?php t360_the_icon( 'zap' ); ?><?php echo esc_html( $highlight ); ?></a>
		<?php endif; ?>
	</div>
</nav>
