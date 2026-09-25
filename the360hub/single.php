<?php
/**
 * Single post.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

get_header();

$is_wc_page = t360_has_wc() && ( is_cart() || is_checkout() || is_account_page() );
?>
<div class="t360-container t360-page<?php echo $is_wc_page ? ' t360-page--wc' : ''; ?>">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class(); ?>>
			<h1 class="t360-page__title"><?php the_title(); ?></h1>
			<div class="<?php echo $is_wc_page ? 't360-wc-content' : 't360-prose'; ?>">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
		</article>
	<?php endwhile; ?>
</div>
<?php
get_footer();
