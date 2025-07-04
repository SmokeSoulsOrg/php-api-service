#!/bin/bash

set -e
cd /var/www/html

ENV_FILE=".env"

if [ -f "$ENV_FILE" ]; then
    echo "🔧 Resetting DB_USE_REPLICA=false in $ENV_FILE before migrations"
    TMP_FILE=$(mktemp)
    sed 's/^DB_USE_REPLICA=.*/DB_USE_REPLICA=false/' "$ENV_FILE" > "$TMP_FILE"
    cp "$TMP_FILE" "$ENV_FILE"
    rm "$TMP_FILE"
else
    echo "⚠️  $ENV_FILE not found before migrations!"
fi

echo "🔧 Changing .env ownership to www-data"
chown www-data:www-data /var/www/html/.env

echo "🔧 Ensuring .env is writable..."
chmod +w /var/www/html/.env || echo "⚠️  .env not writable and chmod failed"

echo "🔑 Generating app key..."
php artisan key:generate

echo "📦 Caching config..."
php artisan config:cache

echo "⏳ Waiting for MySQL primary..."
until mysqladmin ping -hmysql -ularavel -ppassword --silent; do
  echo "  ...waiting for mysql"
  sleep 2
done

echo "🔎 Checking if primary DB is already migrated..."
IS_PRIMARY_MIGRATED=$(mysql -hmysql -ularavel -ppassword -Dlaravel -e "SHOW TABLES LIKE 'migrations';" | grep -c migrations)

if [ "$IS_PRIMARY_MIGRATED" -eq 0 ]; then
  echo "🛠 Running migrations on primary..."
  php artisan migrate:fresh --force --database=mysql
else
  echo "✅ Primary DB already migrated. Skipping..."
fi

echo "⏳ Waiting for MySQL replica..."
until mysqladmin ping -hmysql_read -ularavel -ppassword --silent; do
  echo "  ...waiting for mysql_read"
  sleep 2
done

echo "🔎 Checking if replica DB is already migrated..."
IS_REPLICA_MIGRATED=$(mysql -hmysql_read -ularavel -ppassword -Dlaravel -e "SHOW TABLES LIKE 'migrations';" | grep -c migrations)

if [ "$IS_REPLICA_MIGRATED" -eq 0 ]; then
  echo "🛠 Running migrations on replica..."
  php artisan migrate:fresh --force --database=mysql_read_direct
else
  echo "✅ Replica DB already migrated. Skipping..."
fi

if [ -f "$ENV_FILE" ]; then
    echo "✅ Updating DB_USE_REPLICA=true in $ENV_FILE"
    TMP_FILE=$(mktemp)
    sed 's/^DB_USE_REPLICA=.*/DB_USE_REPLICA=true/' "$ENV_FILE" > "$TMP_FILE"
    cp "$TMP_FILE" "$ENV_FILE"
    rm "$TMP_FILE"
else
    echo "⚠️  $ENV_FILE not found!"
fi

echo "✅ Migrations complete. Starting PHP server..."
exec php artisan serve --host=0.0.0.0 --port=9000
