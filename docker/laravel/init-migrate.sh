#!/bin/bash

set -e

cd /var/www/html

echo "⏳ Waiting for mysql to be ready..."
until mysqladmin ping -h"mysql" -u"sail" -p"password" --silent; do
  echo "  ...waiting for mysql"
  sleep 2
done

echo "🛠 Running migrations on primary..."
php artisan migrate:fresh --database=mysql

echo "⏳ Waiting for mysql_read to be ready..."
until mysqladmin ping -h"mysql_read" -u"sail" -p"password" --silent; do
  echo "  ...waiting for mysql_read"
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

echo "✅ Migrations complete."

# Final step: start Laravel server
if [ -f /usr/local/bin/start-container ]; then
    echo "🚀 Starting Laravel server..."
    exec /usr/local/bin/start-container
else
    echo "❌ start-container script not found!"
    exit 1
fi
