<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostyka projektów - ProximaLumine</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">🔍 Diagnostyka projektów</h1>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">📊 Przegląd wszystkich projektów</h2>
        
        @php
            $projects = \App\Models\Project::with(['responsibleUser', 'removals.part', 'removals.user'])
                ->orderBy('created_at', 'desc')
                ->get();
        @endphp

        <table class="w-full border-collapse border text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Numer</th>
                    <th class="border p-2">Nazwa</th>
                    <th class="border p-2">Pobrań</th>
                    <th class="border p-2">Pobrań z NULL part</th>
                    <th class="border p-2">Odpowiedzialny</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Akcje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                    @php
                        $removalsCount = $project->removals->count();
                        $nullPartsCount = $project->removals->filter(fn($r) => $r->part === null)->count();
                        $hasNullParts = $nullPartsCount > 0;
                    @endphp
                    <tr class="{{ $hasNullParts ? 'bg-red-50' : '' }}">
                        <td class="border p-2 text-center">{{ $project->id }}</td>
                        <td class="border p-2 font-mono">{{ $project->project_number }}</td>
                        <td class="border p-2">
                            <strong>{{ $project->name }}</strong>
                            @if($hasNullParts)
                                <span class="ml-2 text-red-600 text-xs">⚠️ PROBLEM!</span>
                            @endif
                        </td>
                        <td class="border p-2 text-center">{{ $removalsCount }}</td>
                        <td class="border p-2 text-center {{ $hasNullParts ? 'bg-red-200 font-bold text-red-800' : 'text-green-600' }}">
                            {{ $nullPartsCount }}
                        </td>
                        <td class="border p-2">{{ $project->responsibleUser->name ?? '-' }}</td>
                        <td class="border p-2">
                            @if($project->status === 'in_progress')
                                <span class="text-blue-600">W toku</span>
                            @elseif($project->status === 'warranty')
                                <span class="text-green-600">Gwarancja</span>
                            @else
                                <span class="text-gray-600">{{ $project->status }}</span>
                            @endif
                        </td>
                        <td class="border p-2">
                            <a href="{{ route('diagnostics.project.details', $project->id) }}" 
                               class="text-blue-600 hover:underline text-xs">
                                🔍 Szczegóły
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-3">✅ Projekty bez problemów</h3>
            <p class="text-3xl font-bold text-green-600">
                {{ $projects->filter(fn($p) => $p->removals->filter(fn($r) => $r->part === null)->count() === 0)->count() }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-3">⚠️ Projekty z problemami</h3>
            <p class="text-3xl font-bold text-red-600">
                {{ $projects->filter(fn($p) => $p->removals->filter(fn($r) => $r->part === null)->count() > 0)->count() }}
            </p>
        </div>
    </div>

    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <h3 class="font-bold mb-2">ℹ️ Co oznaczają problemy?</h3>
        <p class="text-sm">Projekty z "Pobrań z NULL part > 0" mają w historii produkty, które zostały usunięte z katalogu. 
           To może powodować błąd 500 przy próbie wyświetlenia szczegółów projektu.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-3">🛠️ Dostępne narzędzia naprawcze</h3>
        <div class="space-y-2">
            <a href="{{ route('diagnostics.project.fix-all') }}" 
               class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
               onclick="return confirm('Czy na pewno chcesz naprawić wszystkie problematyczne pobrania? Operacja usunie rekordy z NULL part.')">
                🔧 Napraw wszystkie projekty
            </a>
            <p class="text-xs text-gray-600">
                Usuwa wszystkie rekordy project_removals, które wskazują na nieistniejące produkty
            </p>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('diagnostics.index') }}" class="text-blue-600 hover:underline">
            ← Powrót do diagnostyki głównej
        </a>
    </div>
</div>

</body>
</html>
