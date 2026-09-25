<?php
/**
 * Customizer control: reorderable checkbox list.
 *
 * Loaded only inside customize_register, where WP_Customize_Control exists.
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

use WP_Customize_Control;

defined( 'ABSPATH' ) || exit;

/**
 * Checkbox list with up/down buttons. Stores enabled keys, in order, as CSV.
 * Buttons instead of drag-and-drop keep it keyboard- and touch-friendly.
 */
class Sortable_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 't360-sortable';

	public function render_content() {
		$enabled = array_filter( explode( ',', (string) $this->value() ) );
		$all     = array_keys( $this->choices );
		// Enabled first (in saved order), then the rest.
		$ordered = array_merge( array_values( array_intersect( $enabled, $all ) ), array_values( array_diff( $all, $enabled ) ) );
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>
		<ul class="t360-sortable" data-t360-sortable>
			<?php foreach ( $ordered as $key ) : ?>
				<li class="t360-sortable__item" data-key="<?php echo esc_attr( $key ); ?>">
					<label>
						<input type="checkbox" <?php checked( in_array( $key, $enabled, true ) ); ?>>
						<?php echo esc_html( $this->choices[ $key ] ); ?>
					</label>
					<span class="t360-sortable__btns">
						<button type="button" class="button-link" data-dir="-1" aria-label="<?php /* translators: %s: section name */ echo esc_attr( sprintf( __( 'Move %s up', 'the360hub' ), $this->choices[ $key ] ) ); ?>">▲</button>
						<button type="button" class="button-link" data-dir="1" aria-label="<?php /* translators: %s: section name */ echo esc_attr( sprintf( __( 'Move %s down', 'the360hub' ), $this->choices[ $key ] ) ); ?>">▼</button>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
		<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( (string) $this->value() ); ?>">
		<?php
	}
}
