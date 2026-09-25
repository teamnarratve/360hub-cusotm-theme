<?php
/**
 * Promo tiles in a bento grid (one tall, one wide, two small on desktop;
 * two-up on mobile). Each tile defaults to a top-level category with its
 * image; every part can be overridden in the Customizer.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

use The360Hub\WooCommerce\Catalog;

if ( ! t360_has_wc() ) {
	return;
}

$tiles = array();
$auto  = 0;
for ( $i = 1; $i <= 4; $i++ ) {
	$chosen   = (int) t360_setting( "tile_{$i}_category" );
	$category = Catalog::category_node( $chosen, $chosen ? 0 : $auto );
	if ( ! $chosen ) {
		++$auto;
	}
	$tile_title = (string) t360_setting( "tile_{$i}_title" );
	$url        = (string) t360_setting( "tile_{$i}_url" );
	$image      = (int) t360_setting( "tile_{$i}_image" );
	$text       = (string) t360_setting( "tile_{$i}_text" );

	$tile_title = '' !== $tile_title ? $tile_title : ( $category['name'] ?? '' );
	if ( '' === $tile_title ) {
		continue;
	}
	$count   = $category['count'] ?? 0;
	$tiles[] = array(
		'title' => $tile_title,
		/* translators: %s: number of products */
		'text'  => '' !== $text ? $text : ( $count ? sprintf( _n( '%s product', '%s products', $count, 'the360hub' ), number_format_i18n( $count ) ) : '' ),
		'url'   => '' !== $url ? $url : ( $category['url'] ?? '' ),
		'image' => $image ? $image : ( $category['image'] ?? 0 ),
		'bg'    => sanitize_hex_color( (string) t360_setting( "tile_{$i}_bg" ) ),
		'tone'  => 'light' === t360_setting( "tile_{$i}_tone" ) ? 'light' : 'dark',
	);
}
if ( ! $tiles ) {
	return;
}
?>
<section class="t360-section" aria-label="<?php esc_attr_e( 'Promotions', 'the360hub' ); ?>">
	<div class="t360-container">
		<ul class="t360-bento t360-bento--<?php echo count( $tiles ); ?>">
			<?php foreach ( $tiles as $tile ) : ?>
				<li>
					<a class="t360-bento__tile is-<?php echo esc_attr( $tile['tone'] ); ?>" href="<?php echo esc_url( $tile['url'] ); ?>" style="<?php echo $tile['bg'] ? '--tile-bg:' . esc_attr( $tile['bg'] ) : ''; ?>">
						<span class="t360-bento__text">
							<span class="t360-bento__title"><?php echo esc_html( $tile['title'] ); ?></span>
							<?php if ( $tile['text'] ) : ?>
								<span class="t360-bento__sub"><?php echo esc_html( $tile['text'] ); ?></span>
							<?php endif; ?>
							<span class="t360-bento__cta"><?php esc_html_e( 'Shop now', 'the360hub' ); ?><?php t360_the_icon( 'arrow-right' ); ?></span>
						</span>
						<?php if ( $tile['image'] ) : ?>
							<?php
							echo wp_get_attachment_image(
								$tile['image'],
								'woocommerce_thumbnail',
								false,
								array(
									'class'   => 't360-bento__img',
									'alt'     => '',
									'sizes'   => '(min-width: 1024px) 320px, 40vw',
									'loading' => 'lazy',
								)
							);
							?>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
