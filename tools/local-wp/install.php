<?php
/**
 * Installs the local test site and seeds a sample electronics catalogue.
 *
 *   php install.php <site-dir> core   → WordPress install, activate plugins + theme
 *   php install.php <site-dir> store  → WooCommerce install, settings, sample data
 *
 * Local development only. Product names and prices are fictional samples.
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

[ , $site, $phase ] = $argv + array( null, null, 'core' );
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/';

if ( 'core' === $phase ) {
	define( 'WP_INSTALLING', true );
	require $site . '/wp-load.php';
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	wp_install( 'The360Hub', 'admin', 'admin@example.com', true, '', 'admin' );
	update_option( 'active_plugins', array( 'sqlite-database-integration/load.php', 'woocommerce/woocommerce.php' ) );
	update_option( 'template', 'the360hub' );
	update_option( 'stylesheet', 'the360hub' );
	update_option( 'permalink_structure', '/%postname%/' );
	echo "core: installed\n";
	exit;
}

// Store phase: full load with plugins active.
require $site . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

WC_Install::install();
WC_Install::create_pages();
do_action( 'after_switch_theme', 'The360Hub', wp_get_theme() );

foreach ( array(
	'woocommerce_currency'                  => 'AED',
	'woocommerce_default_country'           => 'AE:DU',
	'woocommerce_price_num_decimals'        => '0',
	'woocommerce_price_thousand_sep'        => ',',
	'woocommerce_currency_pos'              => 'left_space',
	'woocommerce_enable_reviews'            => 'yes',
	'woocommerce_enable_review_rating'      => 'yes',
	'woocommerce_manage_stock'              => 'yes',
	'woocommerce_notify_low_stock_amount'   => '3',
	'woocommerce_coming_soon'               => 'no',
	'woocommerce_onboarding_profile'        => array( 'completed' => true ),
	'woocommerce_task_list_hidden'          => 'yes',
	'show_on_front'                         => 'posts',
) as $key => $value ) {
	update_option( $key, $value );
}
flush_rewrite_rules();

/**
 * Generate a simple product-style image with GD and add it to the library.
 * Shapes are abstract (no real product imagery).
 */
function t360_seed_image( string $label, string $shape, array $rgb ): int {
	$size = 1000;
	$im   = imagecreatetruecolor( $size, $size );
	imagefill( $im, 0, 0, imagecolorallocate( $im, 255, 255, 255 ) );
	$fill  = imagecolorallocate( $im, ...$rgb );
	$dark  = imagecolorallocate( $im, 30, 36, 48 );
	$glass = imagecolorallocate( $im, 225, 232, 240 );

	switch ( $shape ) {
		case 'phone':
			imagefilledrectangle( $im, 330, 120, 670, 880, $dark );
			imagefilledrectangle( $im, 345, 140, 655, 860, $fill );
			break;
		case 'laptop':
			imagefilledrectangle( $im, 200, 260, 800, 640, $dark );
			imagefilledrectangle( $im, 220, 280, 780, 620, $fill );
			imagefilledrectangle( $im, 140, 650, 860, 700, $glass );
			break;
		case 'tv':
			imagefilledrectangle( $im, 110, 230, 890, 700, $dark );
			imagefilledrectangle( $im, 125, 245, 875, 685, $fill );
			imagefilledrectangle( $im, 450, 700, 550, 770, $dark );
			break;
		case 'headphones':
			imagesetthickness( $im, 40 );
			imagearc( $im, 500, 500, 520, 560, 180, 360, $dark );
			imagefilledellipse( $im, 250, 560, 150, 240, $fill );
			imagefilledellipse( $im, 750, 560, 150, 240, $fill );
			break;
		case 'console':
			imagefilledellipse( $im, 500, 520, 720, 380, $fill );
			imagefilledellipse( $im, 360, 500, 90, 90, $dark );
			imagefilledellipse( $im, 640, 500, 90, 90, $dark );
			break;
		case 'watch':
			imagefilledrectangle( $im, 420, 160, 580, 840, $dark );
			imagefilledrectangle( $im, 360, 330, 640, 670, $fill );
			break;
		default: // speaker / accessory.
			imagefilledellipse( $im, 500, 500, 480, 480, $fill );
			imagefilledellipse( $im, 500, 500, 180, 180, $dark );
	}

	$upload = wp_upload_dir();
	$file   = trailingslashit( $upload['path'] ) . sanitize_file_name( $label ) . '.jpg';
	imagejpeg( $im, $file, 88 );
	imagedestroy( $im );

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $label,
			'post_status'    => 'inherit',
		),
		$file
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	return (int) $id;
}

