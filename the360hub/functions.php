<?php
/**
 * The360Hub theme bootstrap.
 *
 * Registers the class autoloader and boots each module. Modules are small,
 * single-purpose classes under inc/ that attach themselves to WordPress hooks.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

define( 'THE360HUB_VERSION', '0.2.0' );
define( 'THE360HUB_DIR', get_template_directory() );
define( 'THE360HUB_URI', get_template_directory_uri() );

/*
 * Autoloader: The360Hub\Core\Product_Card -> inc/core/class-product-card.php
 * (namespace segments lower-cased, class file named per WordPress standards).
 */
spl_autoload_register(
	static function ( $class_name ) {
		$prefix = 'The360Hub\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$parts = explode( '\\', substr( $class_name, strlen( $prefix ) ) );
		$class = array_pop( $parts );
		$dir   = strtolower( implode( '/', $parts ) );
		$file  = THE360HUB_DIR . '/inc/' . ( $dir ? $dir . '/' : '' ) . 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';

		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

require THE360HUB_DIR . '/inc/helpers.php';

The360Hub\Core\Theme::init();
The360Hub\Core\Customizer::init();
The360Hub\Core\Assets::init();
The360Hub\Core\Performance::init();

if ( class_exists( 'WooCommerce' ) ) {
	The360Hub\WooCommerce\Setup::init();
	The360Hub\WooCommerce\Catalog::init();
	The360Hub\WooCommerce\Ajax::init();
}
