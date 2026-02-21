# Quick CRM status check - PowerShell version

Write-Host "=== Quick CRM Status Check ===" -ForegroundColor Cyan
Write-Host ""

# Check if is_closed column exists
Write-Host "Checking is_closed column..." -ForegroundColor Yellow
$hasColumn = php artisan tinker --execute="echo Schema::hasColumn('crm_stages', 'is_closed') ? '1' : '0';"
if ($hasColumn -match "1") {
    Write-Host "✓ is_closed column EXISTS" -ForegroundColor Green
} else {
    Write-Host "✗ is_closed column MISSING!" -ForegroundColor Red
    Write-Host "Run: php artisan migrate" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Checking stages count..." -ForegroundColor Yellow
php artisan tinker --execute="
`$count = DB::table('crm_stages')->count();
echo 'Total stages: ' . `$count . PHP_EOL;

if (Schema::hasColumn('crm_stages', 'is_closed')) {
    `$withClosed = DB::table('crm_stages')->whereNotNull('is_closed')->count();
    if (`$withClosed > 0) {
        echo '✓ ' . `$withClosed . ' stages have is_closed value' . PHP_EOL;
    } else {
        echo '⚠ No stages have is_closed value' . PHP_EOL;
    }
}
"

Write-Host ""
Write-Host "Checking migrations..." -ForegroundColor Yellow
php artisan tinker --execute="
`$migration = DB::table('migrations')->where('migration', 'LIKE', '%is_closed%')->first();
if (`$migration) {
    echo '✓ is_closed migration executed (batch ' . `$migration->batch . ')' . PHP_EOL;
} else {
    echo '✗ is_closed migration NOT executed' . PHP_EOL;
}
"

Write-Host ""
Write-Host "Checking controller code..." -ForegroundColor Yellow
$controllerPath = "app\Http\Controllers\PartController.php"
if (Test-Path $controllerPath) {
    $content = Get-Content $controllerPath -Raw
    if ($content -match "Schema::hasColumn") {
        Write-Host "✓ Controller uses Schema::hasColumn()" -ForegroundColor Green
    } else {
        Write-Host "⚠ Controller doesn't use Schema::hasColumn()" -ForegroundColor Yellow
    }
    
    if ($content -match "is_closed") {
        Write-Host "✓ Controller handles is_closed" -ForegroundColor Green
    } else {
        Write-Host "✗ Controller doesn't handle is_closed" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=== End of Quick Check ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "For detailed diagnostics:" -ForegroundColor White
Write-Host "  Local:   http://localhost:8000/crm-diagnostics" -ForegroundColor Cyan
Write-Host "  Railway: https://your-app.railway.app/crm-diagnostics" -ForegroundColor Cyan
