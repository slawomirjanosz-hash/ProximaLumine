<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

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
