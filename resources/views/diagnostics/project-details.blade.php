<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szczegóły projektu {{ $project->name }} - Diagnostyka</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">🔍 Diagnostyka projektu: {{ $project->name }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-600 mb-2">ID projektu</h3>
            <p class="text-2xl font-bold">{{ $project->id }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-600 mb-2">Numer projektu</h3>
            <p class="text-2xl font-bold font-mono">{{ $project->project_number }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-600 mb-2">Status</h3>
            <p class="text-2xl font-bold">{{ $project->status }}</p>
        </div>
    </div>

    @php
        $removals = \App\Models\ProjectRemoval::where('project_id', $project->id)
            ->with(['part', 'user', 'returnedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $nullRemovals = $removals->filter(fn($r) => $r->part === null);
        $validRemovals = $removals->filter(fn($r) => $r->part !== null);
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 rounded-lg shadow p-4">
            <h3 class="font-semibold text-green-700 mb-2">✅ Prawidłowe pobrania</h3>
            <p class="text-3xl font-bold text-green-600">{{ $validRemovals->count() }}</p>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-4">
            <h3 class="font-semibold text-red-700 mb-2">⚠️ Problematyczne pobrania</h3>
            <p class="text-3xl font-bold text-red-600">{{ $nullRemovals->count() }}</p>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-4">
            <h3 class="font-semibold text-blue-700 mb-2">📊 Łącznie</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $removals->count() }}</p>
        </div>
    </div>

    @if($nullRemovals->count() > 0)
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
        <h3 class="font-bold text-red-800 mb-2">⚠️ ZNALEZIONO PROBLEM!</h3>
        <p class="text-sm text-red-700 mb-3">
            Ten projekt ma {{ $nullRemovals->count() }} pobrań wskazujących na usunięte produkty. 
            To powoduje błąd 500 przy próbie wyświetlenia szczegółów projektu.
        </p>
        <form method="POST" action="{{ route('diagnostics.project.fix', $project->id) }}" 
              onsubmit="return confirm('Czy na pewno chcesz usunąć problematyczne rekordy? Ta operacja jest nieodwracalna.')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                🔧 Napraw ten projekt (usuń problematyczne rekordy)
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4 text-red-700">⚠️ Problematyczne pobrania (NULL part)</h2>
        <table class="w-full border-collapse border text-sm">
            <thead class="bg-red-100">
                <tr>
                    <th class="border p-2">ID removal</th>
                    <th class="border p-2">part_id</th>
                    <th class="border p-2">Ilość</th>
                    <th class="border p-2">Data pobrania</th>
                    <th class="border p-2">Użytkownik</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Autoryzacja</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nullRemovals as $removal)
                <tr class="bg-red-50">
                    <td class="border p-2 text-center font-mono">{{ $removal->id }}</td>
                    <td class="border p-2 text-center font-mono text-red-600">
                        {{ $removal->part_id }} <span class="text-xs">(USUNIĘTY)</span>
                    </td>
                    <td class="border p-2 text-center">{{ $removal->quantity }}</td>
                    <td class="border p-2 text-center">{{ $removal->created_at->format('Y-m-d H:i') }}</td>
                    <td class="border p-2">{{ $removal->user->name ?? 'Brak' }}</td>
                    <td class="border p-2 text-center">{{ $removal->status }}</td>
                    <td class="border p-2 text-center">
                        @if($removal->authorized)
                            <span class="text-green-600">✅</span>
                        @else
                            <span class="text-red-600">❌</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
        <h3 class="font-bold text-green-800 mb-2">✅ BRAK PROBLEMÓW</h3>
        <p class="text-sm text-green-700">
            Wszystkie pobrania w tym projekcie wskazują na istniejące produkty. Projekt powinien działać poprawnie.
        </p>
    </div>
    @endif

    @if($validRemovals->count() > 0)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4 text-green-700">✅ Prawidłowe pobrania</h2>
        <table class="w-full border-collapse border text-sm">
            <thead class="bg-green-100">
                <tr>
                    <th class="border p-2">ID removal</th>
                    <th class="border p-2">Produkt</th>
                    <th class="border p-2">Kod QR</th>
                    <th class="border p-2">Ilość</th>
                    <th class="border p-2">Data</th>
                    <th class="border p-2">Użytkownik</th>
                    <th class="border p-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($validRemovals->take(10) as $removal)
                <tr>
                    <td class="border p-2 text-center font-mono">{{ $removal->id }}</td>
                    <td class="border p-2">{{ $removal->part->name }}</td>
                    <td class="border p-2 text-center font-mono text-xs">{{ $removal->part->qr_code ?? '-' }}</td>
                    <td class="border p-2 text-center">{{ $removal->quantity }}</td>
                    <td class="border p-2 text-center">{{ $removal->created_at->format('Y-m-d H:i') }}</td>
                    <td class="border p-2">{{ $removal->user->name ?? '-' }}</td>
                    <td class="border p-2 text-center">
                        @if($removal->status === 'returned')
                            <span class="text-green-600">Zwrócony</span>
                        @else
                            <span class="text-blue-600">Dodany</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                @if($validRemovals->count() > 10)
                <tr>
                    <td colspan="7" class="border p-2 text-center text-gray-500 text-xs">
                        ... i {{ $validRemovals->count() - 10 }} więcej prawidłowych rekordów
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-bold mb-3">🔧 SQL Query do ręcznej naprawy</h3>
        <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto">DELETE FROM project_removals 
WHERE project_id = {{ $project->id }} 
  AND part_id NOT IN (SELECT id FROM parts);</pre>
        <p class="text-xs text-gray-600 mt-2">
            Ta komenda usuwa wszystkie rekordy project_removals tego projektu, które wskazują na nieistniejące produkty.
        </p>
    </div>

    <div class="flex gap-4">
        <a href="{{ route('diagnostics.projects') }}" class="text-blue-600 hover:underline">
            ← Powrót do listy projektów
        </a>
        
        @if($nullRemovals->count() === 0)
        <a href="{{ route('magazyn.projects.show', $project->id) }}" class="text-green-600 hover:underline">
            👁️ Zobacz projekt w aplikacji
        </a>
        @else
        <span class="text-gray-400" title="Napraw projekt przed wyświetleniem">
            👁️ Zobacz projekt w aplikacji (niedostępne - napraw najpierw)
        </span>
        @endif
    </div>
</div>

</body>
</html>
