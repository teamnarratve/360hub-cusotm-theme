<?php
/**
 * Homepage module registry.
 *
 * Each module is a template part in template-parts/home/. The order and
 * visibility come from the "home_order" setting, so the homepage can be
 * re-arranged in the Customizer without touching templates.
 *
 * @package The360Hub
 */

namespace The360Hub\Home;

defined( 'ABSPATH' ) || exit;

final class Sections {

	/**
	 * Module key → [template part, args].
	 */
	public static function registry(): array {
		$modules = array(
			'hero'             => array( 'hero', array() ),
			'quick_categories' => array( 'quick-categories', array() ),
			'hot_deals'        => array(
				'products',
				array(
					'type'      => 'deals',
					'title_key' => 'deals_title',
					'view_all'  => array( 'deals' => 1 ),
				),
			),
			'featured'         => array(
				'products',
				array(
					'type'      => 'featured',
					'title_key' => 'featured_title',
					'view_all'  => array( 'featured' => 1 ),
				),
			),
			'promo_banners'    => array( 'promo-banners', array() ),
			'new_arrivals'     => array(
				'products',
				array(
					'type'      => 'new',
					'title_key' => 'new_title',
					'view_all'  => array( 'orderby' => 'date' ),
				),
			),
			'category_grid'    => array( 'category-grid', array() ),
			'trending'         => array(
				'products',
				array(
					'type'      => 'trending',
					'title_key' => 'trending_title',
					'view_all'  => array( 'orderby' => 'popularity' ),
				),
			),
			'brands'           => array( 'brands', array() ),
			'trust'            => array( 'trust', array() ),
			'recently_viewed'  => array( 'recently-viewed', array() ),
		);

		/**
		 * Filters the available homepage modules.
		 *
		 * @param array $modules key => [template slug in template-parts/home, args].
		 */
		return apply_filters( 'the360hub_home_sections', $modules );
	}

	/**
	 * Enabled module keys in display order.
	 *
	 * @return string[]
	 */
	public static function order(): array {
		$registry = self::registry();
		$keys     = array_map( 'trim', explode( ',', (string) t360_setting( 'home_order' ) ) );
		return array_values( array_unique( array_filter( $keys, static fn( $k ) => isset( $registry[ $k ] ) ) ) );
	}

	/**
	 * Render every enabled module.
	 */
	public static function render(): void {
		$registry = self::registry();
		$index    = 0;
		foreach ( self::order() as $key ) {
			[ $slug, $args ]  = $registry[ $key ];
			$args['key']      = $key;
			$args['position'] = $index++; // Lets the first module load its media eagerly.
			get_template_part( 'template-parts/home/' . $slug, null, $args );
		}
	}
}
