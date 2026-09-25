<?php
/**
 * Customizer control: range slider with value readout.
 *
 * Loaded only inside customize_register, where WP_Customize_Control exists.
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

use WP_Customize_Control;

defined( 'ABSPATH' ) || exit;

/**
 * Range slider with a live value readout.
 */
class Range_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 't360-range';

	public function render_content() {
		?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<span class="t360-range">
				<input type="range" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( (string) $this->value() ); ?>" <?php $this->link(); ?>>
				<output><?php echo esc_html( (string) $this->value() ); ?></output>
			</span>
		</label>
		<?php
	}
}
