<?php

namespace App\Http\Controllers;

use App\Models\GanttTask;
use App\Models\GanttChange;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GanttTaskController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może zobaczyć
        $tasks = $project->ganttTasks()->orderBy('order')->get()->values(); // ->values() resetuje klucze i zapewnia tablicę JSON
        \Log::info('📤 Gantt: Zwracam zadania dla projektu', [
            'project_id' => $projectId,
            'tasks_count' => $tasks->count()
        ]);
        return response()->json($tasks);
    }

    public function store(Request $request, $projectId)
    {
        \Log::info('🆕 Gantt: Próba utworzenia zadania', [
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'data' => $request->all()
        ]);
        
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może dodać
        
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'start' => 'required|date',
                'end' => 'required|date',
                'progress' => 'integer',
                'dependencies' => 'nullable|string',
                'order' => 'integer',
                'description' => 'nullable|string',
                'assignee' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Gantt: Błąd walidacji przy tworzeniu zadania', [
                'errors' => $e->errors()
            ]);
            throw $e;
        }
        
        $data['project_id'] = $project->id;
        $task = GanttTask::create($data);
        
        \Log::info('✅ Gantt: Utworzono zadanie', [
            'task_id' => $task->id,
            'task_name' => $task->name,
            'project_id' => $project->id
        ]);
        
        // Loguj zmianę
        GanttChange::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'action' => 'add',
            'task_name' => $data['name'],
            'details' => 'Dodano nowe zadanie',
        ]);
        
        return response()->json($task, 201);
    }

    public function update(Request $request, $projectId, $id)
    {
        \Log::info('📝 Gantt: Próba aktualizacji zadania', [
            'project_id' => $projectId,
            'task_id' => $id,
            'user_id' => Auth::id(),
            'data' => $request->all()
        ]);
        
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może aktualizować
        $task = GanttTask::where('project_id', $project->id)->findOrFail($id);
        
        try {
            $data = $request->validate([
                'name' => 'string',
                'start' => 'date',
                'end' => 'date',
                'progress' => 'integer',
                'dependencies' => 'nullable|string',
                'order' => 'integer',
                'description' => 'nullable|string',
                'assignee' => 'nullable|string|max:255',
                'completed_at' => 'nullable|date',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Gantt: Błąd walidacji przy aktualizacji zadania', [
                'errors' => $e->errors()
            ]);
            throw $e;
        }
        
        // Zbierz szczegóły zmian
        $details = [];
        foreach ($data as $key => $value) {
            if ($task->$key != $value) {
                $details[] = "$key: {$task->$key} → $value";
            }
        }
        
        // Auto-set/clear completed_at based on progress (unless admin explicitly passes completed_at)
        if (isset($data['progress']) && !array_key_exists('completed_at', $data)) {
            if ((int)$data['progress'] >= 100 && $task->completed_at === null) {
                $data['completed_at'] = now()->toDateString();
            } elseif ((int)$data['progress'] < 100) {
                $data['completed_at'] = null;
            }
        }

        $task->update($data);
        
        \Log::info('✅ Gantt: Zaktualizowano zadanie', [
            'task_id' => $task->id,
            'changes' => $details
        ]);
        
        // Loguj zmianę jeśli coś się zmieniło
        if (!empty($details)) {
            GanttChange::create([
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'action' => 'edit',
                'task_name' => $task->name,
                'details' => implode(', ', $details),
            ]);
        }
        
        return response()->json($task);
    }

    public function destroy($projectId, $id)
    {
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może usuwać
        $task = GanttTask::where('project_id', $project->id)->findOrFail($id);
        $taskName = $task->name;
        $task->delete();
        
        // Loguj zmianę
        GanttChange::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'action' => 'delete',
            'task_name' => $taskName,
            'details' => 'Usunięto zadanie',
        ]);
        
        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, $projectId)
    {
        \Log::info('🔄 Gantt: Próba zmiany kolejności zadań', [
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'order' => $request->input('order')
        ]);
        
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może zmieniać kolejność
        $order = $request->input('order'); // array of task IDs in new order
        
        if (!is_array($order)) {
            \Log::error('❌ Gantt: Nieprawidłowy format kolejności (nie jest tablicą)');
            return response()->json(['error' => 'Order must be an array'], 400);
        }
        
        $updated = 0;
        foreach ($order as $idx => $taskId) {
            $result = GanttTask::where('project_id', $project->id)
                ->where('id', $taskId)
                ->update(['order' => $idx]);
            $updated += $result;
        }
        
        \Log::info('✅ Gantt: Zmieniono kolejność zadań', [
            'updated_count' => $updated,
            'total_in_order' => count($order)
        ]);
        
        // Loguj zmianę kolejności
        GanttChange::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'action' => 'move',
            'task_name' => 'Wiele zadań',
            'details' => 'Zmieniono kolejność zadań',
        ]);
        
        return response()->json(['success' => true, 'updated' => $updated]);
    }

    public function publicIndex($token)
    {
        $project = Project::where('public_gantt_token', $token)->firstOrFail();
        $tasks = $project->ganttTasks()->orderBy('order')->get()->values(); // ->values() zapewnia tablicę JSON
        return response()->json($tasks);
    }
}
