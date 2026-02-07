<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

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
