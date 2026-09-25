<?php
/**
 * Product rail (hot deals, featured, new arrivals, trending).
 *
 * @package The360Hub
 *
 * @var array $args { key, type, title_key, view_all, position }
 */

defined( 'ABSPATH' ) || exit;

if ( ! t360_has_wc() ) {
	return;
}

$ids = The360Hub\WooCommerce\Catalog::product_ids( $args['type'], (int) t360_setting( 'home_products_count' ) );
if ( ! $ids ) {
	return;
}

$heading_id = 't360-sec-' . $args['key'];
$view_all   = add_query_arg( $args['view_all'], get_permalink( wc_get_page_id( 'shop' ) ) );
?>
<section class="t360-section" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="t360-container">
		<?php
		get_template_part(
			'template-parts/components/section-header',
			null,
			array(
				'title' => (string) t360_setting( $args['title_key'] ),
				'id'    => $heading_id,
				'url'   => $view_all,
			)
		);
		?>
		<ul class="t360-rail">
			<?php
			The360Hub\WooCommerce\Product_Card::render_many(
				$ids,
				array(
					'heading_tag' => 'h3',
					'eager_count' => (int) ( $args['position'] ?? 9 ) <= 1 ? 2 : 0,
				)
			);
			?>
		</ul>
	</div>
</section>
