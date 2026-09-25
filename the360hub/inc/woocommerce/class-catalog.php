<?php
/**
 * Catalogue data helpers: categories, brands, product ID lists.
 *
 * Expensive lookups are cached and invalidated through a single version
 * number that is bumped whenever products or product terms change.
 *
 * @package The360Hub
 */

namespace The360Hub\WooCommerce;

defined( 'ABSPATH' ) || exit;

final class Catalog {

	const VERSION_OPTION = 't360_catalog_version';

	public static function init(): void {
		foreach ( array( 'woocommerce_update_product', 'woocommerce_new_product', 'woocommerce_delete_product', 'woocommerce_trash_product', 'woocommerce_product_set_stock_status', 'woocommerce_variation_set_stock_status' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'bump' ) );
		}
		foreach ( array( 'created_term', 'edited_term', 'delete_term' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'maybe_bump_for_term' ), 10, 3 );
		}
	}

	/**
	 * Invalidate every catalogue cache at once.
	 */
	public static function bump(): void {
		update_option( self::VERSION_OPTION, time(), true );
	}

	public static function maybe_bump_for_term( $term_id, $tt_id, $taxonomy ): void {
		if ( in_array( $taxonomy, array( 'product_cat', 'product_tag', self::brand_taxonomy() ), true ) ) {
			self::bump();
		}
	}

	public static function version(): int {
		return (int) get_option( self::VERSION_OPTION, 1 );
	}

	/**
	 * Cached value tied to the catalogue version. Stored in a transient so it
	 * survives requests even without a persistent object cache.
	 *
	 * @param string   $key      Short cache key.
	 * @param callable $callback Produces the value on a miss.
	 * @param int      $ttl      Seconds.
	 * @return mixed
	 */
	public static function remember( string $key, callable $callback, int $ttl = HOUR_IN_SECONDS ) {
		$transient = 't360_' . $key;
		$cached    = get_transient( $transient );
		$version   = self::version();

		if ( is_array( $cached ) && ( $cached['v'] ?? 0 ) === $version ) {
			return $cached['data'];
		}

		$data = $callback();
		set_transient(
			$transient,
			array(
				'v'    => $version,
				'data' => $data,
			),
			$ttl
		);
		return $data;
	}

	/**
	 * Brand taxonomy: WooCommerce's native product_brand (9.6+), filterable.
	 */
	public static function brand_taxonomy(): string {
		$taxonomy = (string) apply_filters( 'the360hub_brand_taxonomy', 'product_brand' );
		return taxonomy_exists( $taxonomy ) ? $taxonomy : '';
	}

	/**
	 * Brand name for a product (first assigned brand), or empty string.
	 */
	public static function product_brand( int $product_id ): string {
		$taxonomy = self::brand_taxonomy();
		if ( ! $taxonomy ) {
			return '';
		}
		$terms = get_the_terms( $product_id, $taxonomy ); // Uses the object term cache.
		return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	}

	/**
	 * Category tree (two levels) for the menu drawer and category bar.
	 *
	 * @return array<int, array{id:int, name:string, url:string, image:int, children:array}>
	 */
	public static function category_tree(): array {
		return self::remember(
			'cat_tree_v2',
			static function () {
				$terms = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => true,
						'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
						'menu_order' => 'ASC', // WooCommerce's drag-and-drop category order.
					)
				);
				if ( is_wp_error( $terms ) ) {
					return array();
				}

				$by_parent = array();
				foreach ( $terms as $term ) {
					$by_parent[ $term->parent ][] = $term;
				}

				$build = static function ( $term ) {
					return array(
						'id'    => (int) $term->term_id,
						'slug'  => $term->slug,
						'name'  => $term->name,
						'url'   => get_term_link( $term ),
						'image' => (int) get_term_meta( $term->term_id, 'thumbnail_id', true ),
						'count' => (int) $term->count,
					);
				};

				$tree = array();
				foreach ( $by_parent[0] ?? array() as $top ) {
					$node             = $build( $top );
					$node['children'] = array_map( $build, $by_parent[ $top->term_id ] ?? array() );
					$tree[]           = $node;
				}
				return $tree;
			}
		);
	}

	/**
	 * Brands with products, ordered by product count.
	 *
	 * @return array<int, array{id:int, name:string, url:string, image:int}>
	 */
	public static function brands( int $limit = 16 ): array {
		$taxonomy = self::brand_taxonomy();
		if ( ! $taxonomy ) {
			return array();
		}
		return self::remember(
			'brands_' . $limit,
			static function () use ( $taxonomy, $limit ) {
				$terms = get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'hide_empty' => true,
						'number'     => $limit,
						'orderby'    => 'count',
						'order'      => 'DESC',
					)
				);
				if ( is_wp_error( $terms ) ) {
					return array();
				}
				return array_map(
					static function ( $term ) {
						return array(
							'id'    => (int) $term->term_id,
							'name'  => $term->name,
							'url'   => get_term_link( $term ),
							'image' => (int) get_term_meta( $term->term_id, 'thumbnail_id', true ),
						);
					},
					$terms
				);
			}
		);
	}

	/**
	 * Product IDs for a merchandising list.
	 *
	 * @param string $type     deals | featured | bestsellers (alias trending) | top_rated | new.
	 * @param int    $limit    Max products.
	 * @param int    $category Optional product_cat term ID to restrict to (includes children).
	 * @return int[]
	 */
	public static function product_ids( string $type, int $limit, int $category = 0 ): array {
		return self::remember(
			"ids_{$type}_{$limit}_{$category}",
			static function () use ( $type, $limit, $category ) {
				$args = array(
					'status'     => 'publish',
					'visibility' => 'catalog',
					'limit'      => $limit,
					'return'     => 'ids',
					'order'      => 'DESC',
				);
				if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
					$args['stock_status'] = 'instock';
				}
				if ( $category ) {
					$term = get_term( $category, 'product_cat' );
					if ( ! $term || is_wp_error( $term ) ) {
						return array();
					}
					$args['category'] = array( $term->slug );
				}

				switch ( $type ) {
					case 'deals':
						$on_sale = wc_get_product_ids_on_sale();
						if ( ! $on_sale ) {
							return array();
						}
						$args['include'] = $on_sale;
						$args['orderby'] = 'date';
						break;
					case 'featured':
						$args['featured'] = true;
						$args['orderby']  = 'date';
						break;
					case 'bestsellers':
					case 'trending':
						$args['orderby']  = 'meta_value_num';
						$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- cached.
						break;
					case 'top_rated':
						$args['orderby']  = 'meta_value_num';
						$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- cached.
						break;
					default: // new.
						$args['orderby'] = 'date';
				}

				return array_map( 'intval', wc_get_products( $args ) );
			},
			15 * MINUTE_IN_SECONDS
		);
	}

	/**
	 * Top-level category by term ID, or the Nth top-level category when 0.
	 *
	 * @param int $term_id  Chosen term (0 = automatic).
	 * @param int $fallback Zero-based index into the category tree.
	 * @return array|null Category node from category_tree().
	 */
	public static function category_node( int $term_id, int $fallback ): ?array {
		$tree = self::category_tree();
		if ( $term_id ) {
			foreach ( $tree as $node ) {
				if ( $node['id'] === $term_id ) {
					return $node;
				}
				foreach ( $node['children'] as $child ) {
					if ( $child['id'] === $term_id ) {
						return $child + array( 'children' => array() );
					}
				}
			}
			$term = get_term( $term_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				return array(
					'id'       => (int) $term->term_id,
					'name'     => $term->name,
					'url'      => get_term_link( $term ),
					'image'    => (int) get_term_meta( $term->term_id, 'thumbnail_id', true ),
					'count'    => (int) $term->count,
					'children' => array(),
				);
			}
			return null;
		}
		// Largest categories first for automatic picks.
		usort( $tree, static fn( $a, $b ) => $b['count'] <=> $a['count'] );
		return $tree[ $fallback ] ?? null;
	}
}
