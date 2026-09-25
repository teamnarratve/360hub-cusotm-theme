<?php
/**
 * Site footer. Link columns collapse into disclosure rows on mobile.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$about     = (string) t360_setting( 'footer_about' );
$phone     = (string) t360_setting( 'footer_phone' );
$email     = (string) t360_setting( 'footer_email' );
$whatsapp  = t360_whatsapp_url();
$payments  = array_filter( array_map( 'trim', explode( ',', (string) t360_setting( 'footer_payments' ) ) ) );
$copyright = (string) t360_setting( 'footer_copyright' );
if ( '' === $copyright ) {
	/* translators: 1: year, 2: site name */
	$copyright = sprintf( __( '© %1$s %2$s. All rights reserved.', 'the360hub' ), wp_date( 'Y' ), get_bloginfo( 'name' ) );
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
?>
<footer class="t360-footer">
	<div class="t360-container t360-footer__grid">
		<div class="t360-footer__brand">
			<?php t360_the_logo( 't360-logo t360-logo--footer' ); ?>
			<?php if ( $about ) : ?>
				<p class="t360-footer__about"><?php echo esc_html( $about ); ?></p>
			<?php endif; ?>
			<ul class="t360-footer__contact">
				<?php if ( $whatsapp ) : ?>
					<li><a href="<?php echo esc_url( $whatsapp ); ?>" rel="noopener" target="_blank"><?php t360_the_icon( 'chat' ); ?><?php esc_html_e( 'WhatsApp us', 'the360hub' ); ?></a></li>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<li><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php t360_the_icon( 'phone' ); ?><?php echo esc_html( $phone ); ?></a></li>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<li><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php t360_the_icon( 'mail' ); ?><?php echo esc_html( $email ); ?></a></li>
				<?php endif; ?>
			</ul>
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
	</div>

	<div class="t360-footer__bottom">
		<div class="t360-container t360-footer__bottom-inner">
			<?php if ( $payments ) : ?>
				<ul class="t360-footer__payments" aria-label="<?php esc_attr_e( 'Payment methods', 'the360hub' ); ?>">
					<?php foreach ( $payments as $method ) : ?>
						<li><?php echo esc_html( $method ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<p class="t360-footer__copy"><?php echo esc_html( $copyright ); ?></p>
		</div>
	</div>
</footer>
