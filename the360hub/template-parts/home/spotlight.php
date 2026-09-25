<?php
/**
 * Category spotlight: a coloured category banner next to a row of that
 * category's best sellers.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

use The360Hub\WooCommerce\Catalog;
use The360Hub\WooCommerce\Product_Card;

if ( ! t360_has_wc() ) {
	return;
}

$index    = (int) ( $args['index'] ?? 1 );
$chosen   = (int) t360_setting( "spotlight_{$index}_category" );
$category = Catalog::category_node( $chosen, $index - 1 );
if ( ! $category ) {
	return;
}
$ids = Catalog::product_ids( 'bestsellers', (int) t360_setting( 'home_products_count' ), $category['id'] );
if ( ! $ids ) {
	return;
}

$spot_title = (string) t360_setting( "spotlight_{$index}_title" );
$spot_title = '' !== $spot_title ? $spot_title : $category['name'];
$spot_text  = (string) t360_setting( "spotlight_{$index}_text" );
$image      = (int) t360_setting( "spotlight_{$index}_image" );
$image      = $image ? $image : $category['image'];
$bg         = sanitize_hex_color( (string) t360_setting( "spotlight_{$index}_bg" ) );
$heading_id = 't360-sec-' . $args['key'];
?>
<section class="t360-section t360-spotlight" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="t360-container t360-spotlight__grid">
		<a class="t360-spotlight__banner" href="<?php echo esc_url( $category['url'] ); ?>" style="<?php echo $bg ? '--tile-bg:' . esc_attr( $bg ) : ''; ?>">
			<span class="t360-spotlight__text">
				<span class="t360-spotlight__title" id="<?php echo esc_attr( $heading_id ); ?>" role="heading" aria-level="2"><?php echo esc_html( $spot_title ); ?></span>
				<?php if ( $spot_text ) : ?>
					<span class="t360-spotlight__sub"><?php echo esc_html( $spot_text ); ?></span>
				<?php endif; ?>
				<span class="t360-spotlight__cta">
					<?php
					/* translators: %s: category name */
					echo esc_html( sprintf( __( 'Shop all %s', 'the360hub' ), $category['name'] ) );
					?>
					<?php t360_the_icon( 'arrow-right' ); ?>
				</span>
			</span>
			<?php if ( $image ) : ?>
				<?php
				echo wp_get_attachment_image(
					$image,
					'woocommerce_thumbnail',
					false,
					array(
						'class'   => 't360-spotlight__img',
						'alt'     => '',
						'sizes'   => '(min-width: 1024px) 280px, 30vw',
						'loading' => 'lazy',
					)
				);
				?>
			<?php endif; ?>
		</a>
		<ul class="t360-rail t360-spotlight__rail">
			<?php Product_Card::render_many( $ids, array( 'heading_tag' => 'h3' ) ); ?>
		</ul>
	</div>
</section>
