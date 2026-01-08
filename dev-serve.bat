@echo off
echo Starting Laravel Server with SQLite support...
echo Open http://127.0.0.1:8000/warga/login in your browser.
call php -d extension=intl -d extension=pdo_mysql artisan optimize:clear
cd public
php -d extension=intl -d extension=pdo_mysql -S 127.0.0.1:8000 ../server.php
pause
