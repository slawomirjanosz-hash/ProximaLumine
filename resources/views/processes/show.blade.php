<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $process->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">{{ $process->name }}</h1>
                <a href="{{ route('processes.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    ← Lista Procesów
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Informacje podstawowe -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">📋 Informacje o Procesie</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Receptura</p>
                        <p class="font-semibold">{{ $process->recipe->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Ilość do produkcji</p>
                        <p class="font-semibold text-blue-600">{{ $process->quantity }} szt.</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Całkowity koszt</p>
                        <p class="font-semibold text-green-600">{{ number_format($process->total_cost, 2) }} zł</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Receptura bazowa</p>
                        <p class="font-semibold">{{ $process->recipe->output_quantity }} szt.</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Współczynnik skalowania</p>
                        <p class="font-semibold text-purple-600">{{ number_format($scaledIngredients['scaleFactor'], 2) }}x</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Data utworzenia</p>
                        <p class="font-semibold">{{ $process->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
                
                @if($process->notes)
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-gray-600 mb-1">Notatki:</p>
                        <p class="text-gray-800">{{ $process->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Mąka -->
            @if($scaledIngredients['flour']->isNotEmpty())
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-amber-600 text-white px-6 py-3">
                        <h2 class="text-xl font-bold">🌾 Mąka</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-amber-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Składnik</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Oryginalna waga</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Przeskalowana waga</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">%</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Koszt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scaledIngredients['flour'] as $item)
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="px-4 py-3">{{ $item['name'] }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($item['original_weight'], 3) }} {{ $item['unit'] }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-blue-600">{{ number_format($item['scaled_weight'], 3) }} {{ $item['unit'] }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($item['percentage'], 2) }}%</td>
                                        <td class="px-4 py-3 text-right text-green-600">{{ number_format($item['cost'], 2) }} zł</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-amber-100 font-semibold">
                                <tr>
                                    <td class="px-4 py-3">SUMA</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($scaledIngredients['flour']->sum('original_weight'), 3) }} kg</td>
                                    <td class="px-4 py-3 text-right text-blue-600">{{ number_format($scaledIngredients['flour']->sum('scaled_weight'), 3) }} kg</td>
                                    <td class="px-4 py-3 text-right">100%</td>
                                    <td class="px-4 py-3 text-right text-green-600">{{ number_format($scaledIngredients['flour']->sum('cost'), 2) }} zł</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Pozostałe składniki -->
            @if($scaledIngredients['ingredients']->isNotEmpty())
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-green-600 text-white px-6 py-3">
                        <h2 class="text-xl font-bold">🥄 Pozostałe Składniki</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Składnik</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Oryginalna ilość</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Przeskalowana ilość</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">%</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Koszt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scaledIngredients['ingredients'] as $item)
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="px-4 py-3">{{ $item['name'] }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($item['original_quantity'], 3) }} {{ $item['unit'] }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-blue-600">{{ number_format($item['scaled_quantity'], 3) }} {{ $item['unit'] }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($item['percentage'], 2) }}%</td>
                                        <td class="px-4 py-3 text-right text-green-600">{{ number_format($item['cost'], 2) }} zł</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-green-100 font-semibold">
                                <tr>
                                    <td class="px-4 py-3" colspan="4">SUMA SKŁADNIKÓW</td>
                                    <td class="px-4 py-3 text-right text-green-600">{{ number_format($scaledIngredients['ingredients']->sum('cost'), 2) }} zł</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Kroki akcji -->
            @if($process->steps->isNotEmpty())
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-blue-600 text-white px-6 py-3">
                        <h2 class="text-xl font-bold">🔧 Kroki Realizacji</h2>
                    </div>
                    <div class="p-6">
                        @foreach($process->steps as $step)
                            <div class="mb-4 pb-4 {{ !$loop->last ? 'border-b' : '' }}">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold mr-3">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-800">{{ $step->action_name }}</h3>
                                        @if($step->action_name === 'Dodawanie' && $step->ingredients_data && is_array($step->ingredients_data) && count($step->ingredients_data) > 0)
                                            <div class="mt-2 pl-4 border-l-2 border-blue-300">
                                                <p class="text-sm font-medium text-gray-700 mb-1">Dodawane składniki:</p>
                                                <ul class="text-sm text-gray-600 space-y-1">
                                                    @foreach($step->ingredients_data as $ingredient)
                                                        <li>• {{ $ingredient['name'] ?? 'Nieznany' }} - {{ $ingredient['quantity_added'] ?? 0 }} {{ $ingredient['unit'] ?? '' }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        @if($step->action_description)
                                            <p class="text-gray-600 mt-1">{{ $step->action_description }}</p>
                                        @endif
                                        @if($step->duration)
                                            <p class="text-sm text-blue-600 mt-2">
                                                ⏱️ Czas: {{ gmdate('H:i:s', $step->duration) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Akcje -->
            <div class="flex gap-3 mb-6">
                <a href="{{ route('processes.edit', $process) }}" class="flex-1 text-center px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    ✏️ Edytuj Proces
                </a>
                <form action="{{ route('processes.start', $process) }}" method="POST" class="flex-1" onsubmit="event.preventDefault(); fetch(this.action, {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(r => r.json()).then(d => window.location.href = d.url);">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        ▶️ Realizuj proces
                    </button>
                </form>
                <button onclick="window.print()" class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    🖨️ Drukuj
                </button>
                <form action="{{ route('processes.destroy', $process) }}" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć ten proces?')" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        🗑️ Usuń Proces
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
