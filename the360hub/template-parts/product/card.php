<?php
/**
 * Product card.
 *
 * The product title link is stretched over the whole card (one link per
 * product for screen readers); wishlist and add-to-cart sit above it.
 * WooCommerce's loop actions are still fired so plugins can add to cards;
 * the theme has removed only WooCommerce's default callbacks.
 *
 * @package The360Hub
 *
 * @var array $args {
 *     @type array      $card        Data from Product_Card::data().
 *     @type WC_Product $product     Product.
 *     @type string     $heading_tag h2|h3.
 *     @type bool       $eager       Load the image eagerly (above the fold).
 * }
 */

defined( 'ABSPATH' ) || exit;

$card        = $args['card'];
$pricing     = $card['pricing'];
$heading_tag = in_array( $args['heading_tag'] ?? 'h3', array( 'h2', 'h3', 'h4' ), true ) ? $args['heading_tag'] : 'h3';
$eager       = ! empty( $args['eager'] );
$sizes       = '(min-width: 1400px) 260px, (min-width: 1024px) 22vw, (min-width: 640px) 31vw, 47vw';
$is_out      = $card['stock'] && 'out' === $card['stock']['type'];
$atc_style   = (string) t360_setting( 'card_add_to_cart' );
$classes     = array(
	't360-card',
	't360-card--' . sanitize_html_class( (string) t360_setting( 'card_style' ) ),
	$is_out ? 'is-out' : '',
	$card['alt_image_id'] ? 'has-alt' : '',
);

