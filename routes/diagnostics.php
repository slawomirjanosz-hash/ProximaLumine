<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// Diagnostyka wykresów Gantta
Route::get('/diagnostics/gantt', function () {
    return view('diagnostics-gantt');
})->name('diagnostics.gantt');

// NOWA ROUTE: Diagnostyka projektu - sprawdza tabele i kolumny
Route::get('/diagnostics/project-check', function () {
    $results = [];
    
    // 1. Sprawdź połączenie z bazą danych
    try {
        DB::connection()->getPdo();
        $results['database_connection'] = '✅ OK';
    } catch (\Exception $e) {
        $results['database_connection'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    // 2. Sprawdź czy tabela projects istnieje
    try {
        $results['table_projects_exists'] = Schema::hasTable('projects') ? '✅ Istnieje' : '❌ Brak';
    } catch (\Exception $e) {
        $results['table_projects_exists'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    // 3. Sprawdź czy kolumna loaded_list_id istnieje w projects
    try {
        $results['column_loaded_list_id'] = Schema::hasColumn('projects', 'loaded_list_id') ? '✅ Istnieje' : '❌ Brak';
    } catch (\Exception $e) {
        $results['column_loaded_list_id'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    // 4. Sprawdź czy tabela product_lists istnieje
    try {
        $results['table_product_lists_exists'] = Schema::hasTable('product_lists') ? '✅ Istnieje' : '❌ Brak';
    } catch (\Exception $e) {
        $results['table_product_lists_exists'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    // 5. Sprawdź czy tabela product_list_items istnieje
    try {
        $results['table_product_list_items_exists'] = Schema::hasTable('product_list_items') ? '✅ Istnieje' : '❌ Brak';
    } catch (\Exception $e) {
        $results['table_product_list_items_exists'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    // 6. Sprawdź jakie migracje zostały uruchomione (ostatnie 15)
    try {
        $migrations = DB::table('migrations')->orderBy('batch', 'desc')->limit(15)->get();
        $results['recent_migrations'] = $migrations->map(fn($m) => $m->migration)->toArray();
    } catch (\Exception $e) {
        $results['recent_migrations'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    // 7. Sprawdź czy model ProductList działa
    try {
        if (class_exists('\App\Models\ProductList')) {
            $count = \App\Models\ProductList::count();
            $results['product_list_model'] = "✅ OK (znaleziono $count list)";
        } else {
            $results['product_list_model'] = '❌ Klasa nie istnieje';
        }
    } catch (\Exception $e) {
        $results['product_list_model'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    // 8. Sprawdź wszystkie kolumny w tabeli projects
    try {
        if (Schema::hasTable('projects')) {
            $columns = Schema::getColumnListing('projects');
            $results['projects_columns'] = $columns;
        }
    } catch (\Exception $e) {
        $results['projects_columns'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    // 9. Sprawdź pierwszy projekt
    try {
        $project = \App\Models\Project::first();
        if ($project) {
            $results['first_project'] = [
                'id' => $project->id,
                'name' => $project->name,
                'has_loaded_list_id' => isset($project->loaded_list_id),
                'loaded_list_id_value' => $project->loaded_list_id ?? 'null',
            ];
        } else {
            $results['first_project'] = 'Brak projektów w bazie';
        }
    } catch (\Exception $e) {
        $results['first_project'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    // 10. Info o środowisku
    $results['environment'] = [
        'APP_ENV' => env('APP_ENV'),
        'APP_DEBUG' => env('APP_DEBUG') ? 'true' : 'false',
        'DB_CONNECTION' => env('DB_CONNECTION'),
        'PHP_VERSION' => PHP_VERSION,
        'LARAVEL_VERSION' => app()->version(),
    ];
    
    return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
})->name('diagnostics.project.check');

// Test szczegółów projektu
Route::get('/diagnostics/project-details-test/{id}', function ($id) {
    try {
        $project = \App\Models\Project::findOrFail($id);
        
        $data = [
            'status' => '✅ Projekt załadowany',
            'project_id' => $project->id,
            'project_name' => $project->name,
            'loaded_list_id' => $project->loaded_list_id,
        ];
        
        // Sprawdź czy loadedList działa
        try {
            $loadedList = $project->loadedList;
            $data['loadedList_test'] = $loadedList ? "✅ Lista: " . $loadedList->name : "ℹ️ Brak załadowanej listy";
        } catch (\Exception $e) {
            $data['loadedList_test'] = "❌ BŁĄD: " . $e->getMessage();
        }
        
        // Sprawdź czy removals działa
        try {
            $removalsCount = $project->removals()->count();
            $data['removals_test'] = "✅ Pobrań: $removalsCount";
        } catch (\Exception $e) {
            $data['removals_test'] = "❌ BŁĄD: " . $e->getMessage();
        }
        
        // Sprawdź czy ProductList::all() działa
        try {
            $listsCount = \App\Models\ProductList::count();
            $data['product_lists_count'] = "✅ List produktów: $listsCount";
        } catch (\Exception $e) {
            $data['product_lists_count'] = "❌ BŁĄD: " . $e->getMessage();
        }
        
        return response()->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => '❌ BŁĄD',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
})->name('diagnostics.project.details.test');

// Test renderowania widoku project-details
Route::get('/diagnostics/render-project-details/{id}', function ($id) {
    try {
        $project = \App\Models\Project::findOrFail($id);
        $users = \App\Models\User::all();
        
        // Próba renderowania widoku
        return view('parts.project-details', compact('project', 'users'));
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => '❌ BŁĄD podczas renderowania widoku',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
        ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
})->name('diagnostics.render.project.details');

// Test części widoku project-details krok po kroku
Route::get('/diagnostics/test-view-parts/{id}', function ($id) {
    $results = [];
    
    try {
        $project = \App\Models\Project::findOrFail($id);
        $results['1_project_loaded'] = '✅ OK';
    } catch (\Exception $e) {
        return response()->json(['error' => '1_project_loaded', 'msg' => $e->getMessage()], 500, [], JSON_PRETTY_PRINT);
    }
    
    try {
        $users = \App\Models\User::all();
        $results['2_users_loaded'] = '✅ OK (' . $users->count() . ' users)';
    } catch (\Exception $e) {
        return response()->json(['error' => '2_users_loaded', 'msg' => $e->getMessage()], 500, [], JSON_PRETTY_PRINT);
    }
    
    try {
        $qrSettings = \DB::table('qr_settings')->first();
        $results['3_qr_settings'] = '✅ OK';
    } catch (\Exception $e) {
        return response()->json(['error' => '3_qr_settings', 'msg' => $e->getMessage()], 500, [], JSON_PRETTY_PRINT);
    }
    
    try {
        $companySettings = \App\Models\CompanySetting::first();
        $results['4_company_settings'] = '✅ OK';
    } catch (\Exception $e) {
        return response()->json(['error' => '4_company_settings', 'msg' => $e->getMessage()], 500, [], JSON_PRETTY_PRINT);
    }
    
    try {
        // Test include parts.menu
        $menuHtml = view('parts.menu')->render();
        $results['5_menu_include'] = '✅ OK (' . strlen($menuHtml) . ' bytes)';
    } catch (\Exception $e) {
        return response()->json(['error' => '5_menu_include', 'msg' => $e->getMessage(), 'line' => $e->getLine()], 500, [], JSON_PRETTY_PRINT);
    }
    
    try {
        // Test podstawowego HTML bez include
        $html = '<html><body>Test</body></html>';
        $results['6_basic_html'] = '✅ OK';
    } catch (\Exception $e) {
        return response()->json(['error' => '6_basic_html', 'msg' => $e->getMessage()], 500, [], JSON_PRETTY_PRINT);
    }
    
    return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
})->name('diagnostics.test.view.parts');

// Próba renderowania z debugiem
Route::get('/diagnostics/render-with-debug/{id}', function ($id) {
    // Włącz debug tymczasowo
    config(['app.debug' => true]);
    
    try {
        $project = \App\Models\Project::findOrFail($id);
        $users = \App\Models\User::all();
        
        return view('parts.project-details', compact('project', 'users'));
        
    } catch (\Throwable $e) {
        // Pokaż pełny błąd z debug info
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 15),
        ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
})->name('diagnostics.render.debug');

// Sprawdź kolumny w project_removals
Route::get('/diagnostics/check-removals-columns', function () {
    try {
        $columns = Schema::getColumnListing('project_removals');
        
        return response()->json([
            'table_exists' => Schema::hasTable('project_removals'),
            'columns' => $columns,
            'has_authorized' => in_array('authorized', $columns),
            'migration_in_db' => \DB::table('migrations')
                ->where('migration', '2026_02_05_224725_add_authorized_to_removals_table')
                ->exists(),
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('diagnostics.check.removals.columns');

// Napraw brakującą kolumnę authorized
Route::get('/diagnostics/fix-authorized-column', function () {
    try {
        if (!Schema::hasColumn('project_removals', 'authorized')) {
            Schema::table('project_removals', function (Blueprint $table) {
                $table->boolean('authorized')->default(true)->after('status');
            });
            
            return response()->json([
                'status' => '✅ Kolumna authorized została dodana',
                'columns_after' => Schema::getColumnListing('project_removals'),
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'status' => 'ℹ️ Kolumna authorized już istnieje',
                'columns' => Schema::getColumnListing('project_removals'),
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
        ], 500, [], JSON_PRETTY_PRINT);
    }
})->name('diagnostics.fix.authorized.column');

Route::get('/diagnostics/db', function () {
    return view('diagnostics.db');
});

Route::get('/diagnostics/db-status-json', function () {
    try {
        $type = config('database.default');
        $table = DB::select("SELECT name FROM sqlite_master WHERE type='table' LIMIT 1");
        $tableName = $table[0]->name ?? '-';
        $users = DB::table('users')->count();
        return [
            'status' => 'OK',
            'type' => $type,
            'table' => $tableName,
            'users' => $users,
        ];
    } catch (Exception $e) {
        return [
            'status' => 'Błąd: ' . $e->getMessage(),
            'type' => config('database.default'),
            'table' => '-',
            'users' => '-',
        ];
    }
});

Route::get('/diagnostics/migrations', function () {
    $migrationsPath = database_path('migrations');
    $files = File::files($migrationsPath);
    $fileNames = collect($files)->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))->toArray();
    
    $dbMigrations = DB::table('migrations')->pluck('migration')->toArray();
    
    $orphaned = array_diff($dbMigrations, $fileNames);
    $pending = array_diff($fileNames, $dbMigrations);
    
    return view('diagnostics.migrations', compact('orphaned', 'pending', 'dbMigrations', 'fileNames'));
});

Route::post('/diagnostics/migrations/clean', function () {
    $migrationsPath = database_path('migrations');
    $files = File::files($migrationsPath);
    $fileNames = collect($files)->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))->toArray();
    
    $dbMigrations = DB::table('migrations')->pluck('migration')->toArray();
    $orphaned = array_diff($dbMigrations, $fileNames);
    
    if (!empty($orphaned)) {
        DB::table('migrations')->whereIn('migration', $orphaned)->delete();
        return response()->json(['success' => true, 'deleted' => count($orphaned), 'migrations' => $orphaned]);
    }
    
    return response()->json(['success' => false, 'message' => 'Brak osieroconych migracji']);
});

// DIAGNOSTYKA: Sprawdź ustawienia kodów QR/Barcode
Route::get('/diagnostics/qr-settings', function () {
    $qrSettings = DB::table('qr_settings')->first();
    
    $diagnostics = [
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'settings_exist' => $qrSettings !== null,
        'code_type' => $qrSettings->code_type ?? 'BRAK',
        'qr_enabled' => $qrSettings->qr_enabled ?? false,
        'generation_mode' => $qrSettings->generation_mode ?? 'BRAK',
        'all_settings' => $qrSettings,
    ];
    
    // Test generowania kodu
    $testCode = 'TEST123';
    $testResults = [];
    
    try {
        // Test kodu QR
        $qrImage = \QrCode::format('svg')->size(100)->generate($testCode);
        $testResults['qr_generation'] = '✅ OK';
    } catch (\Exception $e) {
        $testResults['qr_generation'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    try {
        // Test kodu kreskowego
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $barcodeImage = $generator->getBarcode($testCode, $generator::TYPE_CODE_128, 2, 50);
        $testResults['barcode_generation'] = '✅ OK';
    } catch (\Exception $e) {
        $testResults['barcode_generation'] = '❌ BŁĄD: ' . $e->getMessage();
    }
    
    $html = '<!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <title>Diagnostyka Kodów QR/Barcode</title>
        <style>
            body { font-family: monospace; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
            h2 { color: #555; margin-top: 30px; }
            .info { background: #e9ecef; padding: 15px; border-radius: 4px; margin: 10px 0; }
            .success { color: #28a745; }
            .error { color: #dc3545; }
            .warning { color: #ffc107; }
            pre { background: #f8f9fa; padding: 10px; border-left: 3px solid #007bff; overflow-x: auto; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
            th { background: #007bff; color: white; }
            tr:nth-child(even) { background: #f8f9fa; }
            .code-display { display: inline-block; padding: 20px; background: white; border: 2px solid #ddd; margin: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔍 Diagnostyka Kodów QR/Barcode</h1>
            
            <div class="info">
                <strong>Czas sprawdzenia:</strong> ' . $diagnostics['timestamp'] . '
            </div>
            
            <h2>📊 Aktualne Ustawienia</h2>
            <table>
                <tr>
                    <th>Parametr</th>
                    <th>Wartość</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td><strong>Ustawienia istnieją</strong></td>
                    <td>' . ($diagnostics['settings_exist'] ? 'TAK' : 'NIE') . '</td>
                    <td class="' . ($diagnostics['settings_exist'] ? 'success' : 'error') . '">' . ($diagnostics['settings_exist'] ? '✅' : '❌') . '</td>
                </tr>
                <tr>
                    <td><strong>Typ kodu (code_type)</strong></td>
                    <td><strong>' . strtoupper($diagnostics['code_type']) . '</strong></td>
                    <td class="' . ($diagnostics['code_type'] !== 'BRAK' ? 'success' : 'error') . '">' . ($diagnostics['code_type'] !== 'BRAK' ? '✅' : '❌') . '</td>
                </tr>
                <tr>
                    <td><strong>Kody włączone (qr_enabled)</strong></td>
                    <td>' . ($diagnostics['qr_enabled'] ? 'TAK' : 'NIE') . '</td>
                    <td class="' . ($diagnostics['qr_enabled'] ? 'success' : 'warning') . '">' . ($diagnostics['qr_enabled'] ? '✅' : '⚠️') . '</td>
                </tr>
                <tr>
                    <td><strong>Tryb generowania</strong></td>
                    <td>' . $diagnostics['generation_mode'] . '</td>
                    <td class="success">ℹ️</td>
                </tr>
            </table>
            
            <h2>🧪 Test Generowania</h2>
            <table>
                <tr>
                    <th>Typ kodu</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>Kod QR</td>
                    <td>' . $testResults['qr_generation'] . '</td>
                </tr>
                <tr>
                    <td>Kod kreskowy</td>
                    <td>' . $testResults['barcode_generation'] . '</td>
                </tr>
            </table>
            
            <h2>🎨 Przykłady Wygenerowanych Kodów</h2>
            <p><strong>Kod testowy:</strong> ' . $testCode . '</p>
            
            <div style="display: flex; justify-content: space-around; flex-wrap: wrap;">
                <div class="code-display">
                    <h3 style="text-align: center;">📱 Kod QR</h3>
                    ' . (isset($qrImage) ? $qrImage : 'Błąd generowania') . '
                </div>
                <div class="code-display">
                    <h3 style="text-align: center;">📦 Kod Kreskowy</h3>
                    ' . (isset($barcodeImage) ? $barcodeImage : 'Błąd generowania') . '
                </div>
            </div>
            
            <h2>📝 Pełne Ustawienia (JSON)</h2>
            <pre>' . json_encode($diagnostics['all_settings'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>
            
            <div class="info" style="margin-top: 30px;">
                <strong>ℹ️ Informacje:</strong>
                <ul>
                    <li>Jeśli <code>code_type</code> = <strong>qr</strong> → system generuje kody QR</li>
                    <li>Jeśli <code>code_type</code> = <strong>barcode</strong> → system generuje kody kreskowe</li>
                    <li>Ustawienia można zmienić w: <strong>Menu → Ustawienia → Inne → Ustawienia Kodów QR</strong></li>
                </ul>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="/magazyn/ustawienia" style="display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                    ⚙️ Przejdź do Ustawień
                </a>
                <a href="/diagnostics/project-check" style="display: inline-block; padding: 12px 24px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">
                    🔍 Inne Diagnostyki
                </a>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
});

// CRM Live Test - wykonuje INSERT/UPDATE na żywo i pokazuje dokładne błędy
Route::get('/crm-live-test', function () {
    return view('crm-live-test');
})->name('diagnostics.crm-live-test');

// CRM Deal Update Test - testuje walidację updateDeal() i pokazuje dostępne slugi
Route::get('/crm-deal-test', function () {
    return view('crm-deal-test');
})->name('diagnostics.crm-deal-test');

// CRM Real Update Test - WYKONUJE PRAWDZIWY UPDATE na szansie i łapie błąd
Route::get('/crm-real-update-test', function () {
    return view('crm-real-update-test');
})->name('diagnostics.crm-real-update-test');

// CRM Deals Structure - sprawdza typ kolumny 'stage' w crm_deals
Route::get('/crm-deals-structure', function () {
    return view('crm-deals-structure');
})->name('diagnostics.crm-deals-structure');

Route::get('/diagnostics/anomalies', function () {
    $checks = [];
    $anomalies = [];
    $queryErrors = [];

    $pushCheck = function (string $key, bool $ok, string $message, bool $critical = false) use (&$checks) {
        $checks[] = [
            'key' => $key,
            'ok' => $ok,
            'critical' => $critical,
            'message' => $message,
        ];
    };

    $pushAnomaly = function (string $title, string $details, string $severity = 'warning') use (&$anomalies) {
        $anomalies[] = [
            'title' => $title,
            'details' => $details,
            'severity' => $severity,
        ];
    };

    $hasTable = function (string $table) {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    };

    $hasColumn = function (string $table, string $column) use ($hasTable) {
        if (!$hasTable($table)) {
            return false;
        }

        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    };

    try {
        DB::connection()->getPdo();
        $pushCheck('db_connection', true, 'Połączenie z bazą działa.', true);
    } catch (\Throwable $e) {
        $pushCheck('db_connection', false, 'Brak połączenia z bazą: ' . $e->getMessage(), true);
        $pushAnomaly('Brak połączenia z bazą', $e->getMessage(), 'critical');
    }

    $requiredTables = [
        'projects' => true,
        'project_finance' => true,
        'project_removals' => true,
        'users' => true,
        'migrations' => true,
    ];

    foreach ($requiredTables as $table => $critical) {
        $exists = $hasTable($table);
        $pushCheck('table_' . $table, $exists, $exists ? "Tabela {$table} istnieje." : "Brak tabeli {$table}.", $critical);

        if (!$exists) {
            $pushAnomaly("Brak tabeli {$table}", 'Brak tej tabeli może powodować błędy 500 w szczegółach projektu.', $critical ? 'critical' : 'warning');
        }
    }

    $requiredColumns = [
        'projects' => [
            'visible_sections' => false,
            'responsible_user_id' => false,
        ],
        'project_finance' => [
            'category' => true,
            'status' => true,
            'supplier' => false,
            'document_number' => false,
            'description' => false,
            'payment_date' => false,
            'import_row_order' => false,
        ],
        'users' => [
            'can_import_project_costs_excel' => false,
        ],
    ];

    foreach ($requiredColumns as $table => $columns) {
        foreach ($columns as $column => $critical) {
            $exists = $hasColumn($table, $column);
            $pushCheck("column_{$table}_{$column}", $exists, $exists ? "Kolumna {$table}.{$column} istnieje." : "Brak kolumny {$table}.{$column}.", $critical);

            if (!$exists) {
                $pushAnomaly(
                    "Brak kolumny {$table}.{$column}",
                    'Aplikacja działa z fallbackiem, ale zalecane jest uruchomienie brakujących migracji.',
                    $critical ? 'critical' : 'warning'
                );
            }
        }
    }

    if ($hasTable('projects') && $hasTable('users') && $hasColumn('projects', 'responsible_user_id')) {
        try {
            $orphanResponsible = DB::table('projects')
                ->leftJoin('users', 'projects.responsible_user_id', '=', 'users.id')
                ->whereNotNull('projects.responsible_user_id')
                ->whereNull('users.id')
                ->count();

            if ($orphanResponsible > 0) {
                $pushAnomaly('Osieroceni użytkownicy odpowiedzialni', "{$orphanResponsible} projektów wskazuje na nieistniejącego użytkownika.", 'warning');
            }
        } catch (\Throwable $e) {
            $queryErrors[] = 'Błąd sprawdzania responsible_user_id: ' . $e->getMessage();
        }
    }

    if ($hasTable('project_finance') && $hasTable('projects')) {
        try {
            $orphanFinance = DB::table('project_finance')
                ->leftJoin('projects', 'project_finance.project_id', '=', 'projects.id')
                ->whereNull('projects.id')
                ->count();

            if ($orphanFinance > 0) {
                $pushAnomaly('Osierocone rekordy project_finance', "{$orphanFinance} rekordów finansowych nie ma istniejącego projektu.", 'warning');
            }
        } catch (\Throwable $e) {
            $queryErrors[] = 'Błąd sprawdzania osieroconych project_finance: ' . $e->getMessage();
        }
    }

    if ($hasTable('project_finance') && $hasColumn('project_finance', 'category') && $hasColumn('project_finance', 'date')) {
        try {
            $importsWithoutDate = DB::table('project_finance')
                ->where('category', 'excel_import')
                ->whereNull('date')
                ->count();

            if ($importsWithoutDate > 0) {
                $pushAnomaly('Importy kosztów bez daty', "{$importsWithoutDate} rekordów z category=excel_import ma pustą datę.", 'warning');
            }
        } catch (\Throwable $e) {
            $queryErrors[] = 'Błąd sprawdzania importów bez daty: ' . $e->getMessage();
        }
    }

    if ($hasTable('project_finance') && $hasColumn('project_finance', 'category') && $hasColumn('project_finance', 'status')) {
        try {
            $invalidIssuedStatus = DB::table('project_finance')
                ->where('category', 'issued_invoice')
                ->whereNotIn('status', ['paid', 'pending'])
                ->count();

            if ($invalidIssuedStatus > 0) {
                $pushAnomaly('Nieprawidłowe statusy faktur wystawionych', "{$invalidIssuedStatus} rekordów ma status inny niż paid/pending.", 'warning');
            }
        } catch (\Throwable $e) {
            $queryErrors[] = 'Błąd sprawdzania statusów issued_invoice: ' . $e->getMessage();
        }
    }

    $migrationStats = [
        'pending_count' => null,
        'orphaned_count' => null,
    ];
    if ($hasTable('migrations')) {
        try {
            $migrationFiles = File::files(database_path('migrations'));
            $fileNames = collect($migrationFiles)
                ->map(fn ($file) => pathinfo($file, PATHINFO_FILENAME))
                ->toArray();

            $dbMigrations = DB::table('migrations')->pluck('migration')->toArray();

            $pending = array_diff($fileNames, $dbMigrations);
            $orphaned = array_diff($dbMigrations, $fileNames);

            $migrationStats['pending_count'] = count($pending);
            $migrationStats['orphaned_count'] = count($orphaned);

            if (count($pending) > 0) {
                $pushAnomaly('Niewykonane migracje', count($pending) . ' migracji czeka na uruchomienie na tym środowisku.', 'critical');
            }

            if (count($orphaned) > 0) {
                $pushAnomaly('Migracje osierocone w tabeli migrations', count($orphaned) . ' wpisów migracji nie ma pliku w repozytorium.', 'warning');
            }
        } catch (\Throwable $e) {
            $queryErrors[] = 'Błąd analizy migracji: ' . $e->getMessage();
        }
    }

    $criticalCount = collect($anomalies)->where('severity', 'critical')->count();
    $warningCount = collect($anomalies)->where('severity', 'warning')->count();

    return view('diagnostics.anomalies', [
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'environment' => app()->environment(),
        'phpVersion' => PHP_VERSION,
        'laravelVersion' => app()->version(),
        'dbConnection' => config('database.default'),
        'checks' => $checks,
        'anomalies' => $anomalies,
        'queryErrors' => $queryErrors,
        'criticalCount' => $criticalCount,
        'warningCount' => $warningCount,
        'migrationStats' => $migrationStats,
    ]);
})->middleware('auth')->name('diagnostics.anomalies');

// ============================================================
// DIAGNOSTYKA POBIERANIA XLSX I WORD
// ============================================================

// Minimalny test pobierania XLSX (1 wiersz, bez maatwebsite/excel – bezpośrednio PhpSpreadsheet)
Route::get('/magazyn/sprawdz/test-minimal-xlsx', function () {
    if (!auth()->check()) return response()->json(['error' => 'Unauthorized'], 401);
    try {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return response()->json(['error' => 'Brak klasy PhpSpreadsheet'], 500);
        }
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nazwa');
        $sheet->setCellValue('B1', 'Ilość');
        $sheet->setCellValue('A2', 'Test produkt');
        $sheet->setCellValue('B2', 42);

        $tempDir = is_writable(sys_get_temp_dir()) ? sys_get_temp_dir() : storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);
        $file = tempnam($tempDir, 'xlsx_') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($file);

        return response()->download($file, 'test-minimal.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    } catch (\Throwable $e) {
        \Log::error('test-minimal-xlsx failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'error' => $e->getMessage(),
            'class' => get_class($e),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
        ], 500);
    }
})->middleware('auth')->name('diagnostics.test.minimal.xlsx');

// Minimalny test pobierania Word (1 akapit, bezpośrednio PhpWord)
Route::get('/magazyn/sprawdz/test-minimal-word', function () {
    if (!auth()->check()) return response()->json(['error' => 'Unauthorized'], 401);
    try {
        if (!class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
            return response()->json(['error' => 'Brak klasy PhpWord'], 500);
        }
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Test dokument Word – diagnostyka Railway');
        $section->addText('Środowisko: ' . app()->environment());
        $section->addText('Czas: ' . now()->toDateTimeString());

        $tempDir = is_writable(sys_get_temp_dir()) ? sys_get_temp_dir() : storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);
        $file = tempnam($tempDir, 'word_') . '.docx';

        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($file);

        return response()->download($file, 'test-minimal.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    } catch (\Throwable $e) {
        \Log::error('test-minimal-word failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'error' => $e->getMessage(),
            'class' => get_class($e),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
        ], 500);
    }
})->middleware('auth')->name('diagnostics.test.minimal.word');

// Diagnostyka generowania Word dokumentacji projektowej
Route::get('/api/diagnostics/test-word-doc/{project?}', function ($projectId = null) {
    if (!auth()->check()) return response()->json(['error' => 'Unauthorized'], 401);

    $info = [
        'php_version'        => PHP_VERSION,
        'os'                 => PHP_OS,
        'environment'        => app()->environment(),
        'extensions'         => [],
        'phpword_class'      => false,
        'zip_archive_class'  => false,
        'temp_dir'           => sys_get_temp_dir(),
        'temp_writable'      => is_writable(sys_get_temp_dir()),
        'storage_app_writable' => is_writable(storage_path('app')),
        'table_exists'       => false,
        'project_found'      => false,
        'doc_found'          => false,
        'generate_result'    => null,
        'error'              => null,
        'error_class'        => null,
        'error_file'         => null,
        'error_line'         => null,
        'error_trace'        => null,
    ];

    foreach (['zip', 'xml', 'xmlwriter', 'dom', 'gd', 'zlib'] as $ext) {
        $info['extensions'][$ext] = extension_loaded($ext);
    }

    $info['phpword_class']     = class_exists(\PhpOffice\PhpWord\PhpWord::class);
    $info['zip_archive_class'] = class_exists(\ZipArchive::class);

    try {
        $info['table_exists'] = \Schema::hasTable('project_documentations');
    } catch (\Throwable $e) {
        $info['table_exists'] = 'error: ' . $e->getMessage();
    }

    $project = null;
    if ($projectId) {
        $project = \App\Models\Project::find($projectId);
        $info['project_found'] = (bool) $project;
        if ($project && $info['table_exists'] === true) {
            $doc = \App\Models\ProjectDocumentation::where('project_id', $project->id)->first();
            $info['doc_found'] = (bool) $doc;
            if ($doc) {
                $info['doc_fields'] = array_keys($doc->toArray());
            }
        }
    } else {
        // Weź pierwszy projekt
        $project = \App\Models\Project::first();
        $info['project_found'] = (bool) $project;
        $info['project_id_used'] = $project?->id;
    }

    if (!$info['phpword_class']) {
        $info['error'] = 'Brak klasy PhpOffice\PhpWord\PhpWord — pakiet nie zainstalowany';
        return response()->json($info, 500);
    }
    if (!$info['zip_archive_class']) {
        $info['error'] = 'Brak klasy ZipArchive — rozszerzenie zip nie zainstalowane';
        return response()->json($info, 500);
    }

    if (!$project) {
        $info['error'] = 'Brak projektów w bazie — nie można przetestować';
        return response()->json($info);
    }

    // Testuj zapis do tmp
    try {
        $tmpDir = sys_get_temp_dir();
        if (!$tmpDir || !is_writable($tmpDir)) {
            $tmpDir = storage_path('app');
        }
        $testFile = $tmpDir . DIRECTORY_SEPARATOR . 'diag_word_test_' . time() . '.docx';
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Test diagnostyczny: ' . $project->name);
        $section->addText('Środowisko: ' . app()->environment());
        $section->addText('PHP: ' . PHP_VERSION);
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($testFile);
        $info['generate_result'] = 'OK — plik zapisany: ' . basename($testFile) . ' (' . filesize($testFile) . ' bytes)';
        @unlink($testFile);
    } catch (\Throwable $e) {
        $info['error']       = $e->getMessage();
        $info['error_class'] = get_class($e);
        $info['error_file']  = basename($e->getFile()) . ':' . $e->getLine();
        $info['error_line']  = $e->getLine();
        $info['error_trace'] = array_slice(explode("\n", $e->getTraceAsString()), 0, 10);
    }

    // Sprawdź dostęp do $doc->property (null safety test)
    if ($info['table_exists'] === true) {
        try {
            $doc = \App\Models\ProjectDocumentation::where('project_id', $project->id)->first();
            $nullSafeTest = [
                'tytul'  => $doc?->tytul ?? '(null)',
                'autor'  => $doc?->autor ?? '(null)',
                'branza' => $doc?->branza ?? '(null)',
            ];
            $info['null_safe_test'] = 'OK — ' . json_encode($nullSafeTest);
        } catch (\Throwable $e) {
            $info['null_safe_test'] = 'ERROR: ' . $e->getMessage();
        }
    }

    return response()->json($info, $info['error'] ? 500 : 200);
})->middleware('auth');

// Pełna diagnostyka ścieżki generowania Word dla konkretnego projektu
Route::get('/api/diagnostics/trace-word/{projectId}', function ($projectId) {
    if (!auth()->check()) return response()->json(['error' => 'Unauthorized'], 401);
    $trace = [];

    try {
        $project = \App\Models\Project::find($projectId);
        if (!$project) return response()->json(['error' => "Projekt $projectId nie znaleziony"], 404);
        $trace['project'] = ['id' => $project->id, 'name' => $project->name];

        if (!\Schema::hasTable('project_documentations')) {
            return response()->json(['error' => 'Brak tabeli project_documentations'] + ['trace' => $trace]);
        }

        $doc = \App\Models\ProjectDocumentation::where('project_id', $project->id)->first();
        $trace['doc_found'] = (bool) $doc;
        if ($doc) {
            $trace['doc_id']            = $doc->id;
            $trace['doc_tytul']         = $doc->tytul;
            $trace['doc_template_path'] = $doc->template_path;
        }

        $useTemplate = false;
        if ($doc && $doc->template_path) {
            try {
                $exists = \Storage::exists($doc->template_path);
                $trace['storage_exists'] = $exists;
                $useTemplate = $exists;
                if ($exists) {
                    $realPath = \Storage::path($doc->template_path);
                    $trace['storage_real_path']     = $realPath;
                    $trace['real_path_file_exists'] = file_exists($realPath);
                    $trace['real_path_readable']    = is_readable($realPath);
                }
            } catch (\Throwable $e) {
                $trace['storage_check_error'] = $e->getMessage();
                $useTemplate = false;
            }
        }

        $trace['use_template'] = $useTemplate;
        $trace['path'] = $useTemplate ? 'generateProjectDocWithTemplate' : 'generateProjectDocProgrammatic';

        if ($useTemplate) {
            // Testuj TemplateProcessor
            try {
                $realPath = \Storage::path($doc->template_path);
                $processor = new \PhpOffice\PhpWord\TemplateProcessor($realPath);
                $trace['template_processor'] = 'OK';
                $tmpDir = sys_get_temp_dir();
                if (!is_writable($tmpDir)) $tmpDir = storage_path('app');
                $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'trace_test_' . time() . '.docx';
                $processor->saveAs($tmpFile);
                $trace['template_save'] = 'OK — ' . filesize($tmpFile) . ' bytes';
                @unlink($tmpFile);
            } catch (\Throwable $e) {
                $trace['template_error'] = $e->getMessage();
                $trace['template_error_class'] = get_class($e);
                $trace['template_error_file'] = basename($e->getFile()) . ':' . $e->getLine();
            }
        } else {
            // Testuj generowanie programmatic — kroki
            try {
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $trace['step_phpword_init'] = 'OK';

                $phpWord->getSettings()->setUpdateFields(true);
                $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 13], ['spaceAfter' => 120]);
                $section = $phpWord->addSection(['paperSize' => 'A4', 'marginLeft' => 1418, 'marginRight' => 1134]);
                $trace['step_section'] = 'OK';

                // Header/Footer
                $header = $section->addHeader();
                $trace['step_header'] = 'OK';

                $companySettings = \App\Models\CompanySetting::first();
                $trace['step_company_settings'] = $companySettings ? 'found' : 'null';

                $footer = $section->addFooter();
                $fTable = $footer->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF']);
                $fTable->addRow();
                $fTable->addCell(7000)->addText('Test footer', ['size' => 8]);
                $trace['step_footer'] = 'OK';

                // Relationships
                try {
                    $crmClient = $project->crmCompany;
                    $trace['step_crmCompany'] = $crmClient ? 'found id=' . $crmClient->id : 'null';
                } catch (\Throwable $e) {
                    $trace['step_crmCompany_error'] = $e->getMessage();
                }
                try {
                    $offer = $project->sourceOffer;
                    $trace['step_sourceOffer'] = $offer ? 'found id=' . $offer->id : 'null';
                } catch (\Throwable $e) {
                    $trace['step_sourceOffer_error'] = $e->getMessage();
                }

                $section->addText(htmlspecialchars($doc?->tytul ?? 'TEST', ENT_QUOTES, 'UTF-8'), ['bold' => true, 'size' => 20]);
                $trace['step_title_text'] = 'OK';

                // Tables
                $t1 = $section->addTable(['borderSize' => 6, 'borderColor' => 'E5E7EB', 'cellMargin' => 100]);
                $row = $t1->addRow();
                $row->addCell(2800)->addText('Test label', ['bold' => true, 'size' => 10]);
                $row->addCell(6000)->addText('Test value', ['size' => 10]);
                $trace['step_tables'] = 'OK';

                // Save
                $tmpDir = sys_get_temp_dir();
                if (!is_writable($tmpDir)) $tmpDir = storage_path('app');
                $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'trace_prog_' . time() . '.docx';
                \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($tmpFile);
                $trace['step_save'] = 'OK — ' . filesize($tmpFile) . ' bytes';
                @unlink($tmpFile);
            } catch (\Throwable $e) {
                $trace['step_error'] = $e->getMessage();
                $trace['step_error_class'] = get_class($e);
                $trace['step_error_file'] = basename($e->getFile()) . ':' . $e->getLine();
                $trace['step_error_trace'] = array_slice(explode("\n", $e->getTraceAsString()), 0, 12);
            }
        }
    } catch (\Throwable $e) {
        $trace['fatal_error'] = $e->getMessage();
        $trace['fatal_class']  = get_class($e);
        $trace['fatal_file']   = basename($e->getFile()) . ':' . $e->getLine();
    }

    return response()->json($trace);
})->middleware('auth');

// Probe JSON: sprawdز pełny stack XLSX (1 produkt z bazy) i zwróć szczegóły błędu
Route::get('/api/diagnostics/probe-xlsx', function () {
    if (!auth()->check()) return response()->json(['error' => 'Unauthorized'], 401);
    $info = [
        'php_version' => PHP_VERSION,
        'environment' => app()->environment(),
        'extensions' => [],
        'classes' => [],
        'temp_dir' => null,
        'temp_writable' => null,
        'parts_count' => null,
        'excel_raw_1part_bytes' => null,
        'excel_raw_allparts_bytes' => null,
        'error' => null,
    ];
    foreach (['zip','xml','xmlwriter','dom','gd'] as $ext) {
        $info['extensions'][$ext] = extension_loaded($ext);
    }
    foreach ([
        'PhpOffice\\PhpSpreadsheet\\Spreadsheet' => 'PhpSpreadsheet',
        'Maatwebsite\\Excel\\Facades\\Excel' => 'Excel facade',
        'App\\Exports\\PartsExport' => 'PartsExport',
    ] as $class => $label) {
        $info['classes'][$label] = class_exists($class);
    }
    $tempDir = is_writable(sys_get_temp_dir()) ? sys_get_temp_dir() : storage_path('app/temp');
    $info['temp_dir'] = $tempDir;
    $info['temp_writable'] = is_writable($tempDir);

    try {
        $allParts = \App\Models\Part::with(['category','lastModifiedBy'])->get();
        $info['parts_count'] = $allParts->count();
        if ($allParts->isEmpty()) {
            $info['error'] = 'Brak produktów w bazie – nie można przetestować';
            return response()->json($info, 422);
        }
        $firstPart = $allParts->first();
        $info['tested_first_part'] = $firstPart->name;

        // Próba via Excel::raw z 1 produktem
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class) && class_exists(\App\Exports\PartsExport::class)) {
            $raw1 = \Maatwebsite\Excel\Facades\Excel::raw(
                new \App\Exports\PartsExport(collect([$firstPart])),
                \Maatwebsite\Excel\Excel::XLSX
            );
            $info['excel_raw_1part_bytes'] = is_string($raw1) ? strlen($raw1) : 0;

            // Próba via Excel::raw ze wszystkimi produktami (dokładnie jak exportXlsx)
            $rawAll = \Maatwebsite\Excel\Facades\Excel::raw(
                new \App\Exports\PartsExport($allParts),
                \Maatwebsite\Excel\Excel::XLSX
            );
            $info['excel_raw_allparts_bytes'] = is_string($rawAll) ? strlen($rawAll) : 0;
        }

        // Próba via PhpSpreadsheet bezpośrednio
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nazwa');
        $sheet->setCellValue('A2', $firstPart->name ?? 'brak');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmpFile = tempnam($tempDir, 'probe_xlsx_') . '.xlsx';
        $writer->save($tmpFile);
        $info['direct_phpspreadsheet_bytes'] = file_exists($tmpFile) ? filesize($tmpFile) : 0;
        @unlink($tmpFile);

        $info['success'] = true;
        return response()->json($info, 200);
    } catch (\Throwable $e) {
        $info['error'] = $e->getMessage();
        $info['error_class'] = get_class($e);
        $info['error_file'] = $e->getFile();
        $info['error_line'] = $e->getLine();
        $info['error_trace'] = array_slice(explode("\n", $e->getTraceAsString()), 0, 20);
        \Log::error('probe-xlsx failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json($info, 500);
    }
})->middleware('auth')->name('api.diagnostics.probe-xlsx');

// Probe JSON: sprawdź pełny stack Word (1 produkt z bazy) i zwróć szczegóły błędu
Route::get('/api/diagnostics/probe-word', function () {
    if (!auth()->check()) return response()->json(['error' => 'Unauthorized'], 401);
    $info = [
        'php_version' => PHP_VERSION,
        'environment' => app()->environment(),
        'extensions' => [],
        'classes' => [],
        'temp_dir' => null,
        'temp_writable' => null,
        'phpword_create' => null,
        'phpword_save_bytes' => null,
        'error' => null,
    ];
    foreach (['zip','xml','gd'] as $ext) {
        $info['extensions'][$ext] = extension_loaded($ext);
    }
    foreach ([
        'PhpOffice\\PhpWord\\PhpWord' => 'PhpWord',
        'PhpOffice\\PhpWord\\IOFactory' => 'PhpWord IOFactory',
    ] as $class => $label) {
        $info['classes'][$label] = class_exists($class);
    }
    $tempDir = is_writable(sys_get_temp_dir()) ? sys_get_temp_dir() : storage_path('app/temp');
    $info['temp_dir'] = $tempDir;
    $info['temp_writable'] = is_writable($tempDir);

    try {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $info['phpword_create'] = true;
        $section = $phpWord->addSection();
        $section->addText('Diagnostyka – test generowania Word');
        $section->addText('Środowisko: ' . app()->environment());

        $tmpFile = tempnam($tempDir, 'probe_word_') . '.docx';
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($tmpFile);
        $info['phpword_save_bytes'] = file_exists($tmpFile) ? filesize($tmpFile) : 0;
        @unlink($tmpFile);

        $info['success'] = true;
        return response()->json($info, 200);
    } catch (\Throwable $e) {
        $info['error'] = $e->getMessage();
        $info['error_class'] = get_class($e);
        $info['error_file'] = $e->getFile();
        $info['error_line'] = $e->getLine();
        $info['error_trace'] = array_slice(explode("\n", $e->getTraceAsString()), 0, 20);
        \Log::error('probe-word failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json($info, 500);
    }
})->middleware('auth')->name('api.diagnostics.probe-word');
