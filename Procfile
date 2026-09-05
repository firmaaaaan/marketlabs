web: php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:work --sleep=3 --tries=3 --timeout=60 --max-jobs=50 --memory=256
scheduler: php artisan schedule:work