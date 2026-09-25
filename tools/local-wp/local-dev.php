<?php
/**
 * Local test site only (installed as an mu-plugin by setup.sh).
 * WooCommerce built from source lacks its compiled React admin assets, so
 * the new admin (wc-admin) is switched off to keep wp-admin usable.
 */
add_filter( 'woocommerce_admin_disabled', '__return_true' );