function t360_seed_term( string $name, string $taxonomy, int $parent = 0, int $image = 0 ): int {
	$existing = term_exists( $name, $taxonomy, $parent );
	$term     = $existing ?: wp_insert_term( $name, $taxonomy, array( 'parent' => $parent ) );
	$id       = (int) $term['term_id'];
	if ( $image ) {
		update_term_meta( $id, 'thumbnail_id', $image );
	}
	return $id;
}

// Categories (with generated tile images).
$cats = array(
	'Mobiles & Tablets' => array( 'phone', array( 29, 78, 216 ), array( 'Smartphones', 'Tablets' ) ),
	'Laptops'           => array( 'laptop', array( 71, 84, 103 ), array( 'Gaming Laptops', 'Ultrabooks' ) ),
	'TV & Audio'        => array( 'tv', array( 11, 18, 32 ), array( 'Televisions', 'Headphones', 'Speakers' ) ),
	'Gaming'            => array( 'console', array( 102, 112, 133 ), array( 'Consoles', 'Controllers' ) ),
	'Wearables'         => array( 'watch', array( 6, 118, 71 ), array( 'Smart Watches' ) ),
	'Smart Home'        => array( 'speaker', array( 181, 71, 8 ), array() ),
	'Accessories'       => array( 'speaker', array( 200, 16, 46 ), array( 'Chargers', 'Power Banks' ) ),
);
$cat_ids = array();
foreach ( $cats as $name => [ $shape, $rgb, $children ] ) {
	$parent            = t360_seed_term( $name, 'product_cat', 0, t360_seed_image( "cat-$name", $shape, $rgb ) );
	$cat_ids[ $name ] = $parent;
	foreach ( $children as $child ) {
		$cat_ids[ $child ] = t360_seed_term( $child, 'product_cat', $parent );
	}
}

// Brands (WooCommerce 9.6+ native taxonomy).
$brand_ids = array();
if ( taxonomy_exists( 'product_brand' ) ) {
	foreach ( array( 'Orbit', 'Nova', 'Kestrel', 'Lumen', 'Arcadia', 'Voltix', 'Northwind', 'Zenith' ) as $brand ) {
		$brand_ids[ $brand ] = t360_seed_term( $brand, 'product_brand' );
	}
}

// Global attribute for variations.
$attr_id = wc_attribute_taxonomy_id_by_name( 'storage' );
if ( ! $attr_id ) {
	$attr_id = wc_create_attribute( array( 'name' => 'Storage', 'slug' => 'storage' ) );
	register_taxonomy( 'pa_storage', 'product' );
}
foreach ( array( '128GB', '256GB', '512GB' ) as $t ) {
	if ( ! term_exists( $t, 'pa_storage' ) ) {
		wp_insert_term( $t, 'pa_storage' );
	}
}

