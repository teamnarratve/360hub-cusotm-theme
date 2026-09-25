<?php
/**
 * Site footer: optional newsletter strip, brand + social, menu columns,
 * contact, payment methods and copyright. Link columns collapse into
 * disclosure rows on mobile.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$about      = (string) t360_setting( 'footer_about' );
$phone      = (string) t360_setting( 'footer_phone' );
$email      = (string) t360_setting( 'footer_email' );
$address    = (string) t360_setting( 'footer_address' );
$whatsapp   = t360_whatsapp_url();
$payments   = array_filter( array_map( 'trim', explode( ',', (string) t360_setting( 'footer_payments' ) ) ) );
$newsletter = trim( (string) t360_setting( 'footer_newsletter' ) );
$copyright  = (string) t360_setting( 'footer_copyright' );
if ( '' === $copyright ) {
	/* translators: 1: year, 2: site name */
	$copyright = sprintf( __( '© %1$s %2$s. All rights reserved.', 'the360hub' ), wp_date( 'Y' ), get_bloginfo( 'name' ) );
}

$socials = array();
foreach ( The360Hub\Core\Settings::socials() as $network => $label ) {
	$url = (string) t360_setting( 'social_' . $network );
	if ( $url ) {
		$socials[ $network ] = array( $label, $url );
	}
}

$columns = array();
foreach ( array( 'footer_1', 'footer_2' ) as $location ) {
	if ( ! has_nav_menu( $location ) ) {
		continue;
	}
	$locations   = get_nav_menu_locations();
	$footer_menu = wp_get_nav_menu_object( $locations[ $location ] );
	$columns[]   = array(
		'title' => $footer_menu ? $footer_menu->name : '',
		'menu'  => wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 't360-footer__links',
				'depth'          => 1,
				'echo'           => false,
			)
		),
	);
}
$style = 'light' === t360_setting( 'footer_style' ) ? 'light' : 'dark';
?>
<footer class="t360-footer t360-footer--<?php echo esc_attr( $style ); ?>">
	<?php if ( $newsletter ) : ?>
		<div class="t360-footer__news">
			<div class="t360-container t360-footer__news-inner">
				<p class="t360-footer__news-title"><?php t360_the_icon( 'mail' ); ?><?php echo esc_html( (string) t360_setting( 'footer_newsletter_title' ) ); ?></p>
				<div class="t360-footer__news-form"><?php echo do_shortcode( $newsletter ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- admin-provided shortcode output. ?></div>
			</div>
		</div>
	<?php endif; ?>

	<div class="t360-container t360-footer__grid">
		<div class="t360-footer__brand">
			<?php t360_the_logo( 't360-logo t360-logo--footer' ); ?>
			<?php if ( $about ) : ?>
				<p class="t360-footer__about"><?php echo esc_html( $about ); ?></p>
			<?php endif; ?>
			<?php if ( $socials ) : ?>
				<ul class="t360-footer__social">
					<?php foreach ( $socials as $network => [ $label, $url ] ) : ?>
						<li>
							<a href="<?php echo esc_url( $url ); ?>" rel="noopener me" target="_blank">
								<?php t360_the_icon( $network ); ?>
								<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php foreach ( $columns as $col ) : ?>
			<details class="t360-footer__col" data-t360-footer-col>
				<summary class="t360-footer__title">
					<?php echo esc_html( $col['title'] ); ?>
					<?php t360_the_icon( 'chevron-down' ); ?>
				</summary>
				<?php echo $col['menu']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
			</details>
		<?php endforeach; ?>

		<?php if ( $whatsapp || $phone || $email || $address ) : ?>
			<details class="t360-footer__col" data-t360-footer-col>
				<summary class="t360-footer__title">
					<?php esc_html_e( 'Contact us', 'the360hub' ); ?>
					<?php t360_the_icon( 'chevron-down' ); ?>
				</summary>
				<ul class="t360-footer__contact">
					<?php if ( $whatsapp ) : ?>
						<li><a href="<?php echo esc_url( $whatsapp ); ?>" rel="noopener" target="_blank"><?php t360_the_icon( 'whatsapp' ); ?><?php esc_html_e( 'WhatsApp us', 'the360hub' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<li><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php t360_the_icon( 'phone' ); ?><?php echo esc_html( $phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<li><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php t360_the_icon( 'mail' ); ?><?php echo esc_html( $email ); ?></a></li>
					<?php endif; ?>
					<?php if ( $address ) : ?>
						<li><span><?php t360_the_icon( 'map-pin' ); ?><?php echo esc_html( $address ); ?></span></li>
					<?php endif; ?>
				</ul>
			</details>
		<?php endif; ?>
	</div>

	<div class="t360-footer__bottom">
		<div class="t360-container t360-footer__bottom-inner">
			<p class="t360-footer__copy"><?php echo esc_html( $copyright ); ?></p>
			<?php if ( $payments ) : ?>
				<ul class="t360-footer__payments" aria-label="<?php esc_attr_e( 'Payment methods', 'the360hub' ); ?>">
					<?php foreach ( $payments as $method ) : ?>
						<li><?php echo esc_html( $method ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php if ( $whatsapp && t360_setting( 'whatsapp_float' ) ) : ?>
	<a class="t360-wafloat" href="<?php echo esc_url( t360_whatsapp_url( (string) t360_setting( 'whatsapp_message' ) ) ); ?>" rel="noopener" target="_blank">
		<?php t360_the_icon( 'whatsapp' ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Chat with us on WhatsApp', 'the360hub' ); ?></span>
	</a>
<?php endif; ?>
