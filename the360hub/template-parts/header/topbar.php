<?php
/**
 * Top bar: announcement (all sizes) + utility links and WhatsApp (desktop).
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

if ( ! t360_setting( 'topbar_enable' ) ) {
	return;
}

$notice     = (string) t360_setting( 'header_notice' );
$notice_url = (string) t360_setting( 'header_notice_url' );
$whatsapp   = t360_whatsapp_url();
$links      = array();
foreach ( explode( "\n", (string) t360_setting( 'topbar_links' ) ) as $line ) {
	$parts = array_map( 'trim', explode( '|', $line, 2 ) );
	if ( 2 === count( $parts ) && '' !== $parts[0] && '' !== $parts[1] ) {
		$links[] = array(
			'label' => $parts[0],
			'url'   => 0 === strpos( $parts[1], '/' ) ? home_url( $parts[1] ) : $parts[1],
		);
	}
}
if ( '' === $notice && ! $links && ! $whatsapp ) {
	return;
}
?>
<div class="t360-topbar">
	<div class="t360-container t360-topbar__inner">
		<?php if ( $notice ) : ?>
			<p class="t360-topbar__notice">
				<?php t360_the_icon( 'truck' ); ?>
				<?php if ( $notice_url ) : ?>
					<a href="<?php echo esc_url( $notice_url ); ?>"><?php echo esc_html( $notice ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $notice ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>
		<?php if ( $links || $whatsapp ) : ?>
			<ul class="t360-topbar__links">
				<?php foreach ( $links as $topbar_link ) : ?>
					<li><a href="<?php echo esc_url( $topbar_link['url'] ); ?>"><?php echo esc_html( $topbar_link['label'] ); ?></a></li>
				<?php endforeach; ?>
				<?php if ( $whatsapp ) : ?>
					<li><a href="<?php echo esc_url( $whatsapp ); ?>" rel="noopener" target="_blank"><?php t360_the_icon( 'whatsapp' ); ?><?php esc_html_e( 'WhatsApp', 'the360hub' ); ?></a></li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
	</div>
</div>
