#!/bin/bash

echo "Starting Laravel Server with SQLite support..."
echo "Open http://127.0.0.1:8001/warga/login in your browser."

# Clear cache with necessary extensions enabled
php -d extension=intl -d extension=pdo_mysql artisan optimize:clear

cd public

# Start PHP built-in server
php -d extension=intl -d extension=pdo_mysql -S 127.0.0.1:8001 ../server.php
