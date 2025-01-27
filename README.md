# billing-test 
)))))) docker-compose up -d)))

./vendor/bin/pint 
vendor/bin/phpunit --configuration phpunit.xml --stop-on-failure 
vendor/bin/phpstan analyse --memory-limit=512M
php artisan storage:link
php artisan l5-swagger:generate
