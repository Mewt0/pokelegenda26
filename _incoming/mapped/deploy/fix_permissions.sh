#!/usr/bin/env bash
set -e
PROJECT_DIR="${1:-/var/www/pokemonchic.com}"
WEB_USER="${2:-www-data}"
chown -R "$WEB_USER:$WEB_USER" "$PROJECT_DIR"
find "$PROJECT_DIR" -type d -exec chmod 755 {} \;
find "$PROJECT_DIR" -type f -exec chmod 644 {} \;
find "$PROJECT_DIR" -name "*.sh" -exec chmod 755 {} \;
