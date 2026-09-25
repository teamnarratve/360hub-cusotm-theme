<?php
/**
 * Template helper functions.
 *
 * Thin, prefixed wrappers so templates stay readable.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a theme setting (see Core\Settings::schema()).
 *
 * @param string $key Setting key.
 * @return mixed
 */
function t360_setting( string $key ) {
	return The360Hub\Core\Settings::get( $key );
}

/**
 * Return an inline SVG icon that references the footer sprite.
 *
 * @param string $name      Icon name.
 * @param string $css_class Extra classes.
 * @return string Safe HTML.
 */
function t360_icon( string $name, string $css_class = '' ): string {
	return The360Hub\Core\Icons::get( $name, $css_class );
}

/**
 * Echo an icon.
 *
 * @param string $name      Icon name.
 * @param string $css_class Extra classes.
 */
function t360_the_icon( string $name, string $css_class = '' ): void {
	echo t360_icon( $name, $css_class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Icons::get().
}

/**
 * Whether WooCommerce is active.
 */
function t360_has_wc(): bool {
	return class_exists( 'WooCommerce' );
}

/**
 * URL of the wishlist page, or empty string when none is configured.
 */
function t360_wishlist_url(): string {
	$page_id = (int) t360_setting( 'wishlist_page' );
	return $page_id && 'publish' === get_post_status( $page_id ) ? (string) get_permalink( $page_id ) : '';
}

/**
 * Number of items in the cart for the server-rendered badge.
 */
function t360_cart_count(): int {
	if ( ! t360_has_wc() || ! WC()->cart ) {
		return 0;
	}
	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * WhatsApp (wa.me) link for the configured number.
 *
 * @param string $text Optional prefilled message.
 */
function t360_whatsapp_url( string $text = '' ): string {
	$number = preg_replace( '/\D+/', '', (string) t360_setting( 'whatsapp_number' ) );
	if ( ! $number ) {
		return '';
	}
	$url = 'https://wa.me/' . $number;
	return $text ? add_query_arg( 'text', rawurlencode( $text ), $url ) : $url;
}

/**
 * Render the site logo, falling back to the site name as text.
 *
 * @param string $css_class Wrapper class.
 */
function t360_the_logo( string $css_class = 't360-logo' ): void {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$name    = get_bloginfo( 'name' );

	printf( '<a class="%s" href="%s" rel="home">', esc_attr( $css_class ), esc_url( home_url( '/' ) ) );
	if ( $logo_id ) {
		echo wp_get_attachment_image(
			$logo_id,
			'medium',
			false,
			array(
				'class'         => 't360-logo__img',
				'alt'           => $name,
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'sizes'         => '160px',
			)
		);
	} else {
		echo '<span class="t360-logo__text" translate="no">' . esc_html( $name ) . '</span>';
	}
	echo '</a>';
}

/**
 * Render a template part with arguments and return the HTML.
 *
 * @param string $slug Template slug.
 * @param array  $args Arguments.
 */
function t360_get_part( string $slug, array $args = array() ): string {
	ob_start();
	get_template_part( $slug, null, $args );
	return (string) ob_get_clean();
}
