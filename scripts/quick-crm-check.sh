#!/bin/bash
# Quick CRM status check for Railway

echo "=== Quick CRM Status Check ==="
echo ""

# Check if is_closed column exists
echo "Checking is_closed column..."
php artisan tinker --execute="
if (Schema::hasColumn('crm_stages', 'is_closed')) {
    echo '✓ is_closed column EXISTS' . PHP_EOL;
} else {
    echo '✗ is_closed column MISSING!' . PHP_EOL;
    echo 'Run: php artisan migrate --force' . PHP_EOL;
}
"

echo ""
echo "Checking stages count..."
php artisan tinker --execute="
\$count = DB::table('crm_stages')->count();
echo 'Total stages: ' . \$count . PHP_EOL;

\$withClosed = DB::table('crm_stages')->whereNotNull('is_closed')->count();
if (\$withClosed > 0) {
    echo '✓ ' . \$withClosed . ' stages have is_closed value' . PHP_EOL;
} else {
    echo '⚠ No stages have is_closed value (NEW DB?)' . PHP_EOL;
}
"

echo ""
echo "Checking migrations..."
php artisan tinker --execute="
\$migration = DB::table('migrations')->where('migration', 'LIKE', '%is_closed%')->first();
if (\$migration) {
    echo '✓ is_closed migration executed (batch ' . \$migration->batch . ')' . PHP_EOL;
} else {
    echo '✗ is_closed migration NOT executed' . PHP_EOL;
}
"

echo ""
echo "=== End of Quick Check ==="
echo ""
echo "For detailed diagnostics, visit: /crm-diagnostics"
