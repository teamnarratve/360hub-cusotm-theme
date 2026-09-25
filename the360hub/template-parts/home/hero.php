<?php
/**
 * Hero: slider (+ two product side tiles in the "bento" layout).
 *
 * Each slide is either a finished banner image (desktop + mobile art
 * direction) or a designed slide built from a product: brand, name, price
 * and product photo on a coloured panel. With nothing configured, slides are
 * filled from featured products, so the homepage looks complete out of the box.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

use The360Hub\WooCommerce\Catalog;
use The360Hub\WooCommerce\Product_Card;

if ( ! t360_has_wc() ) {
	return;
}

$above_fold = 0 === (int) ( $args['position'] ?? 0 );
$layout     = (string) t360_setting( 'hero_layout' );

// Automatic picks: featured first, then best sellers.
$auto = array_values( array_unique( array_merge( Catalog::product_ids( 'featured', 6 ), Catalog::product_ids( 'bestsellers', 6 ) ) ) );
$used = array();
$next = static function ( int $chosen ) use ( &$auto, &$used ): ?array {
	if ( $chosen ) {
		$used[] = $chosen;
		return Product_Card::teaser( $chosen );
	}
	foreach ( $auto as $pid ) {
		if ( ! in_array( $pid, $used, true ) ) {
			$used[] = $pid;
			$teaser = Product_Card::teaser( $pid );
			if ( $teaser ) {
				return $teaser;
			}
		}
	}
	return null;
};

$slides = array();
for ( $i = 1; $i <= 3; $i++ ) {
	if ( ! t360_setting( "hero_{$i}_enable" ) ) {
		continue;
	}
	$desktop     = (int) t360_setting( "hero_{$i}_image" );
	$mobile      = (int) t360_setting( "hero_{$i}_mobile" );
	$slide_title = (string) t360_setting( "hero_{$i}_title" );

	if ( $desktop || $mobile ) {
		$slides[] = array(
			'type'    => 'image',
			'desktop' => $desktop ? $desktop : $mobile,
			'mobile'  => $mobile ? $mobile : $desktop,
			'url'     => (string) t360_setting( "hero_{$i}_url" ),
			'alt'     => $slide_title,
		);
		continue;
	}

	$product = $next( (int) t360_setting( "hero_{$i}_product" ) );
	if ( ! $product && '' === $slide_title ) {
		continue;
	}
	$eyebrow  = (string) t360_setting( "hero_{$i}_eyebrow" );
	$text     = (string) t360_setting( "hero_{$i}_text" );
	$url      = (string) t360_setting( "hero_{$i}_url" );
	$slides[] = array(
		'type'    => 'designed',
		'bg'      => sanitize_hex_color( (string) t360_setting( "hero_{$i}_bg" ) ),
		'tone'    => 'dark' === t360_setting( "hero_{$i}_tone" ) ? 'dark' : 'light',
		'eyebrow' => '' !== $eyebrow ? $eyebrow : ( $product['brand'] ?? '' ),
		'title'   => '' !== $slide_title ? $slide_title : $product['name'],
		'text'    => '' !== $text ? $text : ( $product['price'] ?? '' ),
		'was'     => '' !== $text ? '' : ( $product['was'] ?? '' ),
		'percent' => $product['percent'] ?? 0,
		'cta'     => (string) t360_setting( "hero_{$i}_cta" ),
		'url'     => '' !== $url ? $url : ( $product['url'] ?? '' ),
		'image'   => $product['image_id'] ?? 0,
	);
}

// Side tiles: biggest current discount + newest product.
$sides = array();
if ( 'bento' === $layout ) {
	$deal_pick = (int) t360_setting( 'hero_side_1_product' );
	if ( ! $deal_pick ) {
		$best = 0;
		foreach ( Catalog::product_ids( 'deals', 12 ) as $pid ) {
			$teaser = Product_Card::teaser( $pid );
			if ( $teaser && $teaser['percent'] > $best && ! in_array( $pid, $used, true ) ) {
				$best      = $teaser['percent'];
				$deal_pick = $pid;
			}
		}
	}
	$new_pick = (int) t360_setting( 'hero_side_2_product' );
	if ( ! $new_pick ) {
		foreach ( Catalog::product_ids( 'new', 6 ) as $pid ) {
			if ( $pid !== $deal_pick && ! in_array( $pid, $used, true ) ) {
				$new_pick = $pid;
				break;
			}
		}
	}
	foreach ( array(
		1 => $deal_pick,
		2 => $new_pick,
	) as $i => $pid ) {
		$teaser = Product_Card::teaser( (int) $pid );
		if ( $teaser ) {
			$sides[] = $teaser + array(
				'label' => (string) t360_setting( "hero_side_{$i}_label" ),
				'bg'    => sanitize_hex_color( (string) t360_setting( "hero_side_{$i}_bg" ) ),
			);
		}
	}
}

if ( ! $slides ) {
	return;
}
$count    = count( $slides );
$interval = (int) t360_setting( 'hero_interval' ) * 1000;
?>
<section class="t360-section t360-section--hero" aria-label="<?php esc_attr_e( 'Featured promotions', 'the360hub' ); ?>">
	<div class="t360-container t360-hero<?php echo $sides ? ' t360-hero--bento' : ''; ?>">
		<div
			class="t360-slider"
			data-t360-slider
			<?php echo t360_setting( 'hero_autoplay' ) && $count > 1 ? 'data-autoplay="' . esc_attr( (string) $interval ) . '"' : ''; ?>
			aria-roledescription="<?php esc_attr_e( 'carousel', 'the360hub' ); ?>"
			aria-label="<?php esc_attr_e( 'Featured promotions', 'the360hub' ); ?>"
		>
			<div class="t360-slider__track" data-t360-slider-track>
				<?php foreach ( $slides as $index => $slide ) : ?>
					<?php
					$eager = $above_fold && 0 === $index;
					/* translators: 1: slide number, 2: total slides */
					$slide_label = sprintf( __( '%1$d of %2$d', 'the360hub' ), $index + 1, $count );
					?>
					<?php if ( 'image' === $slide['type'] ) : ?>
						<?php $src = wp_get_attachment_image_src( $slide['mobile'], 't360-hero-mobile' ); ?>
						<div class="t360-slide t360-slide--image" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'the360hub' ); ?>" aria-label="<?php echo esc_attr( $slide_label ); ?>">
							<?php echo $slide['url'] ? '<a class="t360-slide__imglink" href="' . esc_url( $slide['url'] ) . '">' : ''; ?>
							<picture>
								<source media="(min-width: 768px)" srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $slide['desktop'], 't360-hero' ) ); ?>" sizes="(min-width: 1400px) 1000px, 70vw">
								<img class="t360-slide__img" src="<?php echo esc_url( $src ? $src[0] : '' ); ?>" srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $slide['mobile'], 't360-hero-mobile' ) ); ?>" sizes="100vw" width="<?php echo (int) ( $src[1] ?? 1080 ); ?>" height="<?php echo (int) ( $src[2] ?? 1080 ); ?>" alt="<?php echo esc_attr( $slide['alt'] ); ?>" loading="<?php echo $eager ? 'eager' : 'lazy'; ?>" fetchpriority="<?php echo $eager ? 'high' : 'auto'; ?>" decoding="async">
							</picture>
							<?php echo $slide['url'] ? '</a>' : ''; ?>
						</div>
					<?php else : ?>
						<div class="t360-slide t360-slide--designed is-<?php echo esc_attr( $slide['tone'] ); ?><?php echo $slide['bg'] ? '' : ' is-auto'; ?>" style="<?php echo $slide['bg'] ? '--slide-bg:' . esc_attr( $slide['bg'] ) : ''; ?>" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'the360hub' ); ?>" aria-label="<?php echo esc_attr( $slide_label ); ?>">
							<div class="t360-slide__text">
								<?php if ( $slide['eyebrow'] ) : ?>
									<p class="t360-slide__eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></p>
								<?php endif; ?>
								<h2 class="t360-slide__title"><?php echo esc_html( $slide['title'] ); ?></h2>
								<?php if ( $slide['text'] ) : ?>
									<p class="t360-slide__desc">
										<span><?php echo esc_html( $slide['text'] ); ?></span>
										<?php if ( $slide['was'] ) : ?>
											<del><?php echo esc_html( $slide['was'] ); ?></del>
										<?php endif; ?>
									</p>
								<?php endif; ?>
								<?php if ( $slide['url'] && $slide['cta'] ) : ?>
									<a class="t360-btn t360-slide__cta" href="<?php echo esc_url( $slide['url'] ); ?>"><?php echo esc_html( $slide['cta'] ); ?><?php t360_the_icon( 'arrow-right' ); ?></a>
								<?php endif; ?>
							</div>
							<?php if ( $slide['image'] ) : ?>
								<div class="t360-slide__media">
									<?php if ( $slide['percent'] > 0 ) : ?>
										<span class="t360-slide__burst">
											<?php
											/* translators: %d: discount percent */
											echo esc_html( sprintf( __( '-%d%%', 'the360hub' ), $slide['percent'] ) );
											?>
										</span>
									<?php endif; ?>
									<?php
									echo wp_get_attachment_image(
										$slide['image'],
										'woocommerce_single',
										false,
										array(
											'class'   => 't360-slide__product',
											'alt'     => '',
											'sizes'   => '(min-width: 1024px) 360px, 45vw',
											'loading' => $eager ? 'eager' : 'lazy',
											'fetchpriority' => $eager ? 'high' : 'auto',
										)
									);
									?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<?php if ( $count > 1 ) : ?>
				<div class="t360-slider__controls" data-t360-requires-js>
					<button type="button" class="t360-slider__btn" data-t360-slider-prev>
						<?php t360_the_icon( 'chevron-left' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Previous slide', 'the360hub' ); ?></span>
					</button>
					<div class="t360-slider__dots">
						<?php for ( $d = 0; $d < $count; $d++ ) : ?>
							<button type="button" class="t360-slider__dot" data-t360-slider-dot="<?php echo (int) $d; ?>" aria-current="<?php echo 0 === $d ? 'true' : 'false'; ?>">
								<span class="screen-reader-text">
									<?php
									/* translators: %d: slide number */
									echo esc_html( sprintf( __( 'Go to slide %d', 'the360hub' ), $d + 1 ) );
									?>
								</span>
							</button>
						<?php endfor; ?>
					</div>
					<button type="button" class="t360-slider__btn" data-t360-slider-next>
						<?php t360_the_icon( 'chevron-right' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Next slide', 'the360hub' ); ?></span>
					</button>
					<?php if ( t360_setting( 'hero_autoplay' ) ) : ?>
						<button type="button" class="t360-slider__btn" data-t360-slider-pause aria-pressed="false">
							<?php t360_the_icon( 'pause', 't360-slider__pause' ); ?>
							<?php t360_the_icon( 'play', 't360-slider__play' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Pause slideshow', 'the360hub' ); ?></span>
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $sides ) : ?>
			<div class="t360-hero__side">
				<?php foreach ( $sides as $side ) : ?>
					<a class="t360-sidetile" href="<?php echo esc_url( $side['url'] ); ?>" style="<?php echo $side['bg'] ? '--tile-bg:' . esc_attr( $side['bg'] ) : ''; ?>">
						<span class="t360-sidetile__text">
							<span class="t360-sidetile__label"><?php echo esc_html( $side['label'] ); ?></span>
							<span class="t360-sidetile__name"><?php echo esc_html( $side['name'] ); ?></span>
							<span class="t360-sidetile__price">
								<?php echo esc_html( $side['price'] ); ?>
								<?php if ( $side['percent'] > 0 ) : ?>
									<span class="t360-tag t360-tag--deal">
										<?php
										/* translators: %d: discount percent */
										echo esc_html( sprintf( __( '%d%% off', 'the360hub' ), $side['percent'] ) );
										?>
									</span>
								<?php endif; ?>
							</span>
						</span>
						<?php
						echo wp_get_attachment_image(
							$side['image_id'],
							'woocommerce_thumbnail',
							false,
							array(
								'class'   => 't360-sidetile__img',
								'alt'     => '',
								'sizes'   => '(min-width: 1024px) 180px, 30vw',
								'loading' => 'lazy',
							)
						);
						?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
