<?php
/**
 * Customizer UI generated from Settings::schema().
 *
 * Adds a few custom controls (reorderable section list, range slider,
 * product-category picker, date/time) and live preview for colours and
 * layout width. All Customizer scripts load in the Customizer only.
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

use WP_Customize_Color_Control;
use WP_Customize_Manager;
use WP_Customize_Media_Control;

defined( 'ABSPATH' ) || exit;

final class Customizer {

	/** Settings previewed live via postMessage (CSS variables only). */
	const LIVE = array( 'color_preset', 'color_brand', 'color_cta', 'color_deal', 'color_highlight', 'color_header', 'color_topbar', 'color_footer', 'color_page', 'style_container' );

	public static function init(): void {
		add_action( 'customize_register', array( __CLASS__, 'register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'controls_assets' ) );
		add_action( 'customize_preview_init', array( __CLASS__, 'preview_assets' ) );
		// The theme has no widget areas. Core still prints the "Add a Widget"
		// template, which reads the missing widgets panel and logs a warning.
		add_action( 'customize_controls_init', array( __CLASS__, 'skip_widget_templates' ) );
	}

	public static function skip_widget_templates(): void {
		global $wp_customize;
		if ( $wp_customize && $wp_customize->widgets && ! $wp_customize->get_panel( 'widgets' ) ) {
			remove_action( 'customize_controls_print_footer_scripts', array( $wp_customize->widgets, 'output_widget_control_templates' ) );
		}
	}

	/**
	 * Panels → sections, in order.
	 */
	private static function structure(): array {
		return array(
			'the360hub_design'   => array(
				__( 'The360Hub: Design', 'the360hub' ),
				array(
					'colors'     => __( 'Colours', 'the360hub' ),
					'style'      => __( 'Corners, buttons & shadows', 'the360hub' ),
					'typography' => __( 'Typography', 'the360hub' ),
					'cards'      => __( 'Product cards', 'the360hub' ),
					'catalog'    => __( 'Shop & category pages', 'the360hub' ),
				),
			),
			'the360hub_layout'   => array(
				__( 'The360Hub: Header, footer & navigation', 'the360hub' ),
				array(
					'topbar'     => __( 'Top bar', 'the360hub' ),
					'header'     => __( 'Header', 'the360hub' ),
					'mobile_nav' => __( 'Mobile navigation & WhatsApp', 'the360hub' ),
					'search'     => __( 'Search', 'the360hub' ),
					'footer'     => __( 'Footer & social', 'the360hub' ),
				),
			),
			'the360hub_home'     => array(
				__( 'The360Hub: Homepage', 'the360hub' ),
				array(
					'home'           => __( 'Sections & order', 'the360hub' ),
					'home_hero'      => __( 'Hero slider', 'the360hub' ),
					'home_tiles'     => __( 'Promo tiles', 'the360hub' ),
					'home_flash'     => __( 'Flash deals', 'the360hub' ),
					'home_tabs'      => __( 'Product tabs', 'the360hub' ),
					'home_spotlight' => __( 'Category spotlights', 'the360hub' ),
					'home_cta'       => __( 'Call-to-action banner', 'the360hub' ),
					'home_trust'     => __( 'Trust & services', 'the360hub' ),
					'home_titles'    => __( 'Other section titles', 'the360hub' ),
				),
			),
			'the360hub_advanced' => array(
				__( 'The360Hub: Performance', 'the360hub' ),
				array(
					'performance' => __( 'Performance', 'the360hub' ),
				),
			),
		);
	}

	public static function register( WP_Customize_Manager $wp_customize ): void {
		// Custom controls extend WP_Customize_Control, which only exists here.
		require_once THE360HUB_DIR . '/inc/core/class-sortable-control.php';
		require_once THE360HUB_DIR . '/inc/core/class-range-control.php';

		$section_of = array();
		$priority   = 25;
		foreach ( self::structure() as $panel_id => [ $panel_title, $sections ] ) {
			$wp_customize->add_panel(
				$panel_id,
				array(
					'title'    => $panel_title,
					'priority' => $priority++,
				)
			);
			$order = 10;
			foreach ( $sections as $id => $title ) {
				$wp_customize->add_section(
					'the360hub_' . $id,
					array(
						'title'    => $title,
						'panel'    => $panel_id,
						'priority' => $order,
					)
				);
				$order += 10;
			}
		}

		foreach ( Settings::schema() as $key => $def ) {
			$id = Settings::PREFIX . $key;

			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $def['default'],
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'transport'         => in_array( $key, self::LIVE, true ) ? 'postMessage' : 'refresh',
					'sanitize_callback' => static function ( $value ) use ( $key ) {
						return Settings::sanitize( $value, $key );
					},
				)
			);

			$args = array(
				'label'       => $def['label'],
				'description' => $def['description'],
				'section'     => 'the360hub_' . $def['section'],
				'settings'    => $id,
			);

			switch ( $def['type'] ) {
				case 'color':
					$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $args ) );
					break;
				case 'image':
					$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $id, $args + array( 'mime_type' => 'image' ) ) );
					break;
				case 'sortable':
					$wp_customize->add_control(
						new Sortable_Control(
							$wp_customize,
							$id,
							$args + array( 'choices' => \The360Hub\Home\Sections::labels() )
						)
					);
					break;
				case 'range':
					[ $min, $max, $step ] = $def['range'];
					$wp_customize->add_control(
						new Range_Control(
							$wp_customize,
							$id,
							$args + array(
								'input_attrs' => array(
									'min'  => $min,
									'max'  => $max,
									'step' => $step,
								),
							)
						)
					);
					break;
				case 'category':
					$wp_customize->add_control(
						$id,
						$args + array(
							'type'    => 'select',
							'choices' => self::category_choices(),
						)
					);
					break;
				case 'datetime':
					$wp_customize->add_control( $id, $args + array( 'type' => 'datetime-local' ) );
					break;
				case 'page':
					$wp_customize->add_control(
						$id,
						$args + array(
							'type'           => 'dropdown-pages',
							'allow_addition' => true,
						)
					);
					break;
				case 'select':
					$wp_customize->add_control(
						$id,
						$args + array(
							'type'    => 'select',
							'choices' => $def['choices'],
						)
					);
					break;
				case 'number':
					$wp_customize->add_control(
						$id,
						$args + array(
							'type'        => 'number',
							'input_attrs' => array( 'min' => 0 ),
						)
					);
					break;
				default:
					$wp_customize->add_control( $id, $args + array( 'type' => $def['type'] ) );
			}
		}
	}

	/**
	 * Product categories for dropdowns (indented by level).
	 */
	private static function category_choices(): array {
		static $choices = null;
		if ( null !== $choices ) {
			return $choices;
		}
		$choices = array( 0 => __( '— All / automatic —', 'the360hub' ) );
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return $choices;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) ) {
			return $choices;
		}
		$walk = static function ( $parent_id, $depth ) use ( &$walk, $terms, &$choices ) {
			foreach ( $terms as $term ) {
				if ( (int) $term->parent === $parent_id ) {
					$choices[ $term->term_id ] = str_repeat( '— ', $depth ) . $term->name;
					$walk( (int) $term->term_id, $depth + 1 );
				}
			}
		};
		$walk( 0, 0 );
		return $choices;
	}

	public static function controls_assets(): void {
		wp_enqueue_script( 't360-customizer-controls', THE360HUB_URI . '/assets/admin/customizer-controls.js', array( 'customize-controls' ), THE360HUB_VERSION, true );
		wp_enqueue_style( 't360-customizer-controls', THE360HUB_URI . '/assets/admin/customizer-controls.css', array(), THE360HUB_VERSION );
	}

	public static function preview_assets(): void {
		wp_enqueue_script( 't360-customizer-preview', THE360HUB_URI . '/assets/admin/customizer-preview.js', array( 'customize-preview' ), THE360HUB_VERSION, true );
		$presets = array();
		foreach ( Settings::presets() as $key => $preset ) {
			$presets[ $key ] = $preset['colors'];
		}
		wp_add_inline_script(
			't360-customizer-preview',
			'window.t360Presets=' . wp_json_encode(
				array(
					'presets' => $presets,
					'prefix'  => Settings::PREFIX,
					'header'  => (string) Settings::get( 'header_style' ),
				)
			) . ';',
			'before'
		);
	}
}