// Second image loads on first hover only (core.js), so it costs nothing on phones.
$alt_attrs = '';
if ( $card['alt_image_id'] ) {
	$alt_src = wp_get_attachment_image_src( $card['alt_image_id'], 'woocommerce_thumbnail' );
	if ( $alt_src ) {
		$alt_attrs = sprintf(
			' data-t360-alt-src="%s" data-t360-alt-srcset="%s"',
			esc_url( $alt_src[0] ),
			esc_attr( (string) wp_get_attachment_image_srcset( $card['alt_image_id'], 'woocommerce_thumbnail' ) )
		);
	}
}
?>
<article class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

	<div class="t360-card__media"<?php echo $alt_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>>
		<?php
		if ( $card['image_id'] ) {
			echo wp_get_attachment_image(
				$card['image_id'],
				'woocommerce_thumbnail',
				false,
				array(
					'class'         => 't360-card__img',
					'alt'           => '',
					'sizes'         => $sizes,
					'loading'       => $eager ? 'eager' : 'lazy',
					'fetchpriority' => $eager ? 'high' : 'auto',
					'decoding'      => 'async',
				)
			);
		} else {
			// wc_placeholder_img() returns escaped markup; kses would strip srcset/sizes.
			echo wc_placeholder_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'woocommerce_thumbnail',
				array(
					'class' => 't360-card__img',
					'alt'   => '',
				)
			);
		}
		?>

		<div class="t360-card__badges">
			<?php if ( $pricing['percent'] > 0 ) : ?>
				<span class="t360-tag t360-tag--deal">
					<?php if ( $pricing['up_to'] ) : ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Up to', 'the360hub' ); ?></span>
					<?php endif; ?>
					<?php
					/* translators: %d: discount percent */
					echo esc_html( sprintf( __( '-%d%%', 'the360hub' ), $pricing['percent'] ) );
					?>
				</span>
			<?php endif; ?>
			<?php if ( $card['is_new'] ) : ?>
				<span class="t360-tag t360-tag--new"><?php esc_html_e( 'New', 'the360hub' ); ?></span>
			<?php endif; ?>
			<?php if ( $is_out ) : ?>
				<span class="t360-tag t360-tag--muted"><?php esc_html_e( 'Sold out', 'the360hub' ); ?></span>
			<?php endif; ?>
		</div>

		<button type="button" class="t360-card__wish" data-t360-wish="<?php echo esc_attr( (string) $card['id'] ); ?>" aria-pressed="false" data-t360-requires-js>
			<?php t360_the_icon( 'heart' ); ?>
			<span class="screen-reader-text">
				<?php
				/* translators: %s: product name */
				echo esc_html( sprintf( __( 'Save %s to wishlist', 'the360hub' ), $card['name'] ) );
				?>
			</span>
		</button>

		<?php if ( $card['add_to_cart'] && 'icon' === $atc_style ) : ?>
			<a
				class="t360-card__atc-icon"
				href="<?php echo esc_url( $card['add_to_cart']['url'] ); ?>"
				<?php if ( $card['add_to_cart']['ajax'] ) : ?>
					data-t360-atc="<?php echo esc_attr( (string) $card['id'] ); ?>" rel="nofollow" role="button"
				<?php endif; ?>
			>
				<?php t360_the_icon( $card['add_to_cart']['ajax'] ? 'bag' : 'arrow-right' ); ?>
				<span class="screen-reader-text">
					<?php
					/* translators: 1: button label, 2: product name */
					echo esc_html( sprintf( __( '%1$s: %2$s', 'the360hub' ), $card['add_to_cart']['label'], $card['name'] ) );
					?>
				</span>
			</a>
		<?php endif; ?>

		<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
	</div>

	<div class="t360-card__body">
		<?php if ( $card['brand'] || $card['reviews'] > 0 ) : ?>
			<div class="t360-card__meta">
				<?php if ( $card['brand'] ) : ?>
					<span class="t360-card__brand"><?php echo esc_html( $card['brand'] ); ?></span>
				<?php endif; ?>
				<?php if ( $card['reviews'] > 0 && wc_review_ratings_enabled() ) : ?>
					<span class="t360-card__rating">
						<span class="t360-stars" style="--r:<?php echo esc_attr( (string) round( $card['rating'], 1 ) ); ?>" aria-hidden="true"></span>
						<span class="t360-card__reviews" aria-hidden="true"><?php echo esc_html( number_format_i18n( $card['rating'], 1 ) ); ?></span>
						<span class="screen-reader-text">
							<?php
							/* translators: 1: average rating, 2: review count */
							echo esc_html( sprintf( _n( 'Rated %1$s out of 5 from %2$s review', 'Rated %1$s out of 5 from %2$s reviews', $card['reviews'], 'the360hub' ), number_format_i18n( $card['rating'], 1 ), number_format_i18n( $card['reviews'] ) ) );
							?>
						</span>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<<?php echo $heading_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted above. ?> class="t360-card__title">
			<a class="t360-card__link" href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['name'] ); ?></a>
		</<?php echo $heading_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<?php do_action( 'woocommerce_shop_loop_item_title' ); ?>

		<div class="t360-price">
			<?php if ( $pricing['html'] ) : ?>
				<span class="t360-price__now"><?php echo wp_kses_post( $pricing['html'] ); ?></span>
			<?php elseif ( $pricing['now'] > 0 ) : ?>
				<span class="t360-price__now<?php echo $pricing['was'] > 0 ? ' is-sale' : ''; ?>">
					<?php if ( $pricing['from'] ) : ?>
						<span class="t360-price__from"><?php esc_html_e( 'From', 'the360hub' ); ?></span>
					<?php endif; ?>
					<?php echo wp_kses_post( wc_price( $pricing['now'] ) ); ?>
				</span>
				<?php if ( $pricing['was'] > 0 ) : ?>
					<span class="t360-price__was">
						<span class="screen-reader-text"><?php esc_html_e( 'Original price:', 'the360hub' ); ?></span>
						<del><?php echo wp_kses_post( wc_price( $pricing['was'] ) ); ?></del>
					</span>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php if ( $pricing['save'] > 0 ) : ?>
			<p class="t360-card__save">
				<?php
				/* translators: %s: amount saved */
				echo wp_kses_post( sprintf( __( 'Save %s', 'the360hub' ), wc_price( $pricing['save'] ) ) );
				?>
			</p>
		<?php endif; ?>

		<?php if ( $card['installment'] ) : ?>
			<p class="t360-card__installment"><?php echo esc_html( $card['installment'] ); ?></p>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>

		<?php if ( $card['stock'] || ( $card['free_ship'] && ! $is_out ) ) : ?>
			<p class="t360-card__flags">
				<?php if ( $card['free_ship'] && ! $is_out ) : ?>
					<span class="t360-card__flag t360-card__flag--ship"><?php t360_the_icon( 'truck' ); ?><?php esc_html_e( 'Free delivery', 'the360hub' ); ?></span>
				<?php endif; ?>
				<?php if ( $card['stock'] && ! $is_out ) : ?>
					<span class="t360-card__flag t360-card__flag--<?php echo esc_attr( $card['stock']['type'] ); ?>"><?php echo esc_html( $card['stock']['text'] ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<?php if ( $card['add_to_cart'] && 'button' === $atc_style ) : ?>
			<div class="t360-card__foot">
				<a
					class="t360-btn t360-btn--cta t360-btn--sm t360-card__atc"
					href="<?php echo esc_url( $card['add_to_cart']['url'] ); ?>"
					<?php if ( $card['add_to_cart']['ajax'] ) : ?>
						data-t360-atc="<?php echo esc_attr( (string) $card['id'] ); ?>"
						rel="nofollow"
						role="button"
					<?php endif; ?>
				>
					<?php t360_the_icon( $card['add_to_cart']['ajax'] ? 'bag' : 'arrow-right' ); ?>
					<?php echo esc_html( $card['add_to_cart']['label'] ); ?>
					<span class="screen-reader-text">
						<?php
						/* translators: %s: product name */
						echo esc_html( sprintf( __( ': %s', 'the360hub' ), $card['name'] ) );
						?>
					</span>
				</a>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
	</div>
</article>
