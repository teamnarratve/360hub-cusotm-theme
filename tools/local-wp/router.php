<?php
// Router for `php -S`: serve real files directly, send everything else to WordPress.
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$file = getcwd() . $path;
if ( '/' !== $path && is_file( $file ) && ! str_ends_with( $path, '.php' ) ) {
	return false;
}
if ( is_file( $file ) && str_ends_with( $path, '.php' ) ) {
	require $file;
	return;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require getcwd() . '/index.php';
