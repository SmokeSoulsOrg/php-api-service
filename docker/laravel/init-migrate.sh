#!/bin/bash

set -e
cd /var/www/html

echo "🔧 Changing .env ownership to www-data"
chown www-data:www-data /var/www/html/.env

echo "🔧 Ensuring .env is writable..."
chmod +w /var/www/html/.env || echo "⚠️  .env not writable and chmod failed"

echo "🔑 Generating app key..."
php artisan key:generate

echo "📦 Caching config..."
php artisan config:cache

echo "⏳ Waiting for MySQL primary..."
until mysqladmin ping -h"mysql" -u"laravel" -p"password" --silent; do
  echo "  ...waiting for mysql"
  sleep 2
done

echo "🛠 Running migrations on primary..."
php artisan migrate:fresh --force --database=mysql

echo "⏳ Waiting for MySQL replica..."
until mysqladmin ping -h"mysql_read" -u"laravel" -p"password" --silent; do
  echo "  ...waiting for mysql_read"
  sleep 2
done

echo "🛠 Running migrations on replica..."
php artisan migrate:fresh --force --database=mysql_read_direct

ENV_FILE=".env"
if [ -f "$ENV_FILE" ]; then
    echo "✅ Updating DB_USE_REPLICA=true in $ENV_FILE"
    TMP_FILE=$(mktemp)
    sed 's/^DB_USE_REPLICA=.*/DB_USE_REPLICA=true/' "$ENV_FILE" > "$TMP_FILE" && mv "$TMP_FILE" "$ENV_FILE"
else
    echo "⚠️  $ENV_FILE not found!"
fi

echo "✅ Migrations complete. Starting PHP-FPM..."
if command -v su-exec >/dev/null; then
    echo "🔒 Dropping to www-data"
    exec su-exec www-data "$@"
else
    echo "⚠️  su-exec not found, continuing as root"
    exec "$@"
fi

