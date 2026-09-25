<?php
/**
 * Search overlay (full screen on mobile, top panel on desktop).
 *
 * Idle state: recent searches (from localStorage, filled by search.js) and
 * popular searches (server-rendered from settings).
 * Typing state: instant results from ?wc-ajax=t360_search.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;

$popular    = array_filter( array_map( 'trim', explode( "\n", (string) t360_setting( 'popular_searches' ) ) ) );
$search_url = static function ( string $term ): string {
	$args = array( 's' => $term );
	if ( t360_has_wc() ) {
		$args['post_type'] = 'product';
	}
	return add_query_arg( array_map( 'rawurlencode', $args ), home_url( '/' ) );
};
?>
<dialog class="t360-sheet t360-sheet--search" id="t360-search" aria-label="<?php esc_attr_e( 'Search', 'the360hub' ); ?>" data-t360-search>
	<div class="t360-sheet__inner">
		<form class="t360-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<button type="button" class="t360-iconbtn" data-t360-close>
				<?php t360_the_icon( 'arrow-left' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close search', 'the360hub' ); ?></span>
			</button>
			<label class="screen-reader-text" for="t360-search-input"><?php esc_html_e( 'Search products', 'the360hub' ); ?></label>
			<div class="t360-searchfield t360-searchfield--overlay">
				<?php t360_the_icon( 'search', 't360-searchfield__icon' ); ?>
				<input
					class="t360-searchfield__input"
					type="search"
					id="t360-search-input"
					name="s"
					placeholder="<?php echo esc_attr( (string) ( $args['placeholder'] ?? '' ) ); ?>"
					autocomplete="off"
					autocapitalize="off"
					spellcheck="false"
					enterkeyhint="search"
					maxlength="64"
					aria-describedby="t360-search-status"
				>
				<button type="button" class="t360-searchfield__clear" data-t360-search-clear hidden>
					<?php t360_the_icon( 'close' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Clear search', 'the360hub' ); ?></span>
				</button>
			</div>
			<?php if ( t360_has_wc() ) : ?>
				<input type="hidden" name="post_type" value="product">
			<?php endif; ?>
		</form>

		<p class="screen-reader-text" id="t360-search-status" role="status" aria-live="polite" data-t360-search-status></p>

		<div class="t360-search__body">
			<div data-t360-search-idle>
				<section class="t360-search__group" data-t360-search-recent hidden>
					<div class="t360-search__grouphead">
						<h2 class="t360-search__title"><?php esc_html_e( 'Recent searches', 'the360hub' ); ?></h2>
						<button type="button" class="t360-link" data-t360-search-recent-clear><?php esc_html_e( 'Clear', 'the360hub' ); ?></button>
					</div>
					<ul class="t360-search__list" data-t360-search-recent-list></ul>
				</section>

				<?php if ( $popular ) : ?>
					<section class="t360-search__group">
						<h2 class="t360-search__title"><?php esc_html_e( 'Popular searches', 'the360hub' ); ?></h2>
						<ul class="t360-chips">
							<?php foreach ( $popular as $popular_term ) : ?>
								<li><a class="t360-chip" href="<?php echo esc_url( $search_url( $popular_term ) ); ?>" data-t360-search-term="<?php echo esc_attr( $popular_term ); ?>"><?php t360_the_icon( 'trending' ); ?><?php echo esc_html( $popular_term ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			</div>

			<div data-t360-search-results hidden></div>
		</div>
	</div>
</dialog>
