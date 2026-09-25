<?php
/**
 * Front-end weight reduction.
 *
 * Removes assets this theme does not use, on the pages where they are not
 * needed. Everything here is conservative: content pages that may contain
 * blocks keep the block styles.
 *
 * @package The360Hub
 */

namespace The360Hub\Core;

defined( 'ABSPATH' ) || exit;

final class Performance {

	public static function init(): void {
		// Emoji detection script + styles (~15KB) — modern devices render emoji natively.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		add_filter( 'emoji_svg_url', '__return_false' );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_block_styles' ), 100 );
		add_filter( 'image_editor_output_format', array( __CLASS__, 'image_output_format' ) );
	}

	/**
	 * Block library CSS is only needed where post content is rendered.
	 */
	public static function dequeue_block_styles(): void {
		$renders_content = is_singular() && ! ( function_exists( 'is_product' ) && is_product() ) && ! is_front_page();
		if ( $renders_content ) {
			return;
		}
		foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wc-blocks-style' ) as $handle ) {
			wp_dequeue_style( $handle );
		}
	}

	/**
	 * Generate WebP sub-sizes for JPEG uploads when the server supports it.
	 * WordPress keeps the original upload, so this is reversible. PNGs are
	 * left alone: palette PNGs (logos, graphics) can't be converted by GD.
	 */
	public static function image_output_format( array $formats ): array {
		if ( ! t360_setting( 'images_webp' ) || ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			return $formats;
		}
		$formats['image/jpeg'] = 'image/webp';
		return $formats;
	}
}
