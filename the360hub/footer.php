<?php
/**
 * Closes main, prints footer, bottom nav, toast region and icon sprite.
 *
 * @package The360Hub
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php
get_template_part( 'template-parts/footer/site-footer' );
get_template_part( 'template-parts/navigation/bottom-nav' );
?>

<div class="t360-toast" role="status" aria-live="polite" data-t360-toast></div>

<?php
wp_footer();
The360Hub\Core\Icons::sprite();
?>
</body>
</html>
