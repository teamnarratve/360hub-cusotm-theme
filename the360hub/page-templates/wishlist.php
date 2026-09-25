<?php
/**
 * Template Name: Wishlist
 *
 * The wishlist is stored in the visitor's browser; lists.js renders the
 * saved products into the grid via ?wc-ajax=t360_cards.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="t360-container t360-page">
	<header class="t360-archive__head">
		<h1 class="t360-archive__title"><?php the_title(); ?></h1>
		<p class="t360-archive__count" data-t360-list-count></p>
	</header>

	<div data-t360-list="wishlist">
		<ul class="t360-grid" data-t360-list-items aria-busy="true"></ul>
		<div class="t360-empty" data-t360-list-empty hidden>
			<?php t360_the_icon( 'heart', 't360-empty__icon' ); ?>
			<h2 class="t360-empty__title"><?php esc_html_e( 'Your wishlist is empty', 'the360hub' ); ?></h2>
			<p><?php esc_html_e( 'Tap the heart on any product to save it here.', 'the360hub' ); ?></p>
			<?php if ( t360_has_wc() ) : ?>
				<a class="t360-btn t360-btn--cta" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'Start shopping', 'the360hub' ); ?></a>
			<?php endif; ?>
		</div>
		<noscript><p class="t360-empty"><?php esc_html_e( 'Please enable JavaScript to see your saved products.', 'the360hub' ); ?></p></noscript>
	</div>

	<?php
	while ( have_posts() ) :
		the_post();
		if ( get_the_content() ) :
			?>
			<div class="t360-prose"><?php the_content(); ?></div>
			<?php
		endif;
	endwhile;
	?>
</div>
<?php
get_footer();
