<?php
/**
 * Brand showcase (WooCommerce Brands taxonomy).
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$brands = t360_has_wc() ? The360Hub\WooCommerce\Catalog::brands( 16 ) : array();
if ( ! $brands ) {
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
				'title' => (string) t360_setting( 'brands_title' ),
				'id'    => $heading_id,
			)
		);
		?>
		<ul class="t360-brands">
			<?php foreach ( $brands as $brand ) : ?>
				<li>
					<a class="t360-brands__item" href="<?php echo esc_url( $brand['url'] ); ?>">
						<?php if ( $brand['image'] ) : ?>
							<?php
							echo wp_get_attachment_image(
								$brand['image'],
								't360-tile',
								false,
								array(
									'alt'     => $brand['name'],
									'sizes'   => '112px',
									'loading' => 'lazy',
									'class'   => 't360-brands__logo',
								)
							);
							?>
						<?php else : ?>
							<span class="t360-brands__name"><?php echo esc_html( $brand['name'] ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
