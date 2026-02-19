<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostyka wykresów Gantta</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">🔧 Diagnostyka wykresów Gantta</h1>
        
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
            <h2 class="text-lg font-semibold mb-2">📊 Informacje ogólne</h2>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li>Liczba wszystkich projektów: <strong>{{ \App\Models\Project::count() }}</strong></li>
                <li>Liczba projektów z zadaniami Gantta: <strong>{{ \App\Models\Project::has('ganttTasks')->count() }}</strong></li>
                <li>Łączna liczba zadań Gantta: <strong>{{ \App\Models\GanttTask::count() }}</strong></li>
                <li>Liczba logów zmian Gantta: <strong>{{ \App\Models\GanttChange::count() }}</strong></li>
            </ul>
        </div>

        @php
            $projectsWithGantt = \App\Models\Project::has('ganttTasks')->with(['ganttTasks', 'ganttChanges'])->get();
        @endphp

        @if($projectsWithGantt->count() > 0)
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4">📋 Projekty z wykresami Gantta</h2>
            
            @foreach($projectsWithGantt as $project)
            <div class="mb-4 p-4 border border-gray-200 rounded bg-gray-50">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-bold text-blue-600">
                            Projekt #{{ $project->id }}: {{ $project->name }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            Nr projektu: {{ $project->project_number }} | 
                            Status: {{ $project->status }}
                        </p>
                    </div>
                    <a href="{{ route('magazyn.projects.details', $project->id) }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Przejdź do projektu →
                    </a>
                </div>

                <div class="mt-3">
                    <h4 class="font-semibold mb-2">Zadania Gantta ({{ $project->ganttTasks->count() }}):</h4>
                    <div class="space-y-2">
                        @foreach($project->ganttTasks->sortBy('order') as $task)
                        <div class="p-3 bg-white border rounded flex items-center justify-between">
                            <div>
                                <span class="font-semibold">{{ $task->name }}</span>
                                <span class="ml-3 text-sm text-gray-600">
                                    {{ $task->start->format('Y-m-d') }} → {{ $task->end->format('Y-m-d') }}
                                </span>
                                <span class="ml-3 text-sm text-gray-500">Postęp: {{ $task->progress }}%</span>
                                @if($task->dependencies)
                                <span class="ml-3 text-xs text-purple-600">Zależność: {{ $task->dependencies }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400">
                                Kolejność: {{ $task->order }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($project->ganttChanges->count() > 0)
                <div class="mt-4">
                    <h4 class="font-semibold mb-2">Ostatnie zmiany ({{ $project->ganttChanges->count() }}):</h4>
                    <div class="max-h-40 overflow-y-auto bg-white p-2 rounded border text-xs">
                        @foreach($project->ganttChanges->sortByDesc('created_at')->take(10) as $change)
                        <div class="py-1 border-b">
                            <span class="text-gray-500">{{ $change->created_at->format('Y-m-d H:i') }}</span>
                            <span class="ml-2 font-semibold">{{ $change->action }}</span>
                            <span class="ml-2">{{ $change->task_name }}</span>
                            @if($change->user)
                            <span class="ml-2 text-gray-600">przez {{ $change->user->name }}</span>
                            @endif
                            @if($change->details)
                            <div class="ml-4 text-gray-500">{{ $change->details }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="p-6 bg-yellow-50 border border-yellow-200 rounded text-center">
            <div class="text-5xl mb-3">⚠️</div>
            <h3 class="text-lg font-semibold mb-2">Brak projektów z wykresami Gantta</h3>
            <p class="text-sm text-gray-600">Żaden projekt w bazie danych nie ma przypisanych zadań Gantta.</p>
        </div>
        @endif

        <div class="mt-6 p-4 bg-gray-50 border rounded">
            <h2 class="text-lg font-semibold mb-2">💡 Pomoc</h2>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                <li>Jeśli nie widzisz wykresu Gantta w projekcie, upewnij się że jesteś w projekcie który ma zadania (lista powyżej)</li>
                <li>Sprawdź konsolę przeglądarki (F12) aby zobaczyć szczegółowe logi ładowania danych</li>
                <li>Dane Gantta są zapisywane w bazie danych i NIE znikają po zamknięciu przeglądarki</li>
                <li>Jeśli widzisz logi zmian ale brak zadań - może to oznaczać, że zadania zostały usunięte</li>
                <li>W razie problemów skontaktuj się z administratorem systemu</li>
            </ul>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('magazyn.projects') }}" class="text-blue-600 hover:underline">← Powrót do listy projektów</a>
        </div>
    </div>
</body>
</html>
