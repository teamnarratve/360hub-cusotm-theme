<?php
/**
 * Call-to-action banner (defaults to WhatsApp expert help).
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$cta_title = (string) t360_setting( 'cta_title' );
$url       = (string) t360_setting( 'cta_url' );
$url       = '' !== $url ? $url : t360_whatsapp_url( (string) t360_setting( 'whatsapp_message' ) );
if ( '' === $url || '' === $cta_title ) {
	return;
}
$is_whatsapp = false !== strpos( $url, 'wa.me' );
$bg          = sanitize_hex_color( (string) t360_setting( 'cta_bg' ) );
$image       = (int) t360_setting( 'cta_image' );
?>
<section class="t360-section" aria-label="<?php echo esc_attr( $cta_title ); ?>">
	<div class="t360-container">
		<div class="t360-cta" style="<?php echo $bg ? '--cta-bg:' . esc_attr( $bg ) : ''; ?>">
			<div class="t360-cta__text">
				<p class="t360-cta__title"><?php echo esc_html( $cta_title ); ?></p>
				<?php if ( t360_setting( 'cta_text' ) ) : ?>
					<p class="t360-cta__desc"><?php echo esc_html( (string) t360_setting( 'cta_text' ) ); ?></p>
				<?php endif; ?>
				<a class="t360-btn t360-btn--light" href="<?php echo esc_url( $url ); ?>" <?php echo $is_whatsapp ? 'rel="noopener" target="_blank"' : ''; ?>>
					<?php t360_the_icon( $is_whatsapp ? 'whatsapp' : 'arrow-right' ); ?>
					<?php echo esc_html( (string) t360_setting( 'cta_button' ) ); ?>
				</a>
			</div>
			<?php if ( $image ) : ?>
				<?php
				echo wp_get_attachment_image(
					$image,
					'large',
					false,
					array(
						'class'   => 't360-cta__img',
						'alt'     => '',
						'sizes'   => '(min-width: 1024px) 420px, 60vw',
						'loading' => 'lazy',
					)
				);
				?>
			<?php else : ?>
				<span class="t360-cta__art" aria-hidden="true"><?php t360_the_icon( $is_whatsapp ? 'whatsapp' : 'sparkles' ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</section>
