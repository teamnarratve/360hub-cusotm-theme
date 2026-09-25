<?php
/**
 * Section heading with an optional icon, subtitle, extra controls and
 * "View all" link.
 *
 * @package The360Hub
 *
 * @var array $args { title:string, id:string, url?:string, hidden?:bool, icon?:string, subtitle?:string, after?:string }
 */

defined( 'ABSPATH' ) || exit;

$section_title = (string) ( $args['title'] ?? '' );
if ( '' === $section_title ) {
	return;
}
?>
<div class="t360-section__head<?php echo ! empty( $args['hidden'] ) ? ' screen-reader-text' : ''; ?>">
	<div class="t360-section__titles">
		<h2 class="t360-section__title" id="<?php echo esc_attr( $args['id'] ); ?>">
			<?php if ( ! empty( $args['icon'] ) ) : ?>
				<?php t360_the_icon( $args['icon'], 't360-section__icon' ); ?>
			<?php endif; ?>
			<?php echo esc_html( $section_title ); ?>
		</h2>
		<?php if ( ! empty( $args['subtitle'] ) ) : ?>
			<p class="t360-section__sub"><?php echo esc_html( $args['subtitle'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $args['after'] ) ) : ?>
		<?php echo $args['after']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup built by the calling template with escaped values. ?>
	<?php endif; ?>
	<?php if ( ! empty( $args['url'] ) ) : ?>
		<a class="t360-section__more" href="<?php echo esc_url( $args['url'] ); ?>">
			<?php esc_html_e( 'View all', 'the360hub' ); ?>
			<span class="screen-reader-text"><?php echo esc_html( $section_title ); ?></span>
			<?php t360_the_icon( 'arrow-right' ); ?>
		</a>
	<?php endif; ?>
</div>