// [name, category, brand, shape, rgb, regular, sale, stock, featured, rating, reviews, sales]
$products = array(
	array( 'Orbit X15 Pro 5G Smartphone 256GB Titanium', 'Smartphones', 'Orbit', 'phone', array( 71, 84, 103 ), 4299, 3899, 25, true, 4.7, 312, 900 ),
	array( 'Nova S24 Ultra Smartphone 512GB Onyx Black', 'Smartphones', 'Nova', 'phone', array( 11, 18, 32 ), 5199, 4599, 2, true, 4.8, 845, 1500 ),
	array( 'Kestrel A5 Smartphone 128GB Mint', 'Smartphones', 'Kestrel', 'phone', array( 6, 118, 71 ), 1199, 0, 40, false, 4.3, 96, 300 ),
	array( 'Nova Tab S9 11-inch Wi-Fi Tablet 128GB Graphite', 'Tablets', 'Nova', 'phone', array( 102, 112, 133 ), 2799, 2399, 12, false, 4.6, 140, 220 ),
	array( 'Orbit Pad Air 10.9-inch Tablet 64GB Blue', 'Tablets', 'Orbit', 'phone', array( 29, 78, 216 ), 2199, 0, 0, false, 4.5, 60, 80 ),
	array( 'Zenith Book 14 Ultrabook Core Ultra 7 16GB 1TB', 'Ultrabooks', 'Zenith', 'laptop', array( 71, 84, 103 ), 5499, 4799, 8, true, 4.6, 51, 120 ),
	array( 'Arcadia Strix G16 Gaming Laptop RTX 4070 32GB', 'Gaming Laptops', 'Arcadia', 'laptop', array( 200, 16, 46 ), 7999, 6999, 3, true, 4.7, 73, 160 ),
	array( 'Lumen Air 13 Laptop M-Series 8GB 256GB Silver', 'Ultrabooks', 'Lumen', 'laptop', array( 102, 112, 133 ), 3899, 0, 15, false, 4.8, 410, 700 ),
	array( 'Northwind 65-inch 4K QLED Smart TV', 'Televisions', 'Northwind', 'tv', array( 29, 78, 216 ), 3499, 2799, 6, true, 4.4, 88, 260 ),
	array( 'Voltix 55-inch 4K UHD Android TV', 'Televisions', 'Voltix', 'tv', array( 11, 18, 32 ), 1899, 1599, 20, false, 4.2, 132, 410 ),
	array( 'Kestrel Pulse Wireless Noise Cancelling Headphones', 'Headphones', 'Kestrel', 'headphones', array( 11, 18, 32 ), 1299, 899, 30, true, 4.6, 520, 1100 ),
	array( 'Orbit Buds Pro 2 True Wireless Earbuds', 'Headphones', 'Orbit', 'headphones', array( 228, 231, 236 ), 949, 0, 50, false, 4.7, 1203, 2400 ),
	array( 'Voltix Boom 360 Portable Bluetooth Speaker', 'Speakers', 'Voltix', 'speaker', array( 29, 78, 216 ), 399, 299, 60, false, 4.4, 210, 650 ),
	array( 'Arcadia Play 5 Console Disc Edition', 'Consoles', 'Arcadia', 'console', array( 228, 231, 236 ), 2099, 1899, 4, true, 4.9, 980, 3100 ),
	array( 'Arcadia DualGrip Wireless Controller Midnight', 'Controllers', 'Arcadia', 'console', array( 11, 18, 32 ), 299, 249, 80, false, 4.5, 340, 900 ),
	array( 'Nova Watch 6 Classic 47mm Smart Watch', 'Smart Watches', 'Nova', 'watch', array( 71, 84, 103 ), 1599, 1199, 18, false, 4.4, 77, 190 ),
	array( 'Orbit Watch Series 9 GPS 45mm Midnight', 'Smart Watches', 'Orbit', 'watch', array( 11, 18, 32 ), 1799, 0, 22, true, 4.8, 640, 1300 ),
	array( 'Lumen Home Mini Smart Speaker with Assistant', 'Smart Home', 'Lumen', 'speaker', array( 102, 112, 133 ), 229, 179, 100, false, 4.3, 150, 500 ),
	array( 'Zenith Cam 2K Indoor Wi-Fi Security Camera', 'Smart Home', 'Zenith', 'speaker', array( 228, 231, 236 ), 199, 0, 0, false, 4.1, 44, 70 ),
	array( 'Voltix 65W GaN USB-C Fast Charger 3-Port', 'Chargers', 'Voltix', 'speaker', array( 11, 18, 32 ), 179, 129, 150, false, 4.6, 890, 2000 ),
	array( 'Northwind 20000mAh Power Bank 22.5W', 'Power Banks', 'Northwind', 'speaker', array( 71, 84, 103 ), 149, 99, 1, false, 4.5, 260, 800 ),
	array( 'Kestrel Braided USB-C to USB-C Cable 2m', 'Accessories', 'Kestrel', 'speaker', array( 200, 16, 46 ), 59, 0, 300, false, 0, 0, 120 ),
);

