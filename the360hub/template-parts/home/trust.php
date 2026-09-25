<?php
/**
 * Trust / service promises.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$icons = array(
	1 => 'truck',
	2 => 'shield',
	3 => 'returns',
	4 => 'card',
);
$items = array();
foreach ( $icons as $i => $icon ) {
	$item_title = (string) t360_setting( "trust_{$i}_title" );
	if ( '' !== $item_title ) {
		$items[] = array(
			'icon'  => $icon,
			'title' => $item_title,
			'text'  => (string) t360_setting( "trust_{$i}_text" ),
		);
	}
}
if ( ! $items ) {
	return;
}
?>
<section class="t360-section" aria-label="<?php esc_attr_e( 'Why shop with us', 'the360hub' ); ?>">
	<div class="t360-container">
		<ul class="t360-trust">
			<?php foreach ( $items as $item ) : ?>
				<li class="t360-trust__item">
					<?php t360_the_icon( $item['icon'], 't360-trust__icon' ); ?>
					<p>
						<strong><?php echo esc_html( $item['title'] ); ?></strong>
						<?php if ( $item['text'] ) : ?>
							<span><?php echo esc_html( $item['text'] ); ?></span>
						<?php endif; ?>
					</p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
