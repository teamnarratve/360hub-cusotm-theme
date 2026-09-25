<?php
/**
 * Product card.
 *
 * The product title link is stretched over the whole card (one link per
 * product for screen readers); the wishlist and add-to-cart buttons sit
 * above it. WooCommerce's loop actions are still fired so plugins can add
 * to cards; the theme has removed only WooCommerce's default callbacks.
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
$sizes       = '(min-width: 1320px) 250px, (min-width: 1280px) 19vw, (min-width: 1024px) 23vw, (min-width: 640px) 31vw, 47vw';
$out         = $card['stock'] && 'out' === $card['stock']['type'];
?>
<article class="t360-card<?php echo $out ? ' is-out' : ''; ?>">
	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

	<div class="t360-card__media">
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

		<?php if ( $pricing['percent'] > 0 ) : ?>
			<span class="t360-card__badge">
				<?php
				echo esc_html(
					$pricing['up_to']
						/* translators: %d: discount percent */
						? sprintf( __( 'Up to -%d%%', 'the360hub' ), $pricing['percent'] )
						/* translators: %d: discount percent */
						: sprintf( __( '-%d%%', 'the360hub' ), $pricing['percent'] )
				);
				?>
			</span>
		<?php endif; ?>

		<button type="button" class="t360-card__wish" data-t360-wish="<?php echo esc_attr( (string) $card['id'] ); ?>" aria-pressed="false" data-t360-requires-js>
			<?php t360_the_icon( 'heart' ); ?>
			<span class="screen-reader-text">
				<?php
				/* translators: %s: product name */
				echo esc_html( sprintf( __( 'Save %s to wishlist', 'the360hub' ), $card['name'] ) );
				?>
			</span>
		</button>

		<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
	</div>

	<div class="t360-card__body">
		<?php if ( $card['brand'] ) : ?>
			<p class="t360-card__brand"><?php echo esc_html( $card['brand'] ); ?></p>
		<?php endif; ?>

		<<?php echo $heading_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted above. ?> class="t360-card__title">
			<a class="t360-card__link" href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['name'] ); ?></a>
		</<?php echo $heading_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<?php do_action( 'woocommerce_shop_loop_item_title' ); ?>

		<?php if ( $card['reviews'] > 0 && wc_review_ratings_enabled() ) : ?>
			<p class="t360-card__rating">
				<?php t360_the_icon( 'star', 't360-star' ); ?>
				<span aria-hidden="true"><?php echo esc_html( number_format_i18n( $card['rating'], 1 ) ); ?></span>
				<span class="t360-card__reviews" aria-hidden="true">(<?php echo esc_html( number_format_i18n( $card['reviews'] ) ); ?>)</span>
				<span class="screen-reader-text">
					<?php
					/* translators: 1: average rating, 2: review count */
					echo esc_html( sprintf( _n( 'Rated %1$s out of 5 from %2$s review', 'Rated %1$s out of 5 from %2$s reviews', $card['reviews'], 'the360hub' ), number_format_i18n( $card['rating'], 1 ), number_format_i18n( $card['reviews'] ) ) );
					?>
				</span>
			</p>
		<?php endif; ?>

		<div class="t360-price">
			<?php if ( $pricing['html'] ) : ?>
				<span class="t360-price__now"><?php echo wp_kses_post( $pricing['html'] ); ?></span>
			<?php elseif ( $pricing['now'] > 0 ) : ?>
				<span class="t360-price__now">
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
					<span class="t360-price__save">
						<?php
						/* translators: %s: amount saved */
						echo wp_kses_post( sprintf( __( 'Save %s', 'the360hub' ), wc_price( $pricing['save'] ) ) );
						?>
					</span>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>

		<?php if ( $card['stock'] ) : ?>
			<p class="t360-card__stock t360-card__stock--<?php echo esc_attr( $card['stock']['type'] ); ?>"><?php echo esc_html( $card['stock']['text'] ); ?></p>
		<?php endif; ?>

		<?php if ( $card['add_to_cart'] ) : ?>
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
