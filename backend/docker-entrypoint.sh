#!/bin/bash
set -e

echo "Starting Laravel setup..."

mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

echo "Waiting for MySQL to be ready..."

until nc -z mysql 3306; do
    echo "Waiting for MySQL port 3306..."
    sleep 2
done

echo "MySQL port is open, waiting for service to be fully ready..."
sleep 10

echo "MySQL is ready. Setting up Laravel..."

su -c "composer dump-autoload"

su -c "php artisan jwt:secret"

su -c "php artisan migrate"
su -c "php artisan db:seed"

su -c "php artisan config:clear"
su -c "php artisan route:clear"
su -c "php artisan cache:clear"

echo "Laravel setup complete. Starting background services..."

/usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf &

sleep 5

echo "Starting PHP-FPM..."
php-fpm