$ids = array();
foreach ( $products as $i => [ $name, $cat, $brand, $shape, $rgb, $regular, $sale, $stock, $featured, $rating, $reviews, $sales ] ) {
	$p = new WC_Product_Simple();
	$p->set_name( $name );
	$p->set_status( 'publish' );
	$p->set_regular_price( (string) $regular );
	if ( $sale ) {
		$p->set_sale_price( (string) $sale );
	}
	$p->set_manage_stock( true );
	$p->set_stock_quantity( $stock );
	$p->set_featured( $featured );
	$p->set_category_ids( array( $cat_ids[ $cat ] ) );
	$p->set_image_id( t360_seed_image( 'product-' . $i, $shape, $rgb ) );
	$p->set_short_description( 'Sample product for local theme testing.' );
	$p->set_date_created( time() - $i * DAY_IN_SECONDS );
	$id = $p->save();

	if ( $brand_ids ) {
		wp_set_object_terms( $id, array( $brand_ids[ $brand ] ), 'product_brand' );
	}
	update_post_meta( $id, 'total_sales', $sales );
	if ( $reviews ) {
		update_post_meta( $id, '_wc_average_rating', $rating );
		update_post_meta( $id, '_wc_review_count', $reviews );
		update_post_meta( $id, '_wc_rating_count', array( 5 => $reviews ) );
	}
	$ids[] = $id;
}

// One variable product (storage variations with different discounts).
$var = new WC_Product_Variable();
$var->set_name( 'Lumen Phone 16 Pro Smartphone Desert Titanium' );
$var->set_status( 'publish' );
$var->set_category_ids( array( $cat_ids['Smartphones'] ) );
$var->set_featured( true );
$var->set_image_id( t360_seed_image( 'product-variable', 'phone', array( 181, 71, 8 ) ) );
$attribute = new WC_Product_Attribute();
$attribute->set_id( $attr_id );
$attribute->set_name( 'pa_storage' );
$attribute->set_options( array_map( static fn( $t ) => get_term_by( 'name', $t, 'pa_storage' )->term_id, array( '128GB', '256GB', '512GB' ) ) );
$attribute->set_visible( true );
$attribute->set_variation( true );
$var->set_attributes( array( $attribute ) );
$var_id = $var->save();
if ( $brand_ids ) {
	wp_set_object_terms( $var_id, array( $brand_ids['Lumen'] ), 'product_brand' );
}
foreach ( array( '128gb' => array( 4299, 3999 ), '256gb' => array( 4799, 4099 ), '512gb' => array( 5699, 0 ) ) as $slug => [ $reg, $sale ] ) {
	$v = new WC_Product_Variation();
	$v->set_parent_id( $var_id );
	$v->set_attributes( array( 'pa_storage' => $slug ) );
	$v->set_regular_price( (string) $reg );
	if ( $sale ) {
		$v->set_sale_price( (string) $sale );
	}
	$v->set_manage_stock( true );
	$v->set_stock_quantity( 10 );
	$v->save();
}
WC_Product_Variable::sync( $var_id );
update_post_meta( $var_id, '_wc_average_rating', 4.8 );
update_post_meta( $var_id, '_wc_review_count', 402 );
update_post_meta( $var_id, 'total_sales', 1800 );
wc_delete_product_transients( $var_id );

// Footer menus.
foreach ( array( 'footer_1' => array( 'Shop', array( 'Deals' => '/shop/?deals=1', 'New arrivals' => '/shop/?orderby=date', 'Brands' => '/shop/' ) ), 'footer_2' => array( 'Help', array( 'Delivery' => '/delivery/', 'Returns' => '/returns/', 'Warranty' => '/warranty/', 'Contact us' => '/contact/' ) ) ) as $location => [ $menu_name, $links ] ) {
	$menu_id = wp_create_nav_menu( $menu_name );
	if ( is_wp_error( $menu_id ) ) {
		continue;
	}
	foreach ( $links as $title => $url ) {
		wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $title, 'menu-item-url' => home_url( $url ), 'menu-item-status' => 'publish' ) );
	}
	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

set_theme_mod( 't360_whatsapp_number', '971500000000' );
set_theme_mod( 't360_footer_phone', '+971 4 000 0000' );
set_theme_mod( 't360_footer_email', 'support@example.com' );
set_theme_mod( 't360_header_notice', 'Free delivery across the UAE on orders over AED 100' );

wc_delete_product_transients();
delete_transient( 'wc_products_onsale' );
echo 'store: seeded ' . ( count( $ids ) + 1 ) . " products\n";
