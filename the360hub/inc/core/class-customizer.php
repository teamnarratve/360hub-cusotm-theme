<?php
/**
 * Customizer UI generated from Settings::schema().
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

use WP_Customize_Color_Control;
use WP_Customize_Manager;
use WP_Customize_Media_Control;

defined( 'ABSPATH' ) || exit;

final class Customizer {

	public static function init(): void {
		add_action( 'customize_register', array( __CLASS__, 'register' ) );
	}

	/**
	 * Section keys → titles, in panel order.
	 */
	private static function sections(): array {
		return array(
			'brand'         => __( 'Brand & colors', 'the360hub' ),
			'typography'    => __( 'Typography', 'the360hub' ),
			'header'        => __( 'Header', 'the360hub' ),
			'mobile_nav'    => __( 'Mobile navigation', 'the360hub' ),
			'search'        => __( 'Search', 'the360hub' ),
			'catalog'       => __( 'Product cards & catalogue', 'the360hub' ),
			'home'          => __( 'Homepage: layout', 'the360hub' ),
			'home_hero'     => __( 'Homepage: hero banners', 'the360hub' ),
			'home_products' => __( 'Homepage: section titles', 'the360hub' ),
			'home_promos'   => __( 'Homepage: promotional banners', 'the360hub' ),
			'home_trust'    => __( 'Homepage: trust & services', 'the360hub' ),
			'footer'        => __( 'Footer', 'the360hub' ),
			'performance'   => __( 'Performance', 'the360hub' ),
		);
	}

	public static function register( WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_panel(
			'the360hub',
			array(
				'title'    => __( 'The360Hub theme', 'the360hub' ),
				'priority' => 30,
			)
		);

		$priority = 10;
		foreach ( self::sections() as $id => $title ) {
			$wp_customize->add_section(
				'the360hub_' . $id,
				array(
					'title'    => $title,
					'panel'    => 'the360hub',
					'priority' => $priority,
				)
			);
			$priority += 10;
		}

		foreach ( Settings::schema() as $key => $def ) {
			$id = Settings::PREFIX . $key;

			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $def['default'],
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'transport'         => 'refresh',
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
							'input_attrs' => array(
								'min' => 1,
								'max' => 100,
							),
						)
					);
					break;
				default:
					$wp_customize->add_control( $id, $args + array( 'type' => $def['type'] ) );
			}
		}
	}
}
