#!/usr/bin/env bash
# Serve the local test site with PHP's built-in server.
DIR="$(cd "$(dirname "$0")" && pwd)"
STACK="${STACK:-/tmp/wpstack}"
SITE="${SITE:-$STACK/site}"
PORT="${PORT:-8080}"
cd "$SITE" && exec php -d memory_limit=512M -S "localhost:$PORT" "$DIR/router.php"
