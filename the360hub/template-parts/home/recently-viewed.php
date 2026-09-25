<?php
/**
 * Recently viewed products. The list lives in the visitor's browser
 * (localStorage), so the section is filled by lists.js and stays hidden
 * until there is something to show. This keeps the homepage cacheable.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

if ( ! t360_has_wc() ) {
	return;
}
$heading_id = 't360-sec-' . $args['key'];
?>
<section class="t360-section" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>" data-t360-list="recent" hidden>
	<div class="t360-container">
		<?php
		get_template_part(
			'template-parts/components/section-header',
			null,
			array(
				'title' => (string) t360_setting( 'recent_title' ),
				'id'    => $heading_id,
			)
		);
		?>
		<ul class="t360-rail" data-t360-list-items></ul>
	</div>
</section>
