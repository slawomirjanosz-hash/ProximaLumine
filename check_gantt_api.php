<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\GanttTask;
use App\Models\GanttChange;
use App\Models\Project;

echo "=== DIAGNOSTYKA API GANTTA ===\n\n";

// 1. Sprawdź czy są jakieś zadania w bazie
$tasksCount = GanttTask::count();
echo "Liczba zadań Gantta w bazie: {$tasksCount}\n";

if ($tasksCount > 0) {
    echo "\n📋 Lista zadań:\n";
    GanttTask::with('project')->orderBy('project_id')->orderBy('order')->get()->each(function($task) {
        echo "  #{$task->id} - {$task->name} (Projekt: {$task->project->name}, Kolejność: {$task->order})\n";
    });
}

// 2. Sprawdź logi zmian
echo "\n=== OSTATNIE 20 LOGÓW ZMIAN GANTTA ===\n";
$recentChanges = GanttChange::with(['project', 'user'])
    ->orderByDesc('created_at')
    ->take(20)
    ->get();

if ($recentChanges->count() > 0) {
    foreach ($recentChanges as $change) {
        $projectName = $change->project ? $change->project->name : 'USUNIĘTY';
        $userName = $change->user ? $change->user->name : 'NIEZNANY';
        echo "[{$change->created_at}] {$change->action} - '{$change->task_name}' (Projekt: {$projectName}) przez {$userName}\n";
        if ($change->details) {
            echo "  └─ {$change->details}\n";
        }
    }
} else {
    echo "Brak logów zmian.\n";
}

// 3. Sprawdź które projekty mają zadania Gantta
echo "\n=== PROJEKTY Z ZADANIAMI GANTTA ===\n";
$projectsWithTasks = Project::has('ganttTasks')->withCount('ganttTasks')->get();
if ($projectsWithTasks->count() > 0) {
    foreach ($projectsWithTasks as $project) {
        echo "Projekt #{$project->id}: {$project->name} - {$project->gantt_tasks_count} zadań\n";
    }
} else {
    echo "Brak projektów z zadaniami Gantta.\n";
}

// 4. Sprawdź czy są zduplikowane kolejności
echo "\n=== SPRAWDZANIE DUPLIKATÓW KOLEJNOŚCI ===\n";
$projects = Project::has('ganttTasks')->get();
foreach ($projects as $project) {
    $tasks = $project->ganttTasks;
    $orders = $tasks->pluck('order')->toArray();
    $duplicates = array_diff_assoc($orders, array_unique($orders));
    
    if (!empty($duplicates)) {
        echo "⚠️ Projekt #{$project->id} ({$project->name}) ma zduplikowane kolejności: " . implode(', ', $duplicates) . "\n";
    } else {
        echo "✅ Projekt #{$project->id} ({$project->name}) - brak duplikatów\n";
    }
}

echo "\n=== KONIEC DIAGNOSTYKI ===\n";
