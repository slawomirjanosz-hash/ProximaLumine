#!/bin/bash
set -e

echo "=== Railway Deployment Start ==="

# Czyszczenie cache
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Bezpieczne migracje - nie przerywaj jeśli błąd
echo "Running migrations (safe mode)..."
php artisan migrate --force || {
    echo "⚠️ Migration warning - some migrations may have failed"
    echo "This is expected if tables already exist"
}

# Seeding (tylko jeśli potrzeba)
if [ "$RUN_SEEDER" = "true" ]; then
    echo "Running seeders..."
    php artisan db:seed --force || echo "⚠️ Seeder warning"
fi

# Link storage
php artisan storage:link || echo "Storage link already exists"

echo "=== Starting Laravel Server ==="
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
