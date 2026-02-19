<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ZADANIA GANTTA W BAZIE LOKALNEJ ===\n\n";
$tasks = \App\Models\GanttTask::with('project')->get();

if ($tasks->count() === 0) {
    echo "❌ Brak zadań Gantta w bazie danych.\n";
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
foreach ($projects as $project) {
    echo "Projekt #{$project->id}: {$project->name} - {$project->ganttTasks->count()} zadań\n";
}
