<?php
/**
 * Verifies WCAG contrast for every colour preset and the neutral tokens.
 * Run: php tools/check-presets.php
 */

// Minimal stubs so the settings class loads outside WordPress.
define( 'ABSPATH', __DIR__ );
function __( $s ) { return $s; }
function apply_filters( $hook, $value ) { return $value; }
require __DIR__ . '/../the360hub/inc/core/class-settings.php';

use The360Hub\Core\Settings;

function t360_lum( string $hex ): float {
	$hex = ltrim( $hex, '#' );
	$l   = 0;
	foreach ( array( 0.2126, 0.7152, 0.0722 ) as $i => $w ) {
		$c  = hexdec( substr( $hex, $i * 2, 2 ) ) / 255;
		$c  = $c <= 0.03928 ? $c / 12.92 : ( ( $c + 0.055 ) / 1.055 ) ** 2.4;
		$l += $w * $c;
	}
	return $l;
}
function t360_ratio( string $a, string $b ): float {
	$x = t360_lum( $a );
	$y = t360_lum( $b );
	return ( max( $x, $y ) + 0.05 ) / ( min( $x, $y ) + 0.05 );
}
// Same rule as Core\Assets::on_color().
function t360_on( string $hex ): string {
	$l = t360_lum( $hex );
	return 1.05 / ( $l + 0.05 ) >= ( $l + 0.05 ) / 0.0563 ? '#FFFFFF' : '#0B1220';
}

$tokens = file_get_contents( __DIR__ . '/../src/css/tokens.css' );
$token  = static function ( string $name ) use ( $tokens ): string {
	preg_match( '/--t360-' . preg_quote( $name, '/' ) . ':\s*(#[0-9A-Fa-f]{6})/', $tokens, $m );
	return $m[1] ?? '#000000';
};

$failed = 0;
$check  = static function ( string $label, string $fg, string $bg, float $min ) use ( &$failed ) {
	$r  = t360_ratio( $fg, $bg );
	$ok = $r >= $min;
	$failed += $ok ? 0 : 1;
	printf( "%s %5.2f:1 ≥%.1f  %s\n", $ok ? 'ok  ' : 'FAIL', $r, $min, $label );
};

echo "Neutral tokens\n";
foreach ( array( 'text', 'text-2', 'text-3' ) as $t ) {
	$check( "{$t} on white", $token( $t ), '#FFFFFF', 4.5 );
}
$check( 'border-strong on white (non-text)', $token( 'border-strong' ), '#FFFFFF', 3 );
$check( 'save on white', $token( 'save' ), '#FFFFFF', 4.5 );
$check( 'save on save-soft', $token( 'save' ), $token( 'save-soft' ), 4.5 );
$check( 'warn on white', $token( 'warn' ), '#FFFFFF', 4.5 );

foreach ( Settings::presets() as $key => $preset ) {
	$c = $preset['colors'];
	echo "\nPreset: {$key}\n";
	foreach ( array( 'brand', 'cta', 'deal', 'highlight', 'header', 'topbar', 'footer' ) as $name ) {
		$check( "text on {$name} ({$c[$name]})", t360_on( $c[ $name ] ), $c[ $name ], 4.5 );
	}
	$check( 'cta as link text on white', $c['cta'], '#FFFFFF', 4.5 );
	$check( 'deal as sale-price text on white', $c['deal'], '#FFFFFF', 4.5 );
	$check( 'text-2 on page background', $token( 'text-2' ), $c['page'], 4.5 );
	$check( 'text-3 on page background', $token( 'text-3' ), $c['page'], 4.5 );
}
echo $failed ? "\n{$failed} contrast checks failed\n" : "\nAll contrast checks passed\n";
exit( $failed ? 1 : 0 );
