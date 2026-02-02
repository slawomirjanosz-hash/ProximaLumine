<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CleanOrphanedMigrations extends Command
{
    protected $signature = 'migrate:clean-orphaned';
    protected $description = 'Usuń wpisy z tabeli migrations dla nieistniejących plików migracji';

    public function handle()
    {
        $migrationsPath = database_path('migrations');
        $files = File::files($migrationsPath);
        $fileNames = collect($files)->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))->toArray();
        
        $dbMigrations = DB::table('migrations')->pluck('migration')->toArray();
        
        $orphaned = array_diff($dbMigrations, $fileNames);
        
        if (empty($orphaned)) {
            $this->info('✅ Brak osieroconych migracji w bazie.');
            return 0;
        }
        
        $this->warn('Znaleziono osierocone migracje:');
        foreach ($orphaned as $migration) {
            $this->line("  - $migration");
        }
        
        if ($this->confirm('Czy usunąć te wpisy z tabeli migrations?', true)) {
            DB::table('migrations')->whereIn('migration', $orphaned)->delete();
            $this->info('✅ Usunięto ' . count($orphaned) . ' osieroconych migracji.');
        } else {
            $this->info('Anulowano.');
        }
        
        return 0;
    }
}
