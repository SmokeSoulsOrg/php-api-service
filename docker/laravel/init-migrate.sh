#!/bin/bash

set -e
cd /var/www/html

echo "🔑 Generating app key..."
php artisan key:generate

echo "📦 Caching config..."
php artisan config:cache

echo "⏳ Waiting for MySQL primary..."
until mysqladmin ping -h"${DB_HOST}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent; do
  echo "  ...waiting for ${DB_HOST}"
  sleep 2
done

echo "🛠 Running migrations on primary..."
php artisan migrate:fresh --database=mysql

echo "⏳ Waiting for MySQL replica..."
until mysqladmin ping -h"${DB_HOST_READ}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent; do
  echo "  ...waiting for ${DB_HOST_READ}"
  sleep 2
done

echo "🛠 Running migrations on replica..."
php artisan migrate:fresh --database=mysql_read_direct

ENV_FILE=".env"
if [ -f "$ENV_FILE" ]; then
    echo "✅ Updating DB_USE_REPLICA=true in $ENV_FILE"
    sed -i 's/^DB_USE_REPLICA=.*/DB_USE_REPLICA=true/' "$ENV_FILE"
else
    echo "⚠️  $ENV_FILE not found!"
fi

echo "✅ Migrations complete. Starting PHP-FPM..."
exec php-fpm
