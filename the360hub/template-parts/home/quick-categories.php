<?php
/**
 * Quick category row: compact, horizontally scrollable shortcuts to
 * top-level departments.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$tree = t360_has_wc() ? The360Hub\WooCommerce\Catalog::category_tree() : array();
if ( ! $tree ) {
	return;
}
?>
<section class="t360-section t360-section--tight" aria-labelledby="t360-sec-<?php echo esc_attr( $args['key'] ); ?>">
	<div class="t360-container">
		<?php
		get_template_part(
			'template-parts/components/section-header',
			null,
			array(
				'title'  => (string) t360_setting( 'quick_cats_title' ),
				'id'     => 't360-sec-' . $args['key'],
				'hidden' => true,
			)
		);
		?>
		<ul class="t360-quickcats">
			<?php foreach ( $tree as $category ) : ?>
				<li>
					<a class="t360-quickcats__item" href="<?php echo esc_url( $category['url'] ); ?>">
						<span class="t360-quickcats__media">
							<?php if ( $category['image'] ) : ?>
								<?php
								echo wp_get_attachment_image(
									$category['image'],
									't360-tile',
									false,
									array(
										'alt'     => '',
										'sizes'   => '56px',
										'loading' => 'lazy',
									)
								);
								?>
							<?php else : ?>
								<span class="t360-quickcats__initial" aria-hidden="true"><?php echo esc_html( mb_substr( $category['name'], 0, 1 ) ); ?></span>
							<?php endif; ?>
						</span>
						<span class="t360-quickcats__label"><?php echo esc_html( $category['name'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
