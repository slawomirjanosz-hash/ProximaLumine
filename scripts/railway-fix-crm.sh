#!/bin/bash
# Railway auto-fix script - naprawia bazę CRM podczas deploymentu

echo "🔧 Sprawdzanie bazy danych CRM..."

# Uruchom migrację jeśli potrzebna
php artisan migrate --force

echo "✅ Migracje zakończone"

# Opcjonalnie: uruchom skrypt naprawczy
if [ -f scripts/fix-crm-stages-railway.php ]; then
    echo "🔧 Uruchamianie skryptu naprawczego..."
    php scripts/fix-crm-stages-railway.php
fi

echo "✅ CRM naprawiony i gotowy do użycia"
