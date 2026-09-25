<?php
/**
 * Homepage: modules rendered in the order set in the Customizer.
 *
 * Without WooCommerce there is no catalogue to merchandise, so this falls
 * back to the regular index template.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

if ( ! t360_has_wc() ) {
	get_template_part( 'index' );
	return;
}

get_header();
?>
<div class="t360-home">
	<?php The360Hub\Home\Sections::render(); ?>
</div>
<?php
get_footer();
