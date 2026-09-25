<?php
/**
 * Not found: search plus the main departments.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

get_header();
$tree = t360_has_wc() ? The360Hub\WooCommerce\Catalog::category_tree() : array();
?>
<div class="t360-container t360-page">
	<div class="t360-empty">
		<h1 class="t360-empty__title"><?php esc_html_e( 'We couldn’t find that page', 'the360hub' ); ?></h1>
		<p><?php esc_html_e( 'The link may be old or the product may no longer be available. Try searching instead.', 'the360hub' ); ?></p>
		<?php
		get_template_part(
			'template-parts/search/form',
			null,
			array(
				'id'          => '404',
				'placeholder' => (string) t360_setting( 'header_search_ph' ),
			)
		);
		?>
	</div>
	<?php if ( $tree ) : ?>
		<h2 class="t360-section__title"><?php esc_html_e( 'Browse departments', 'the360hub' ); ?></h2>
		<ul class="t360-chips">
			<?php foreach ( $tree as $category ) : ?>
				<li><a class="t360-chip" href="<?php echo esc_url( $category['url'] ); ?>"><?php echo esc_html( $category['name'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php
get_footer();
