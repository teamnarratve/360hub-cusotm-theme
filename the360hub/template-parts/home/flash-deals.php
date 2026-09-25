<?php
/**
 * Flash deals: dark band with a countdown and a row of on-sale products.
 *
 * The countdown end is computed on the server (daily = next midnight in the
 * site timezone, or a fixed date) and ticked by home.js. With a fixed date
 * that has passed, the section hides itself.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

use The360Hub\WooCommerce\Catalog;
use The360Hub\WooCommerce\Product_Card;

if ( ! t360_has_wc() ) {
	return;
}

$ids = Catalog::product_ids( 'deals', (int) t360_setting( 'home_products_count' ), (int) t360_setting( 'flash_category' ) );
if ( ! $ids ) {
	return;
}

$timer_mode = (string) t360_setting( 'flash_timer' );
$end        = 0;
if ( 'daily' === $timer_mode ) {
	$end = ( new DateTimeImmutable( 'tomorrow', wp_timezone() ) )->getTimestamp();
} elseif ( 'date' === $timer_mode && t360_setting( 'flash_end' ) ) {
	$date = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', (string) t360_setting( 'flash_end' ), wp_timezone() );
	$end  = $date ? $date->getTimestamp() : 0;
	if ( $end && $end <= time() ) {
		return; // Campaign over.
	}
}

$left        = max( 0, $end - time() );
$heading_id  = 't360-sec-' . $args['key'];
$units       = array(
	'd' => array( (int) floor( $left / DAY_IN_SECONDS ), __( 'days', 'the360hub' ) ),
	'h' => array( (int) floor( $left % DAY_IN_SECONDS / HOUR_IN_SECONDS ), __( 'hrs', 'the360hub' ) ),
	'm' => array( (int) floor( $left % HOUR_IN_SECONDS / MINUTE_IN_SECONDS ), __( 'min', 'the360hub' ) ),
	's' => array( $left % MINUTE_IN_SECONDS, __( 'sec', 'the360hub' ) ),
);
$flash_title = (string) t360_setting( 'flash_title' );
?>
<section class="t360-section t360-flash" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="t360-container">
		<div class="t360-flash__panel">
			<div class="t360-flash__head">
				<div class="t360-flash__titles">
					<h2 class="t360-flash__title" id="<?php echo esc_attr( $heading_id ); ?>">
						<?php t360_the_icon( 'zap', 't360-flash__zap' ); ?>
						<?php echo esc_html( $flash_title ); ?>
					</h2>
					<?php if ( t360_setting( 'flash_subtitle' ) ) : ?>
						<p class="t360-flash__sub"><?php echo esc_html( (string) t360_setting( 'flash_subtitle' ) ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $end ) : ?>
					<div class="t360-countdown" data-t360-countdown="<?php echo esc_attr( (string) $end ); ?>" role="timer" aria-label="<?php esc_attr_e( 'Time left', 'the360hub' ); ?>">
						<span class="t360-countdown__label"><?php esc_html_e( 'Ends in', 'the360hub' ); ?></span>
						<?php foreach ( $units as $unit => [ $value, $label ] ) : ?>
							<span class="t360-countdown__box" <?php echo 'd' === $unit && 0 === $value ? 'hidden' : ''; ?>>
								<b data-unit="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( str_pad( (string) $value, 2, '0', STR_PAD_LEFT ) ); ?></b>
								<small><?php echo esc_html( $label ); ?></small>
							</span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<a class="t360-flash__more" href="<?php echo esc_url( add_query_arg( 'deals', 1, get_permalink( wc_get_page_id( 'shop' ) ) ) ); ?>">
					<?php esc_html_e( 'View all', 'the360hub' ); ?>
					<span class="screen-reader-text"><?php echo esc_html( $flash_title ); ?></span>
					<?php t360_the_icon( 'arrow-right' ); ?>
				</a>
			</div>
			<ul class="t360-rail">
				<?php Product_Card::render_many( $ids, array( 'heading_tag' => 'h3' ) ); ?>
			</ul>
		</div>
	</div>
</section>
