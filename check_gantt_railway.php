<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ZADANIA GANTTA NA RAILWAY ===\n\n";

try {
    $tasks = \App\Models\GanttTask::with('project')->get();
    
    if ($tasks->count() === 0) {
        echo "❌ Brak zadań Gantta w bazie danych Railway.\n";
        echo "\nSprawdzam projekty:\n";
        $projects = \App\Models\Project::all();
        echo "Liczba projektów: {$projects->count()}\n";
        foreach ($projects->take(5) as $project) {
            echo "  - Projekt #{$project->id}: {$project->name}\n";
        }
    } else {
        echo "✅ Znaleziono {$tasks->count()} zadań:\n\n";
        foreach ($tasks as $task) {
            echo "─────────────────────────────────────\n";
            echo "ID: {$task->id}\n";
            echo "Nazwa: {$task->name}\n";
            echo "Projekt: #{$task->project_id} - {$task->project->name}\n";
            echo "Data: {$task->start} → {$task->end}\n";
            echo "Postęp: {$task->progress}%, Kolejność: {$task->order}\n";
            if ($task->dependencies) {
                echo "Zależności: {$task->dependencies}\n";
            }
        }
    }
    
    echo "\n=== PROJEKTY Z ZADANIAMI GANTTA ===\n\n";
    $projects = \App\Models\Project::has('ganttTasks')->with('ganttTasks')->get();
    if ($projects->count() === 0) {
        echo "❌ Żaden projekt nie ma zadań Gantta.\n";
    } else {
        foreach ($projects as $project) {
            echo "Projekt #{$project->id}: {$project->name} - {$project->ganttTasks->count()} zadań\n";
        }
    }
    
    echo "\n=== LOGI ZMIAN GANTTA ===\n\n";
    $changes = \App\Models\GanttChange::with(['user', 'project'])->orderByDesc('created_at')->take(10)->get();
    if ($changes->count() > 0) {
        echo "Ostatnie {$changes->count()} zmian:\n";
        foreach ($changes as $change) {
            $userName = $change->user ? $change->user->name : 'nieznany';
            $projectName = $change->project ? $change->project->name : 'nieznany';
            echo "  [{$change->created_at}] {$change->action} - {$change->task_name} (Projekt: {$projectName}) przez {$userName}\n";
            if ($change->details) {
                echo "    Details: {$change->details}\n";
            }
        }
    } else {
        echo "Brak logów zmian Gantta.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ BŁĄD: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
