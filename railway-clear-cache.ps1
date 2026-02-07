# Skrypt do czyszczenia cache na Railway
Write-Host "=== Railway Cache Clear ===" -ForegroundColor Cyan
Write-Host ""

# Wybierz serwis web
Write-Host "Wybierz serwis aplikacji (nie MySQL):" -ForegroundColor Yellow
railway service

Write-Host ""
Write-Host "Czyszczenie cache..." -ForegroundColor Cyan

# Czyść cache
railway run php artisan cache:clear
railway run php artisan config:clear
railway run php artisan view:clear
railway run php artisan route:clear

Write-Host ""
Write-Host "=== Cache wyczyszczony ===" -ForegroundColor Green
