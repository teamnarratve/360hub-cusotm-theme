<?php
/**
 * Tabbed product rows (Featured / Best sellers / New / Top rated …).
 *
 * The first tab is rendered on the server; the others are fetched on first
 * use from ?wc-ajax=t360_cards (same card template), which keeps the page
 * light. Without JS the first tab still shows.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

use The360Hub\WooCommerce\Catalog;
use The360Hub\WooCommerce\Product_Card;

if ( ! t360_has_wc() ) {
	return;
}

$labels  = array(
	'featured'    => __( 'Featured', 'the360hub' ),
	'bestsellers' => __( 'Best sellers', 'the360hub' ),
	'new'         => __( 'New arrivals', 'the360hub' ),
	'top_rated'   => __( 'Top rated', 'the360hub' ),
	'deals'       => __( 'On sale', 'the360hub' ),
);
$limit   = (int) t360_setting( 'home_products_count' );
$tab_ids = array();
foreach ( array_map( 'trim', explode( ',', (string) t360_setting( 'tabs_items' ) ) ) as $tab_type ) {
	if ( isset( $labels[ $tab_type ] ) && ! isset( $tab_ids[ $tab_type ] ) ) {
		$ids = Catalog::product_ids( $tab_type, $limit );
		if ( $ids ) {
			$tab_ids[ $tab_type ] = $ids;
		}
	}
}
if ( ! $tab_ids ) {
	return;
}
$heading_id = 't360-sec-' . $args['key'];
$first      = array_key_first( $tab_ids );

ob_start();
?>
<div class="t360-tabs__list" role="tablist" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>" data-t360-requires-js>
	<?php foreach ( $tab_ids as $tab_type => $ids ) : ?>
		<button type="button" role="tab" class="t360-tabs__tab" id="t360-tab-<?php echo esc_attr( $tab_type ); ?>" aria-controls="t360-panel-<?php echo esc_attr( $tab_type ); ?>" aria-selected="<?php echo $tab_type === $first ? 'true' : 'false'; ?>" tabindex="<?php echo $tab_type === $first ? '0' : '-1'; ?>"><?php echo esc_html( $labels[ $tab_type ] ); ?></button>
	<?php endforeach; ?>
</div>
<?php
$tablist = (string) ob_get_clean();
?>
<section class="t360-section t360-tabs" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>" data-t360-tabs>
	<div class="t360-container">
		<?php
		get_template_part(
			'template-parts/components/section-header',
			null,
			array(
				'title' => (string) t360_setting( 'tabs_title' ),
				'id'    => $heading_id,
				'after' => $tablist,
			)
		);
		?>
		<?php foreach ( $tab_ids as $tab_type => $ids ) : ?>
			<div class="t360-tabs__panel" role="tabpanel" id="t360-panel-<?php echo esc_attr( $tab_type ); ?>" aria-labelledby="t360-tab-<?php echo esc_attr( $tab_type ); ?>" data-ids="<?php echo esc_attr( implode( ',', $ids ) ); ?>" <?php echo $tab_type === $first ? '' : 'hidden'; ?>>
				<ul class="t360-rail">
					<?php
					if ( $tab_type === $first ) {
						Product_Card::render_many( $ids, array( 'heading_tag' => 'h3' ) );
					}
					?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>
</section>
