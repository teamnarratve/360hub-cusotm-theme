<?php
/**
 * Homepage module registry.
 *
 * Each module is a template part in template-parts/home/. Which modules show,
 * and in what order, comes from the "Homepage sections" Customizer control.
 *
 * @package The360Hub
 */

namespace The360Hub\Home;

defined( 'ABSPATH' ) || exit;

final class Sections {

	/**
	 * Module key → [label, template slug, args].
	 */
	public static function registry(): array {
		$rail = static fn( string $type, string $title_key, array $view_all ) => array(
			'type'      => $type,
			'title_key' => $title_key,
			'view_all'  => $view_all,
		);

		$modules = array(
			'hero'            => array( __( 'Hero slider', 'the360hub' ), 'hero', array() ),
			'categories'      => array( __( 'Category icons', 'the360hub' ), 'categories', array() ),
			'flash_deals'     => array( __( 'Flash deals with countdown', 'the360hub' ), 'flash-deals', array() ),
			'bento'           => array( __( 'Promo tiles (bento grid)', 'the360hub' ), 'bento', array() ),
			'product_tabs'    => array( __( 'Product tabs', 'the360hub' ), 'product-tabs', array() ),
			'spotlight_1'     => array( __( 'Category spotlight 1', 'the360hub' ), 'spotlight', array( 'index' => 1 ) ),
			'spotlight_2'     => array( __( 'Category spotlight 2', 'the360hub' ), 'spotlight', array( 'index' => 2 ) ),
			'brands'          => array( __( 'Brands', 'the360hub' ), 'brands', array() ),
			'new_arrivals'    => array( __( 'New arrivals', 'the360hub' ), 'products', $rail( 'new', 'new_title', array( 'orderby' => 'date' ) ) ),
			'hot_deals'       => array( __( 'Hot deals row', 'the360hub' ), 'products', $rail( 'deals', 'deals_title', array( 'deals' => 1 ) ) ),
			'featured'        => array( __( 'Featured row', 'the360hub' ), 'products', $rail( 'featured', 'featured_title', array( 'featured' => 1 ) ) ),
			'trending'        => array( __( 'Best sellers row', 'the360hub' ), 'products', $rail( 'bestsellers', 'trending_title', array( 'orderby' => 'popularity' ) ) ),
			'top_rated'       => array( __( 'Top rated row', 'the360hub' ), 'products', $rail( 'top_rated', 'top_rated_title', array( 'orderby' => 'rating' ) ) ),
			'cta_banner'      => array( __( 'Call-to-action banner', 'the360hub' ), 'cta-banner', array() ),
			'trust'           => array( __( 'Trust & services', 'the360hub' ), 'trust', array() ),
			'recently_viewed' => array( __( 'Recently viewed', 'the360hub' ), 'recently-viewed', array() ),
		);

		/**
		 * Filters the available homepage modules.
		 *
		 * @param array $modules key => [label, template slug in template-parts/home, args].
		 */
		return apply_filters( 'the360hub_home_sections', $modules );
	}

	/**
	 * Module labels for the Customizer.
	 *
	 * @return array<string, string>
	 */
	public static function labels(): array {
		return array_map( static fn( $module ) => $module[0], self::registry() );
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
			[ , $slug, $args ] = $registry[ $key ];
			$args['key']       = $key;
			$args['position']  = $index++; // Lets the first module load its media eagerly.
			get_template_part( 'template-parts/home/' . $slug, null, $args );
		}
	}
}
