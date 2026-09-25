<?php
/**
 * Hero banners.
 *
 * Up to three banner slots (desktop + mobile art direction via <picture>).
 * Several banners form a swipeable scroll-snap row with the next banner
 * peeking in; nothing auto-rotates. With no banner images configured, a
 * text hero is shown so the page still has a clear message and CTA.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$slides = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$desktop = (int) t360_setting( "hero_{$i}_image" );
	$mobile  = (int) t360_setting( "hero_{$i}_mobile" );
	if ( ! $desktop && ! $mobile ) {
		continue;
	}
	$slides[] = array(
		'desktop' => $desktop ? $desktop : $mobile,
		'mobile'  => $mobile ? $mobile : $desktop,
		'url'     => (string) t360_setting( "hero_{$i}_url" ),
		'alt'     => (string) t360_setting( "hero_{$i}_alt" ),
	);
}

$heading    = (string) t360_setting( 'hero_heading' );
$above_fold = 0 === (int) ( $args['position'] ?? 0 );

if ( ! $slides ) :
	$cta_url = (string) t360_setting( 'hero_cta_url' );
	if ( ! $cta_url && t360_has_wc() ) {
		$cta_url = get_permalink( wc_get_page_id( 'shop' ) );
	}
	?>
	<section class="t360-section t360-hero-text" aria-labelledby="t360-hero-title">
		<div class="t360-container">
			<div class="t360-hero-text__panel">
				<h2 class="t360-hero-text__title" id="t360-hero-title"><?php echo esc_html( $heading ); ?></h2>
				<p class="t360-hero-text__lead"><?php echo esc_html( (string) t360_setting( 'hero_text' ) ); ?></p>
				<?php if ( $cta_url ) : ?>
					<a class="t360-btn t360-btn--light" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( (string) t360_setting( 'hero_cta_label' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return;
endif;
?>
<section class="t360-section t360-hero" aria-label="<?php esc_attr_e( 'Featured promotions', 'the360hub' ); ?>">
	<div class="t360-container">
		<ul class="t360-hero__track<?php echo count( $slides ) > 1 ? ' is-multi' : ''; ?>">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<?php
				$eager = $above_fold && 0 === $index;
				$alt   = '' !== $slide['alt'] ? $slide['alt'] : ( 0 === $index ? $heading : '' );
				$src   = wp_get_attachment_image_src( $slide['mobile'], 't360-hero-mobile' );
				if ( ! $src ) {
					continue;
				}
				$img = sprintf(
					'<img class="t360-hero__img" src="%1$s" srcset="%2$s" sizes="100vw" width="%3$d" height="%4$d" alt="%5$s" loading="%6$s" fetchpriority="%7$s" decoding="%8$s">',
					esc_url( $src[0] ),
					esc_attr( (string) wp_get_attachment_image_srcset( $slide['mobile'], 't360-hero-mobile' ) ),
					(int) $src[1],
					(int) $src[2],
					esc_attr( $alt ),
					$eager ? 'eager' : 'lazy',
					$eager ? 'high' : 'auto',
					$eager ? 'sync' : 'async'
				);
				?>
				<li class="t360-hero__slide">
					<?php echo $slide['url'] ? '<a class="t360-hero__link" href="' . esc_url( $slide['url'] ) . '">' : '<div class="t360-hero__link">'; ?>
						<picture>
							<source media="(min-width: 768px)" srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $slide['desktop'], 't360-hero' ) ); ?>" sizes="(min-width: 1320px) 1256px, 100vw">
							<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
						</picture>
					<?php echo $slide['url'] ? '</a>' : '</div>'; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
