<?php
/**
 * Section heading with an optional "View all" link.
 *
 * @package The360Hub
 *
 * @var array $args { title:string, id:string, url?:string, hidden?:bool }
 */

defined( 'ABSPATH' ) || exit;

$section_title = (string) ( $args['title'] ?? '' );
if ( '' === $section_title ) {
	return;
}
?>
<div class="t360-section__head<?php echo ! empty( $args['hidden'] ) ? ' screen-reader-text' : ''; ?>">
	<h2 class="t360-section__title" id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $section_title ); ?></h2>
	<?php if ( ! empty( $args['url'] ) ) : ?>
		<a class="t360-section__more" href="<?php echo esc_url( $args['url'] ); ?>">
			<?php esc_html_e( 'View all', 'the360hub' ); ?>
			<span class="screen-reader-text"><?php echo esc_html( $section_title ); ?></span>
			<?php t360_the_icon( 'chevron-right' ); ?>
		</a>
	<?php endif; ?>
</div>
