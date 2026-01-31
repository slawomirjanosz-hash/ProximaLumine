# Skrypt do uruchomienia migracji na Railway
# Użycie: .\railway-migrate.ps1

Write-Host "=== Railway Database Migration ===" -ForegroundColor Cyan
Write-Host ""

# Sprawdź czy Railway CLI jest zainstalowane
$railwayInstalled = Get-Command railway -ErrorAction SilentlyContinue
if (-not $railwayInstalled) {
    Write-Host "Railway CLI nie jest zainstalowane. Instaluję..." -ForegroundColor Yellow
    npm install -g @railway/cli
    Write-Host ""
}

# Sprawdź czy projekt jest połączony
$linkedProject = railway status 2>&1
if ($linkedProject -match "No linked project") {
    Write-Host "KROK 1: Połącz projekt z Railway" -ForegroundColor Yellow
    Write-Host "Uruchamiam 'railway link'..." -ForegroundColor Cyan
    Write-Host "Wybierz:"
    Write-Host "  - Workspace: ProximaLumine" -ForegroundColor Green
    Write-Host "  - Project: Prinz" -ForegroundColor Green
    Write-Host "  - Environment: production" -ForegroundColor Green
    Write-Host "  - Service: magazyn" -ForegroundColor Green
    Write-Host ""
    
    railway link
}

Write-Host ""
Write-Host "KROK 2: Uruchamiam migracje na Railway..." -ForegroundColor Green
Write-Host "UWAGA: To nie usunie żadnych danych, tylko doda nowe kolumny." -ForegroundColor Yellow
Write-Host ""

# Uruchom migracje
railway run php artisan migrate --force

Write-Host ""
Write-Host "=== Gotowe! ===" -ForegroundColor Green
Write-Host "Sprawdź teraz czy wyceny działają na Railway:" -ForegroundColor Cyan
Write-Host "  https://your-railway-app.railway.app/wyceny/nowa" -ForegroundColor Blue

