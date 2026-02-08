<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

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
