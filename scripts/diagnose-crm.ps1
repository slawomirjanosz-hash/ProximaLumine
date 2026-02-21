# Diagnoza CRM - sprawdza stan tabeli crm_stages

Write-Host "=== Diagnoza tabeli crm_stages ===" -ForegroundColor Cyan
Write-Host ""

# Sprawdź strukturę tabeli
Write-Host "Struktura tabeli crm_stages:" -ForegroundColor Yellow
php artisan tinker --execute="
\$columns = DB::select('DESCRIBE crm_stages');
foreach (\$columns as \$col) {
    echo str_pad(\$col->Field, 20) . ' | ' . str_pad(\$col->Type, 15) . ' | Default: ' . (\$col->Default ?? 'NULL') . PHP_EOL;
}
"

Write-Host ""
Write-Host "Zawartość tabeli crm_stages:" -ForegroundColor Yellow
php artisan tinker --execute="
\$stages = DB::table('crm_stages')->orderBy('order')->get();
foreach (\$stages as \$stage) {
    \$isClosed = isset(\$stage->is_closed) ? (\$stage->is_closed ? 'TAK' : 'NIE') : 'BRAK KOLUMNY';
    echo sprintf(
        'ID: %d | %s (%s) | Order: %d | Active: %d | Closed: %s',
        \$stage->id,
        \$stage->name,
        \$stage->slug,
        \$stage->order,
        \$stage->is_active,
        \$isClosed
    ) . PHP_EOL;
}
"

Write-Host ""
Write-Host "Stan migracji:" -ForegroundColor Yellow
php artisan tinker --execute="
\$migration = DB::table('migrations')->where('migration', 'LIKE', '%is_closed%')->first();
if (\$migration) {
    echo '✓ Migracja is_closed została uruchomiona' . PHP_EOL;
    echo 'Batch: ' . \$migration->batch . PHP_EOL;
} else {
    echo '✗ Migracja is_closed NIE została uruchomiona!' . PHP_EOL;
    echo 'Uruchom: php artisan migrate' . PHP_EOL;
}
"

Write-Host ""
Write-Host "=== Koniec diagnozy ===" -ForegroundColor Cyan
