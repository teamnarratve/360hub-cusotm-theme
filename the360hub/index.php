<?php
/**
 * Fallback template: blog index, archives and non-product search.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="t360-container t360-page">
	<header class="t360-archive__head">
		<h1 class="t360-archive__title">
			<?php
			if ( is_search() ) {
				/* translators: %s: search query */
				printf( esc_html__( 'Results for “%s”', 'the360hub' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				echo wp_kses_post( get_the_archive_title() );
			} elseif ( is_home() && ! is_front_page() ) {
				single_post_title();
			} else {
				bloginfo( 'name' );
			}
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="t360-postlist">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 't360-postlist__item' ); ?>>
					<h2 class="t360-postlist__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="t360-postlist__excerpt"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'class' => 't360-pagination' ) ); ?>
	<?php else : ?>
		<div class="t360-empty">
			<p><?php esc_html_e( 'Nothing found. Try a different search.', 'the360hub' ); ?></p>
			<?php get_search_form(); ?>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
