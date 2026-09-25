#!/usr/bin/env bash
# Local WordPress + WooCommerce test site on SQLite (no MySQL needed).
#
# Sources (git clones; see README in this folder):
#   WP_SRC      WordPress core (github.com/WordPress/WordPress, tag 6.8.x)
#   SQLITE_SRC  github.com/WordPress/sqlite-database-integration
#   WOO_SRC     woocommerce/plugins/woocommerce with `composer install --no-dev` run
#
# Usage: tools/local-wp/setup.sh  → then tools/local-wp/serve.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
STACK="${STACK:-/tmp/wpstack}"
WP_SRC="${WP_SRC:-$STACK/wordpress}"
SQLITE_SRC="${SQLITE_SRC:-$STACK/sqlite/packages/plugin-sqlite-database-integration}"
WOO_SRC="${WOO_SRC:-$STACK/woo/plugins/woocommerce}"
SITE="${SITE:-$STACK/site}"
PORT="${PORT:-8080}"

rm -rf "$SITE"
cp -r "$WP_SRC" "$SITE"
rm -rf "$SITE/.git"

mkdir -p "$SITE/wp-content/plugins" "$SITE/wp-content/themes" "$SITE/wp-content/database"
ln -sfn "$SQLITE_SRC" "$SITE/wp-content/plugins/sqlite-database-integration"
ln -sfn "$WOO_SRC" "$SITE/wp-content/plugins/woocommerce"
ln -sfn "$ROOT/the360hub" "$SITE/wp-content/themes/the360hub"
mkdir -p "$SITE/wp-content/mu-plugins"
cp "$ROOT/tools/local-wp/local-dev.php" "$SITE/wp-content/mu-plugins/"

sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$SITE/wp-content/plugins/sqlite-database-integration#" \
    -e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
    "$SQLITE_SRC/db.copy" > "$SITE/wp-content/db.php"

cat > "$SITE/wp-config.php" <<PHP
<?php
define( 'DB_NAME', 'wp' );
define( 'DB_USER', '' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'DB_DIR', __DIR__ . '/wp-content/database/' );
define( 'DB_FILE', 'site.sqlite' );
define( 'WP_HOME', 'http://localhost:$PORT' );
define( 'WP_SITEURL', 'http://localhost:$PORT' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', __DIR__ . '/debug.log' );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'AUTH_KEY', 'local' ); define( 'SECURE_AUTH_KEY', 'local' ); define( 'LOGGED_IN_KEY', 'local' ); define( 'NONCE_KEY', 'local' );
define( 'AUTH_SALT', 'local' ); define( 'SECURE_AUTH_SALT', 'local' ); define( 'LOGGED_IN_SALT', 'local' ); define( 'NONCE_SALT', 'local' );
\$table_prefix = 'wp_';
if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
require_once ABSPATH . 'wp-settings.php';
PHP

php -d memory_limit=1G "$ROOT/tools/local-wp/install.php" "$SITE" core
php -d memory_limit=1G "$ROOT/tools/local-wp/install.php" "$SITE" store
echo "Site ready: $SITE  (run tools/local-wp/serve.sh)"
