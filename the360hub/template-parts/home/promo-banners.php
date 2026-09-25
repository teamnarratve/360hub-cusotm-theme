<?php
/**
 * Promotional banners (two slots).
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$banners = array();
for ( $i = 1; $i <= 2; $i++ ) {
	$image = (int) t360_setting( "promo_{$i}_image" );
	if ( $image ) {
		$banners[] = array(
			'image' => $image,
			'url'   => (string) t360_setting( "promo_{$i}_url" ),
			'alt'   => (string) t360_setting( "promo_{$i}_alt" ),
		);
	}
}
if ( ! $banners ) {
	return;
}
?>
<section class="t360-section" aria-label="<?php esc_attr_e( 'Promotions', 'the360hub' ); ?>">
	<div class="t360-container">
		<ul class="t360-promos">
			<?php foreach ( $banners as $banner ) : ?>
				<li>
					<?php
					$img = wp_get_attachment_image(
						$banner['image'],
						'large',
						false,
						array(
							'class'   => 't360-promos__img',
							'alt'     => $banner['alt'],
							'sizes'   => '(min-width: 1320px) 620px, (min-width: 768px) 48vw, 100vw',
							'loading' => 'lazy',
						)
					);
					if ( $banner['url'] ) {
						echo '<a class="t360-promos__link" href="' . esc_url( $banner['url'] ) . '">' . $img . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
