@echo off
echo Starting Laravel Server with SQLite support...
echo Open http://127.0.0.1:8001/warga/login in your browser.
call php artisan optimize:clear
cd public
php -S 127.0.0.1:8001 ../server.php
pause
