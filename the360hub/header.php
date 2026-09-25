<?php
/**
 * Document head and site header.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#FFFFFF">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="t360-skip" href="#main"><?php esc_html_e( 'Skip to content', 'the360hub' ); ?></a>

<?php get_template_part( 'template-parts/header/site-header' ); ?>

<main id="main" class="t360-main" tabindex="-1">
