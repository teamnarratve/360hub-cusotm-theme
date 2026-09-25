<?php
/**
 * Shop by category: image tiles for top-level departments.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$tree = t360_has_wc() ? The360Hub\WooCommerce\Catalog::category_tree() : array();
if ( ! $tree ) {
	return;
}
$heading_id = 't360-sec-' . $args['key'];
?>
<section class="t360-section" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="t360-container">
		<?php
		get_template_part(
			'template-parts/components/section-header',
			null,
			array(
				'title' => (string) t360_setting( 'category_grid_title' ),
				'id'    => $heading_id,
			)
		);
		?>
		<ul class="t360-catgrid">
			<?php foreach ( array_slice( $tree, 0, 12 ) as $category ) : ?>
				<li>
					<a class="t360-catgrid__item" href="<?php echo esc_url( $category['url'] ); ?>">
						<span class="t360-catgrid__media">
							<?php if ( $category['image'] ) : ?>
								<?php
								echo wp_get_attachment_image(
									$category['image'],
									'woocommerce_thumbnail',
									false,
									array(
										'alt'     => '',
										'sizes'   => '(min-width: 1024px) 200px, 30vw',
										'loading' => 'lazy',
									)
								);
								?>
							<?php else : ?>
								<span class="t360-catgrid__initial" aria-hidden="true"><?php echo esc_html( mb_substr( $category['name'], 0, 1 ) ); ?></span>
							<?php endif; ?>
						</span>
						<span class="t360-catgrid__label"><?php echo esc_html( $category['name'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
