<?php
/**
 * Category icons: circles or tiles, scrollable on mobile.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$tree = t360_has_wc() ? The360Hub\WooCommerce\Catalog::category_tree() : array();
if ( ! $tree ) {
	return;
}
$heading_id = 't360-sec-' . $args['key'];
$style      = 'tile' === t360_setting( 'categories_style' ) ? 'tile' : 'circle';
?>
<section class="t360-section" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="t360-container">
		<?php
		get_template_part(
			'template-parts/components/section-header',
			null,
			array(
				'title' => (string) t360_setting( 'categories_title' ),
				'id'    => $heading_id,
				'url'   => get_permalink( wc_get_page_id( 'shop' ) ),
			)
		);
		?>
		<ul class="t360-cats t360-cats--<?php echo esc_attr( $style ); ?>">
			<?php foreach ( $tree as $category ) : ?>
				<li>
					<a class="t360-cats__item" href="<?php echo esc_url( $category['url'] ); ?>">
						<span class="t360-cats__media">
							<?php if ( $category['image'] ) : ?>
								<?php
								echo wp_get_attachment_image(
									$category['image'],
									't360-tile',
									false,
									array(
										'alt'     => '',
										'sizes'   => '(min-width: 1024px) 96px, 72px',
										'loading' => 'lazy',
									)
								);
								?>
							<?php else : ?>
								<span class="t360-cats__initial" aria-hidden="true"><?php echo esc_html( mb_substr( $category['name'], 0, 1 ) ); ?></span>
							<?php endif; ?>
						</span>
						<span class="t360-cats__label"><?php echo esc_html( $category['name'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
