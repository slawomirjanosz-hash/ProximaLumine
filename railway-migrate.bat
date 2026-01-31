@echo off
echo ======================================
echo Railway Database Migration Script
echo ======================================
echo.
echo UWAGA: Ten skrypt doda nowe kolumny do bazy danych
echo        NIE usunie zadnych istniejacych danych!
echo.
echo Upewnij sie, ze jestes zalogowany do Railway.
echo.
pause

echo.
echo Laczenie z projektem Railway...
railway link

echo.
echo Uruchamianie migracji...
railway run php artisan migrate --force

echo.
echo ======================================
echo Gotowe!
echo ======================================
echo.
echo Sprawdz teraz czy wyceny dzialaja na Railway.
echo.
pause
