<?php

namespace App\Http\Controllers;

use App\Models\GanttTask;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GanttTaskController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może zobaczyć
        return response()->json($project->ganttTasks()->orderBy('order')->get());
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może dodać
        $data = $request->validate([
            'name' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date',
            'progress' => 'integer',
            'dependencies' => 'nullable|string',
            'order' => 'integer',
        ]);
        $data['project_id'] = $project->id;
        $task = GanttTask::create($data);
        return response()->json($task, 201);
    }

    public function update(Request $request, $projectId, $id)
    {
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może aktualizować
        $task = GanttTask::where('project_id', $project->id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'string',
            'start' => 'date',
            'end' => 'date',
            'progress' => 'integer',
            'dependencies' => 'nullable|string',
            'order' => 'integer',
        ]);
        $task->update($data);
        return response()->json($task);
    }

    public function destroy($projectId, $id)
    {
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może usuwać
        $task = GanttTask::where('project_id', $project->id)->findOrFail($id);
        $task->delete();
        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        // Brak autoryzacji - każdy zalogowany użytkownik może zmieniać kolejność
        $order = $request->input('order'); // array of task IDs in new order
        foreach ($order as $idx => $taskId) {
            GanttTask::where('project_id', $project->id)->where('id', $taskId)->update(['order' => $idx]);
        }
        return response()->json(['success' => true]);
    }
}
