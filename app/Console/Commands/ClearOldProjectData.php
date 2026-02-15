<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearOldProjectData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:clear-old-data {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Czyści stare dane projektów (projects, project_parts, project_removals, project_tasks) - teraz używamy project_lists';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Czy na pewno chcesz usunąć wszystkie stare dane projektów? Ta operacja jest nieodwracalna!')) {
                $this->info('Operacja anulowana.');
                return 0;
            }
        }

        $this->info('Usuwam stare dane projektów...');

        try {
            DB::beginTransaction();

            // Usuń dane z tabel w odpowiedniej kolejności (najpierw zależne, potem główne)
            $deletedTasks = DB::table('project_tasks')->delete();
            $this->info("Usunięto {$deletedTasks} zadań projektowych");

            $deletedRemovals = DB::table('project_removals')->delete();
            $this->info("Usunięto {$deletedRemovals} pobrań z projektów");

            $deletedParts = DB::table('project_parts')->delete();
            $this->info("Usunięto {$deletedParts} przypisań części do projektów");

            $deletedProjects = DB::table('projects')->delete();
            $this->info("Usunięto {$deletedProjects} projektów");

            DB::commit();

            $this->info('✓ Wszystkie stare dane projektów zostały pomyślnie usunięte!');
            $this->info('Teraz używaj nowego systemu list projektowych (magazyn.projects.settings)');

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Błąd podczas usuwania danych: ' . $e->getMessage());
            return 1;
        }
    }
}
