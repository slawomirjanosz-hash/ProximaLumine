<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Szczegóły projektu - {{ $project->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

@include('parts.menu')

<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow mt-6">
    
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-xl font-bold mb-2">Szczegóły projektu</h2>
            <a href="{{ route('magazyn.projects') }}" class="text-blue-600 hover:underline">← Powrót do listy projektów</a>
        </div>
        
        {{-- DATY W PRAWYM GÓRNYM ROGU --}}
        <div class="text-right space-y-1">
            @if($project->started_at)
            <div class="text-sm">
                <span class="font-semibold text-gray-600">Data rozpoczęcia:</span>
                <span class="text-gray-800">{{ $project->started_at->format('d.m.Y') }}</span>
            </div>
            @endif
            @if($project->finished_at)
            <div class="text-sm">
                <span class="font-semibold text-gray-600">Data zakończenia:</span>
                <span class="text-gray-800">{{ $project->finished_at->format('d.m.Y') }}</span>
            </div>
            @endif
            @if($project->warranty_period)
            <div class="text-sm">
                <span class="font-semibold text-gray-600">Okres gwarancji:</span>
                <span class="text-gray-800">{{ $project->warranty_period }} miesięcy</span>
            </div>
            @endif
            @if($project->status === 'warranty' && $project->finished_at && $project->warranty_period)
            <div class="text-sm">
                <span class="font-semibold text-gray-600">Data zakończenia gwarancji:</span>
                <span class="text-gray-800">{{ $project->finished_at->addMonths($project->warranty_period)->format('d.m.Y') }}</span>
            </div>
            @endif
        </div>
    </div>
    
    {{-- INFORMACJE O PROJEKCIE --}}
    <div class="bg-gray-50 border rounded p-4 mb-6">
        <div class="flex flex-wrap items-center gap-6 mb-4">
            <div>
                <span class="text-sm font-semibold text-gray-600">Nr projektu:</span>
                <span class="text-lg ml-1">{{ $project->project_number }}</span>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-600">Nazwa:</span>
                <span class="text-lg ml-1">{{ $project->name }}</span>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-600">Budżet:</span>
                <span class="text-lg ml-1">{{ $project->budget ? number_format($project->budget, 2) . ' PLN' : '-' }}</span>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-600">Status:</span>
                <span class="text-lg ml-1">
                    @if($project->status === 'in_progress') W toku
                    @elseif($project->status === 'warranty') Na gwarancji
                    @elseif($project->status === 'archived') Archiwalny
                    @endif
                </span>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-600">Osoba odpowiedzialna:</span>
                <span class="text-lg ml-1">
                    @if(isset($project->responsibleUser) && $project->responsibleUser)
                        {{ $project->responsibleUser->name ?? ($project->responsibleUser->short_name ?? '-') }}
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-semibold text-gray-600">Autoryzacja pobrań:</span>
                @if($project->status === 'warranty')
                    <div class="flex items-center gap-2 mt-2">
                        <input type="checkbox" disabled {{ $project->requires_authorization ? 'checked' : '' }} class="w-4 h-4 cursor-not-allowed opacity-50">
                        <label class="text-sm font-medium text-gray-400">
                            Pobranie produktów wymaga autoryzacji przez skanowanie
                        </label>
                    </div>
                    <span class="text-orange-600 font-semibold">✓ Wymagana</span>
                    <p class="text-xs text-gray-400 mt-1">Projekt zamknięty – nie można zmienić autoryzacji.</p>
                @else
                    ...existing code...
                @endif
            </div>
        </div>
        
        {{-- INFORMACJA O ZAŁADOWANYCH LISTACH --}}
        @if($loadedLists->count() > 0)
            <div class="mt-4 space-y-2">
                <h4 class="text-sm font-bold text-gray-700">📋 Załadowane listy projektowe:</h4>
                @foreach($loadedLists as $loadedListData)
                    @php
                        $list = $loadedListData->projectList;
                    @endphp
                    <div class="p-3 rounded border {{ $loadedListData->is_complete ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold">{{ $list->name }}</span>
                            <span class="text-xs text-gray-600">({{ $loadedListData->added_count }} z {{ $loadedListData->total_count }})</span>
                            @if($loadedListData->is_complete)
                                <span class="ml-2 px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full font-semibold">✓ Kompletna</span>
                            @else
                                <span class="ml-2 px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs rounded-full font-semibold">⚠ Niekompletna</span>
                                <button type="button" class="ml-2 text-orange-600 hover:text-orange-800 font-bold text-xl" onclick="showMissingItems({{ $loadedListData->id }})" title="Kliknij aby zobaczyć czego brakuje">❗</button>
                            @endif
                            <span class="text-xs text-gray-500 ml-auto">{{ $loadedListData->created_at->format('d.m.Y H:i') }}</span>
                            @if(auth()->user() && auth()->user()->is_admin && !in_array($project->status, ['warranty','archived']))
                            <button type="button" class="ml-2 px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs font-semibold remove-list-btn"
                                    data-loaded-list-id="{{ $loadedListData->id }}"
                                    data-list-name="{{ $list->name }}"
                                    title="Usuń listę z projektu">
                                🗑️ Usuń
                            </button>
                            @endif
                        </div>
                        @if(!$loadedListData->is_complete && $loadedListData->missing_items)
                            <div id="missing-items-{{ $loadedListData->id }}" class="hidden mt-2 p-2 bg-white border border-yellow-300 rounded text-xs">
                                <strong class="text-red-600">Produkty nie dodane do projektu:</strong>
                                <div class="mt-2 space-y-2">
                                    @foreach($loadedListData->missing_items as $index => $missing)
                                        @php
                                            $part = isset($missing['part_id']) ? \App\Models\Part::find($missing['part_id']) : null;
                                            $currentStock = $part ? $part->quantity : 0;
                                        @endphp
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200">
                                            <div class="flex-1">
                                                <strong>{{ $missing['name'] }}</strong> - ilość: {{ $missing['quantity'] }}
                                                <span class="text-xs {{ $currentStock >= $missing['quantity'] ? 'text-green-600 font-semibold' : 'text-red-600' }}">(teraz dostępne: {{ $currentStock }})</span>
                                                <br>
                                                <span class="text-gray-600 text-xs">{{ $missing['reason'] }}</span>
                                            </div>
                                            @if($part && $part->quantity >= $missing['quantity'])
                                                <button type="button" 
                                                        class="ml-2 px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs font-semibold add-missing-btn"
                                                        data-loaded-list-id="{{ $loadedListData->id }}"
                                                        data-part-id="{{ $missing['part_id'] }}"
                                                        data-quantity="{{ $missing['quantity'] }}"
                                                        data-part-name="{{ $missing['name'] }}">
                                                    ✓ Dostępny - Dodaj
                                                </button>
                                            @elseif($part && $part->quantity > 0)
                                                <button type="button" 
                                                        class="ml-2 px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-xs font-semibold add-missing-btn"
                                                        data-loaded-list-id="{{ $loadedListData->id }}"
                                                        data-part-id="{{ $missing['part_id'] }}"
                                                        data-quantity="{{ $part->quantity }}"
                                                        data-part-name="{{ $missing['name'] }}">
                                                    ⚠ Dodaj {{ $part->quantity }}
                                                </button>
                                            @else
                                                <span class="ml-2 px-3 py-1 bg-red-200 text-red-800 rounded text-xs font-semibold">
                                                    ✗ Brak na magazynie
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- INFORMACJA O PRODUKTACH SPOZA LIST --}}
        @if(count($outsideListsData) > 0)
            <div class="mt-4">
                <div class="p-3 rounded border bg-blue-50 border-blue-200">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-blue-800">📦 Produkty dodane poza listami</span>
                        <button type="button" class="ml-2 text-orange-600 hover:text-orange-800 font-bold text-xl" onclick="showOutsideProducts()" title="Kliknij aby zobaczyć produkty">❗</button>
                    </div>
                    <div id="outside-products-details" class="hidden mt-2 p-2 bg-white border border-blue-300 rounded text-xs">
                        <strong class="text-blue-600">Produkty dodane ręcznie (przez "Pobierz produkty do projektu"):</strong>
                        <ul class="list-disc list-inside ml-2 mt-1">
                            @foreach($outsideListsData as $product)
                                <li><strong>{{ $product['name'] }}</strong> - ilość: {{ $product['quantity'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="mt-4 flex gap-2 justify-end">
            @if($project->status !== 'warranty')
                <button type="button" id="choose-list-btn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    📋 Wybierz listę projektową
                </button>
                <a href="{{ route('magazyn.projects.pickup', $project->id) }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    ➖ Pobierz produkty do projektu
                </a>
                <a href="{{ route('magazyn.editProject', $project->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Edytuj projekt
                </a>
                @if($project->status === 'in_progress')
                <button id="finish-project-btn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Zakończ projekt
                </button>
                @endif
            @else
                <span class="text-gray-400 text-sm">Projekt na gwarancji – tylko podgląd, bez możliwości edycji lub modyfikacji produktów.</span>
            @endif
        </div>
    </div>

    {{-- TABELA PRODUKTÓW --}}
    <div class="mb-6">
        @php
            $unauthorized = $removals->where('authorized', false);
            $authorized = $removals->where('authorized', true);
        @endphp
        
        @if($project->requires_authorization && $unauthorized->count() > 0)
        {{-- SEKCJA NIEAUTORYZOWANYCH --}}
        <div class="bg-red-50 border border-red-200 rounded p-4 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-red-800">🔒 Produkty oczekujące na autoryzację ({{ $unauthorized->count() }})</h3>
                <a href="{{ route('magazyn.projects.authorize', $project->id) }}" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 font-semibold">
                    🔍 Zacznij autoryzację (skanowanie)
                </a>
            </div>
            <table class="w-full border border-collapse text-xs bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">Nazwa produktu</th>
                        <th class="border p-2 text-center">Kod QR</th>
                        <th class="border p-2 text-center">Ilość do autoryzacji</th>
                        <th class="border p-2 text-center">Data dodania</th>
                        <th class="border p-2 text-center">Dodał</th>
                        <th class="border p-2 text-center">Status magazynu</th>
                        <th class="border p-2 text-center">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unauthorized as $removal)
                        <tr class="bg-yellow-50">
                            <td class="border p-2">{{ $removal->part ? $removal->part->name : '⚠️ Produkt usunięty' }}</td>
                            <td class="border p-2 text-center font-mono text-xs">{{ $removal->part ? ($removal->part->qr_code ?? '-') : '-' }}</td>
                            <td class="border p-2 text-center font-bold text-red-600">{{ $removal->quantity }}</td>
                            <td class="border p-2 text-center">{{ $removal->created_at->format('d.m.Y H:i') }}</td>
                            <td class="border p-2 text-center">
                                @if(isset($removal->user) && $removal->user)
                                    {{ $removal->user->short_name ?? $removal->user->name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="border p-2 text-center">
                                <span class="text-orange-600 font-semibold text-xs">⚠️ Nie odjęte ze stanu</span>
                            </td>
                            <td class="border p-2 text-center">
                                @if($project->status !== 'warranty')
                                    <form method="POST" action="{{ route('magazyn.projects.removalDelete', [$project->id, $removal->id]) }}" onsubmit="return confirm('Czy na pewno chcesz usunąć/wycofać ten produkt z projektu? Operacja nie zmienia stanu magazynu.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 text-xs">Usuń / Zwrot</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs">Projekt zamknięty – brak możliwości usuwania produktów</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        {{-- KONTENER NA PRZESUWALNE SEKCJE --}}
        <div id="sortable-sections" class="space-y-8">
        
        {{-- SEKCJA 1: ZMIANY W MAGAZYNIE --}}
        <div id="section-changes" class="sortable-section bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm" data-order="1">
        <div class="flex items-center gap-3 mb-4">
            <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600" draggable="true" title="Przeciągnij, aby zmienić kolejność">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </div>
            <button type="button" id="toggle-changes-section" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <span id="toggle-changes-arrow">▶</span>
            </button>
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <span class="text-blue-600">🔄</span>
                Zmiany w magazynie
            </h3>
        </div>
        <div id="changes-section-content" class="hidden">
            <table class="w-full border border-collapse text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">Nazwa produktu</th>
                        <th class="border p-2 text-center">Ilość</th>
                        <th class="border p-2 text-center">Data/Godzina</th>
                        <th class="border p-2 text-center">Pobrał</th>
                        <th class="border p-2 text-center">Status</th>
                        <th class="border p-2 text-center">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($authorized as $removal)
                        <tr class="{{ $removal->status === 'returned' ? 'bg-green-50' : '' }}">
                            <td class="border p-2">{{ $removal->part ? $removal->part->name : '⚠️ Produkt usunięty' }}</td>
                            <td class="border p-2 text-center">{{ $removal->quantity }}</td>
                            <td class="border p-2 text-center">
                                {{ $removal->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="border p-2 text-center">
                                @if(isset($removal->user) && $removal->user)
                                    {{ $removal->user->short_name ?? $removal->user->name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="border p-2 text-center">
                                @if($removal->status === 'added')
                                    <span class="text-blue-600 font-semibold">Dodany</span>
                                @else
                                    <span class="text-green-600 font-semibold">Zwrócony</span>
                                    <br>
                                    <span class="text-xs text-gray-500">{{ $removal->returned_at->format('d.m.Y H:i') }}</span>
                                    <br>
                                    <span class="text-xs text-gray-500">
                                        przez
                                        @if(isset($removal->returnedBy) && $removal->returnedBy)
                                            {{ $removal->returnedBy->short_name ?? $removal->returnedBy->name }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="border p-2 text-center">
                                @if($removal->status === 'added' && !in_array($project->status, ['warranty','archived']))
                                    <form action="{{ route('magazyn.returnProduct', ['project' => $project->id, 'removal' => $removal->id]) }}" method="POST" class="inline" onsubmit="return confirm('Czy na pewno chcesz zwrócić ten produkt do katalogu?');">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:underline text-xs font-semibold">
                                            Zwróć produkt
                                        </button>
                                    </form>
                                @elseif(in_array($project->status, ['warranty','archived']))
                                    <span class="text-gray-400 text-xs"></span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border p-4 text-center text-gray-500">Brak produktów w magazynie</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{-- KONIEC SEKCJI 1: ZMIANY W MAGAZYNIE --}}
    
    {{-- SEKCJA 2: PODSUMOWANIE PRODUKTÓW --}}
    <div id="section-summary" class="sortable-section bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm" data-order="2">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600" draggable="true" title="Przeciągnij, aby zmienić kolejność">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                </div>
                <button type="button" id="toggle-summary-section" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <span id="toggle-summary-arrow">▼</span>
                </button>
                <h3 class="text-lg font-semibold">📋 Lista produktów w projekcie</h3>
            </div>
        </div>
        <div id="summary-section-content">
        @php
            // Grupowanie produktów i sumowanie ilości
            $summary = $removals->where('status', 'added')->groupBy('part_id')->map(function($group) {
                $firstRemoval = $group->first();
                if (!$firstRemoval->part) {
                    return null; // Pomiń jeśli produkt został usunięty
                }
                return [
                    'part' => $firstRemoval->part,
                    'total_quantity' => $group->sum('quantity')
                ];
            })->filter()->sortBy(function($item) {
                return $item['part']->name;
            });
        @endphp
        
        @if(auth()->user() && auth()->user()->is_admin)
        @if(auth()->user() && auth()->user()->is_admin && !in_array($project->status, ['warranty','archived']))
        <div class="mb-3 flex items-center gap-2">
            <button type="button" id="select-all-products" class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
                Zaznacz wszystkie
            </button>
            <button type="button" id="deselect-all-products" class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
                Odznacz wszystkie
            </button>
            <button type="button" id="delete-selected-products" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-semibold" disabled>
                🗑️ Usuń zaznaczone (<span id="selected-count">0</span>)
            </button>
        </div>
        @endif
        @endif
        
        <table class="w-full border border-collapse text-sm bg-white">
            <thead class="bg-blue-100">
                <tr>
                    @if(auth()->user() && auth()->user()->is_admin && !in_array($project->status, ['warranty','archived']))
                    <th class="border p-3 text-center" style="width: 50px;">
                        <input type="checkbox" id="select-all-checkbox" class="w-4 h-4 cursor-pointer" title="Zaznacz wszystkie">
                    </th>
                    @endif
                    <th class="border p-3 text-left">Nazwa produktu</th>
                    <th class="border p-3 text-left">Opis</th>
                    <th class="border p-3 text-center">Łączna ilość w projekcie</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $item)
                    <tr class="hover:bg-gray-50">
                        @if(auth()->user() && auth()->user()->is_admin && !in_array($project->status, ['warranty','archived']))
                        <td class="border p-3 text-center">
                            <input type="checkbox" class="product-checkbox w-4 h-4 cursor-pointer" data-part-id="{{ $item['part']->id }}" data-part-name="{{ $item['part']->name }}">
                        </td>
                        @endif
                        <td class="border p-3">{{ isset($item['part']) && $item['part'] ? $item['part']->name : '-' }}</td>
                        <td class="border p-3 text-gray-600">{{ isset($item['part']) && $item['part'] ? ($item['part']->description ?? '-') : '-' }}</td>
                        <td class="border p-3 text-center font-bold text-blue-600">{{ $item['total_quantity'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user() && auth()->user()->is_admin ? '4' : '3' }}" class="border p-4 text-center text-gray-500">Brak produktów w projekcie</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    {{-- KONIEC SEKCJI 2 --}}
    
    {{-- SEKCJA 3: GANTT FRAPPE --}}
    <div id="section-frappe" class="sortable-section bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm" data-order="3">
        <div class="flex items-center gap-3 mb-4">
            <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600" draggable="true" title="Przeciągnij, aby zmienić kolejność">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </div>
            <button type="button" id="toggle-frappe-section" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <span id="toggle-frappe-arrow">▶</span>
            </button>
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <span class="text-blue-600">📊</span>
                Gantt Frappe - Interaktywny harmonogram
            </h3>
        </div>
        <div id="frappe-section-content" class="hidden">
            <div class="mb-4 flex gap-2 items-center flex-wrap">
                @if(!in_array($project->status, ['warranty','archived']))
                    <button id="frappe-add-task" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm font-semibold">
                        ➕ Dodaj zadanie
                    </button>
                    <button id="frappe-export-excel" class="bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-700 text-sm font-semibold">
                        📊 Eksport Excel
                    </button>
                    <button id="frappe-share-link" class="bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700 text-sm font-semibold">
                        🔗 Udostępnij link
                    </button>
                    <button id="frappe-save-tasks" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm font-semibold">
                        💾 Zapisz zmiany
                    </button>
                    @if(auth()->user() && auth()->user()->is_admin)
                    <button id="frappe-clear-all" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm font-semibold">
                        🗑️ Wyczyść wszystko
                    </button>
                    @endif
                @endif
            </div>
            
            <div class="mb-4 flex gap-2 items-center flex-wrap">
                <label class="text-sm font-semibold text-gray-700">Widok:</label>
                <button class="frappe-view-btn bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm" data-mode="Quarter Day">Ćwierć dnia</button>
                <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Half Day">Pół dnia</button>
                <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Day">Dzień</button>
                <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Week">Tydzień</button>
                <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Month">Miesiąc</button>
                <button id="frappe-today" class="bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700 text-sm ml-4">
                    📅 Dzisiaj
                </button>
            </div>
            
            <div class="mb-3 p-2 bg-gray-50 rounded border">
                <p class="text-xs text-gray-600">
                    <strong>Instrukcja:</strong> 
                    • Kliknij dwukrotnie zadanie, aby je edytować 
                    • Przeciągnij zadanie, aby zmienić daty 
                    • Przeciągnij pasek postępu, aby zmienić procent ukończenia 
                    • Kliknij i przeciągnij z krawędzi zadania, aby utworzyć zależność
                </p>
            </div>
            
            @if(auth()->user() && auth()->user()->is_admin)
            <div class="mb-3 p-2 bg-yellow-50 rounded border border-yellow-200">
                <p class="text-xs text-gray-700">
                    <strong>🔧 Diagnostyka (tylko dla administratora):</strong> 
                    Projekt #{{ $project->id }} "{{ $project->name }}" • 
                    Otwórz konsolę przeglądarki (F12) aby zobaczyć szczegółowe logi ładowania danych • 
                    Jeśli wykres nie pokazuje zadań, sprawdź czy są one przypisane do tego projektu
                </p>
            </div>
            @endif
            
            <div id="frappe-gantt"></div>

            <div id="frappe-task-list" class="mt-8">
                <!-- Lista zadań pojawi się tutaj -->
            </div>

            {{-- Rejestr zmian Gantt --}}
            @if(method_exists($project, 'ganttChanges'))
            <div class="mt-8">
                <button onclick="toggleGanttChangelog()" class="text-lg font-bold mb-2 text-left hover:text-blue-600 transition-colors flex items-center gap-2">
                    <span id="gantt-changelog-icon">▶</span> Rejestr zmian (Gantt)
                </button>
                @php
                    try {
                        $changes = $project->ganttChanges()->with('user')->orderByDesc('created_at')->get();
                    } catch (\Exception $e) {
                        $changes = collect([]);
                    }
                @endphp
                <div id="gantt-changelog" class="hidden">
                    @if($changes->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-collapse text-xs bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">Data</th>
                                    <th class="border p-2">Użytkownik</th>
                                    <th class="border p-2">Akcja</th>
                                    <th class="border p-2">Nazwa zadania</th>
                                    <th class="border p-2">Szczegóły</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($changes as $change)
                                <tr>
                                    <td class="border p-2 text-center">{{ \Carbon\Carbon::parse($change->created_at)->format('d.m.Y H:i') }}</td>
                                    <td class="border p-2">{{ $change->user ? ($change->user->name ?? $change->user->short_name ?? '-') : '-' }}</td>
                                    <td class="border p-2 text-center">
                                        @if($change->action === 'add') ➕ Dodano
                                        @elseif($change->action === 'edit') ✏️ Edycja
                                        @elseif($change->action === 'delete') ❌ Usunięto
                                        @elseif($change->action === 'move') 🔄 Przesunięto
                                        @else {{ $change->action }}
                                        @endif
                                    </td>
                                    <td class="border p-2">{{ $change->task_name }}</td>
                                    <td class="border p-2">{{ $change->details }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div class="text-gray-500 p-4 text-center">Brak zarejestrowanych zmian w Gantt.</div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    {{-- KONIEC SEKCJI 3 --}}
    
    {{-- SEKCJA 4: HARMONOGRAM FINANSOWY --}}
    <div id="section-finance" class="sortable-section bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm" data-order="4">
        <div class="flex items-center gap-3 mb-4">
            <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600" draggable="true" title="Przeciągnij, aby zmienić kolejność">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </div>
            <button type="button" id="toggle-finance-section" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <span id="toggle-finance-arrow">▶</span>
            </button>
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <span class="text-green-600">💰</span>
                Harmonogram finansowy
            </h3>
        </div>
        <div id="finance-section-content" class="hidden">
            <p class="text-gray-600 text-sm mb-4">Zarządzaj przychodami i wydatkami projektu w czasie:</p>
            
            {{-- Przyciski dodawania --}}
            @if(!in_array($project->status, ['warranty','archived']))
            <div class="flex gap-2 mb-4">
                <button type="button" id="add-income-row" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-2">
                    <span>📈</span> Dodaj przychód
                </button>
                <button type="button" id="add-expense-row" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 flex items-center gap-2">
                    <span>📉</span> Dodaj wydatek
                </button>
            </div>
            @endif
            
            {{-- Lista transakcji finansowych --}}
            <div id="finance-transactions-list" class="space-y-2 mb-4">
                <!-- Wiersze transakcji będą dodawane dynamicznie -->
            </div>
            
            {{-- Podsumowanie finansowe --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-4 mt-4">
                <h4 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                    <span>📊</span> Podsumowanie finansowe
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                    <div class="bg-white rounded p-3 border border-green-200">
                        <div class="text-xs text-gray-500 uppercase mb-1">Przychody</div>
                        <div id="total-income" class="text-xl font-bold text-green-600">0.00 zł</div>
                    </div>
                    <div class="bg-white rounded p-3 border border-red-200">
                        <div class="text-xs text-gray-500 uppercase mb-1">Wydatki</div>
                        <div id="total-expenses" class="text-xl font-bold text-red-600">0.00 zł</div>
                    </div>
                    <div class="bg-white rounded p-3 border border-blue-300">
                        <div class="text-xs text-gray-500 uppercase mb-1">Bilans</div>
                        <div id="balance" class="text-xl font-bold text-blue-600">0.00 zł</div>
                    </div>
                </div>
                
                {{-- Wykres czasowy --}}
                <div class="bg-white rounded-lg p-4 border border-gray-200 mt-3">
                    <h5 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <span>📈</span> Cash Flow w czasie
                    </h5>
                    <canvas id="cashflow-chart" style="max-height: 300px;"></canvas>
                </div>
                
                {{-- Legenda statusów --}}
                <div class="mt-3 flex flex-wrap gap-3 text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-gray-600">Zapłacone</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <span class="text-gray-600">Zamówione</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                        <span class="text-gray-600">Przewidziane</span>
                    </div>
                </div>
            </div>
            
            <p class="text-xs text-gray-500 mt-3">💡 Przeciągnij wiersze, aby zmienić kolejność. Wydatki mogą być oznaczone jako zapłacone, zamówione lub przewidziane.</p>
        </div>
    </div>
    {{-- KONIEC SEKCJI 4 --}}
    
    </div>
    {{-- KONIEC KONTENERA PRZESUWANYCH SEKCJI --}}
    
</div>

{{-- MODAL ZAKOŃCZENIA PROJEKTU --}}
<div id="finish-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">Zakończ projekt</h3>
        <p class="mb-4 text-gray-700">Czy na pewno chcesz zakończyć ten projekt? Status projektu zmieni się na "Na gwarancji".</p>
        <form action="{{ route('magazyn.finishProject', $project->id) }}" method="POST">
            @csrf
            <div class="flex gap-2 justify-end">
                <button type="button" id="cancel-finish-btn" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Anuluj
                </button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Potwierdź
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const finishBtn = document.getElementById('finish-project-btn');
    const finishModal = document.getElementById('finish-modal');
    const cancelFinishBtn = document.getElementById('cancel-finish-btn');

    if (finishBtn) {
        finishBtn.addEventListener('click', function() {
            finishModal.classList.remove('hidden');
        });
    }

    if (cancelFinishBtn) {
        cancelFinishBtn.addEventListener('click', function() {
            finishModal.classList.add('hidden');
        });
    }

    if (finishModal) {
        finishModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    }

    // ===== FUNKCJONALNOŚĆ PRZESUWANIA SEKCJI =====
    const sortableContainer = document.getElementById('sortable-sections');
    let draggedElement = null;

    // Obsługa przeciągania (drag & drop) - TYLKO przez drag-handle (ikona trzech kropek)
    document.querySelectorAll('.drag-handle').forEach(handle => {
        handle.addEventListener('dragstart', function(e) {
            // Znajdź rodzica który jest sekcją sortable
            draggedElement = this.closest('.sortable-section');
            if (draggedElement) {
                draggedElement.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', draggedElement.innerHTML);
            }
        });

        handle.addEventListener('dragend', function() {
            if (draggedElement) {
                draggedElement.classList.remove('dragging');
                saveSectionOrder();
                draggedElement = null;
            }
        });
    });

    // Obsługa dragover na sekcjach (gdzie można upuścić)
    document.querySelectorAll('.sortable-section').forEach(section => {

        section.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            if (draggedElement && this !== draggedElement) {
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    this.parentNode.insertBefore(draggedElement, this);
                } else {
                    this.parentNode.insertBefore(draggedElement, this.nextSibling);
                }
            }
        });
    });

    // Obsługa przycisków (strzałki)
    function moveSection(sectionId, direction) {
        const section = document.getElementById(sectionId);
        const container = document.getElementById('sortable-sections');
        const sections = Array.from(container.children);
        const currentIndex = sections.indexOf(section);

        if (direction === 'up' && currentIndex > 0) {
            container.insertBefore(section, sections[currentIndex - 1]);
            saveSectionOrder();
        } else if (direction === 'down' && currentIndex < sections.length - 1) {
            container.insertBefore(section, sections[currentIndex + 2]);
            saveSectionOrder();
        }
    }

    // Zapisz kolejność do localStorage
    function saveSectionOrder() {
        const container = document.getElementById('sortable-sections');
        const sections = Array.from(container.children);
        const order = sections.map(section => section.id);
        localStorage.setItem('projectSectionsOrder_{{ $project->id }}', JSON.stringify(order));
    }

    // Wczytaj kolejność z localStorage
    function loadSectionOrder() {

        const savedOrder = localStorage.getItem('projectSectionsOrder_{{ $project->id }}');
        const container = document.getElementById('sortable-sections');
        if (!savedOrder) {
            // Domyślna kolejność: podsumowanie na górze, zmiany w magazynie w środku, frappe na dole
            const summary = document.getElementById('section-summary');
            const changes = document.getElementById('section-changes');
            const frappe = document.getElementById('section-frappe');
            if (summary && changes && frappe) {
                container.appendChild(summary);
                container.appendChild(changes);
                container.appendChild(frappe);
            }
            return;
        }
        try {
            const order = JSON.parse(savedOrder);
            order.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) {
                    container.appendChild(section);
                }
            });
        } catch (e) {
            console.error('Błąd wczytywania kolejności sekcji:', e);
        }
    }

    // Wczytaj zapisaną kolejność przy załadowaniu strony
    document.addEventListener('DOMContentLoaded', function() {
        loadSectionOrder();
        
        // Obsługa rozwijania sekcji Zmiany w magazynie
        const toggleBtn = document.getElementById('toggle-changes-section');
        const content = document.getElementById('changes-section-content');
        const arrow = document.getElementById('toggle-changes-arrow');
        if (toggleBtn && content && arrow) {
            // Ustaw domyślnie zamknięte
            content.classList.add('hidden');
            arrow.textContent = '▶';
            toggleBtn.addEventListener('click', function() {
                content.classList.toggle('hidden');
                arrow.textContent = content.classList.contains('hidden') ? '▶' : '▼';
            });
        }
        
        // Obsługa rozwijania sekcji Lista produktów w projekcie
        const summaryToggleBtn = document.getElementById('toggle-summary-section');
        const summaryContent = document.getElementById('summary-section-content');
        const summaryArrow = document.getElementById('toggle-summary-arrow');
        if (summaryToggleBtn && summaryContent && summaryArrow) {
            // Domyślnie rozwinięta (bez hidden)
            summaryArrow.textContent = '▼';
            summaryToggleBtn.addEventListener('click', function() {
                summaryContent.classList.toggle('hidden');
                summaryArrow.textContent = summaryContent.classList.contains('hidden') ? '▶' : '▼';
            });
        }
        
        // Obsługa rozwijania sekcji Gantt Frappe
        const frappeToggleBtn = document.getElementById('toggle-frappe-section');
        const frappeContent = document.getElementById('frappe-section-content');
        const frappeArrow = document.getElementById('toggle-frappe-arrow');
        if (frappeToggleBtn && frappeContent && frappeArrow) {
            // Ustaw domyślnie zamknięte
            frappeContent.classList.add('hidden');
            frappeArrow.textContent = '▶';
            frappeToggleBtn.addEventListener('click', function() {
                frappeContent.classList.toggle('hidden');
                frappeArrow.textContent = frappeContent.classList.contains('hidden') ? '▶' : '▼';
            });
        }
        
        // Obsługa rozwijania sekcji Harmonogram finansowy
        const financeToggleBtn = document.getElementById('toggle-finance-section');
        const financeContent = document.getElementById('finance-section-content');
        const financeArrow = document.getElementById('toggle-finance-arrow');
        if (financeToggleBtn && financeContent && financeArrow) {
            // Ustaw domyślnie zamknięte
            financeContent.classList.add('hidden');
            financeArrow.textContent = '▶';
            financeToggleBtn.addEventListener('click', function() {
                financeContent.classList.toggle('hidden');
                financeArrow.textContent = financeContent.classList.contains('hidden') ? '▶' : '▼';
            });
        }
        
        // --- Harmonogram finansowy: zarządzanie przychodami i wydatkami ---
        let financeRowIndex = 0;
        const financeList = document.getElementById('finance-transactions-list');
        const addIncomeBtn = document.getElementById('add-income-row');
        const addExpenseBtn = document.getElementById('add-expense-row');
        let cashflowChart = null;
        
        // Status badge helper
        function getStatusBadge(status) {
            const badges = {
                'paid': '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">✓ Zapłacone</span>',
                'ordered': '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">⏳ Zamówione</span>',
                'planned': '<span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">📅 Przewidziane</span>'
            };
            return badges[status] || '';
        }
        
        // Dodaj przychód
        function addIncomeRow(name = '', amount = '', date = '') {
            if (!financeList) return;
            const row = document.createElement('div');
            row.className = 'finance-row flex gap-2 items-center p-3 bg-green-50 border-l-4 border-green-500 rounded shadow-sm cursor-move';
            row.draggable = true;
            row.dataset.type = 'income';
            row.innerHTML = `
                <span class="drag-handle text-gray-400 hover:text-gray-600 cursor-grab text-xl" title="Przeciągnij">⋮⋮</span>
                <input type="hidden" name="finance[${financeRowIndex}][type]" value="income">
                <div class="flex items-center gap-2 flex-1">
                    <span class="text-green-600 font-bold text-lg">📈</span>
                    <input type="text" name="finance[${financeRowIndex}][name]" class="px-3 py-2 border rounded flex-1" placeholder="Nazwa transzy / płatności" value="${name}" required>
                </div>
                <input type="number" name="finance[${financeRowIndex}][amount]" class="px-3 py-2 border rounded w-32 font-semibold text-green-700" placeholder="Kwota" min="0" step="0.01" value="${amount}" required>
                <input type="date" name="finance[${financeRowIndex}][date]" class="px-3 py-2 border rounded w-40" value="${date}" required>
                <button type="button" class="remove-finance-row px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-semibold">🗑️</button>`;
            financeList.appendChild(row);
            financeRowIndex++;
            addFinanceDragListeners(row);
            updateFinancials();
        }
        
        // Dodaj wydatek
        function addExpenseRow(category = 'materials', name = '', amount = '', date = '', status = 'planned') {
            if (!financeList) return;
            const row = document.createElement('div');
            row.className = 'finance-row flex gap-2 items-center p-3 bg-red-50 border-l-4 border-red-500 rounded shadow-sm cursor-move';
            row.draggable = true;
            row.dataset.type = 'expense';
            row.innerHTML = `
                <span class="drag-handle text-gray-400 hover:text-gray-600 cursor-grab text-xl" title="Przeciągnij">⋮⋮</span>
                <input type="hidden" name="finance[${financeRowIndex}][type]" value="expense">
                <div class="flex items-center gap-2 flex-1">
                    <span class="text-red-600 font-bold text-lg">📉</span>
                    <select name="finance[${financeRowIndex}][category]" class="px-3 py-2 border rounded bg-white" required>
                        <option value="materials" ${category === 'materials' ? 'selected' : ''}>🔧 Materiały</option>
                        <option value="services" ${category === 'services' ? 'selected' : ''}>👷 Usługi</option>
                    </select>
                    <input type="text" name="finance[${financeRowIndex}][name]" class="px-3 py-2 border rounded flex-1" placeholder="Nazwa wydatku" value="${name}" required>
                </div>
                <input type="number" name="finance[${financeRowIndex}][amount]" class="px-3 py-2 border rounded w-32 font-semibold text-red-700" placeholder="Kwota" min="0" step="0.01" value="${amount}" required>
                <input type="date" name="finance[${financeRowIndex}][date]" class="px-3 py-2 border rounded w-40" value="${date}" required>
                <select name="finance[${financeRowIndex}][status]" class="px-3 py-2 border rounded bg-white status-select" data-status="${status}" required>
                    <option value="paid" ${status === 'paid' ? 'selected' : ''}>✓ Zapłacone</option>
                    <option value="ordered" ${status === 'ordered' ? 'selected' : ''}>⏳ Zamówione</option>
                    <option value="planned" ${status === 'planned' ? 'selected' : ''}>📅 Przewidziane</option>
                </select>
                <button type="button" class="remove-finance-row px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-semibold">🗑️</button>`;
            financeList.appendChild(row);
            financeRowIndex++;
            addFinanceDragListeners(row);
            updateFinancials();
        }
        
        // Event listeners dla przycisków
        if (addIncomeBtn) {
            addIncomeBtn.addEventListener('click', () => addIncomeRow());
        }
        
        if (addExpenseBtn) {
            addExpenseBtn.addEventListener('click', () => addExpenseRow());
        }
        
        // Usuwanie wierszy
        if (financeList) {
            financeList.addEventListener('click', e => {
                if (e.target.closest('.remove-finance-row')) {
                    e.target.closest('.finance-row')?.remove();
                    reindexFinanceRows();
                    updateFinancials();
                }
            });
            
            // Live update przy zmianie wartości
            financeList.addEventListener('input', updateFinancials);
            financeList.addEventListener('change', updateFinancials);
        }
        
        // Drag & drop dla wierszy finansowych
        let finDragged = null;
        function addFinanceDragListeners(row) {
            row.addEventListener('dragstart', function(e) {
                finDragged = this;
                this.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
            });
            row.addEventListener('dragend', function() {
                this.classList.remove('opacity-50');
                finDragged = null;
                reindexFinanceRows();
            });
            row.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (!finDragged || finDragged === this) return;
                const rect = this.getBoundingClientRect();
                const mid = rect.top + rect.height / 2;
                if (e.clientY < mid) {
                    this.parentNode.insertBefore(finDragged, this);
                } else {
                    this.parentNode.insertBefore(finDragged, this.nextSibling);
                }
            });
        }
        
        function reindexFinanceRows() {
            if (!financeList) return;
            const rows = financeList.querySelectorAll('.finance-row');
            rows.forEach((row, idx) => {
                row.querySelectorAll('input, select').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) input.setAttribute('name', name.replace(/finance\[\d+\]/, `finance[${idx}]`));
                });
            });
            financeRowIndex = rows.length;
        }
        
        // Funkcja przeliczająca finanse i aktualizująca wykres
        function updateFinancials() {
            if (!financeList) return;
            
            const rows = financeList.querySelectorAll('.finance-row');
            let totalIncome = 0;
            let totalExpenses = 0;
            const transactions = [];
            
            rows.forEach(row => {
                const type = row.dataset.type;
                const amountInput = row.querySelector('input[name*="[amount]"]');
                const dateInput = row.querySelector('input[name*="[date]"]');
                const nameInput = row.querySelector('input[name*="[name]"]');
                
                const amount = parseFloat(amountInput?.value || 0);
                const date = dateInput?.value || '';
                const name = nameInput?.value || '';
                
                if (amount > 0 && date) {
                    transactions.push({
                        type: type,
                        amount: amount,
                        date: date,
                        name: name
                    });
                    
                    if (type === 'income') {
                        totalIncome += amount;
                    } else if (type === 'expense') {
                        totalExpenses += amount;
                    }
                }
            });
            
            const balance = totalIncome - totalExpenses;
            
            // Aktualizuj podsumowanie
            const incomeEl = document.getElementById('total-income');
            const expensesEl = document.getElementById('total-expenses');
            const balanceEl = document.getElementById('balance');
            
            if (incomeEl) incomeEl.textContent = totalIncome.toFixed(2) + ' zł';
            if (expensesEl) expensesEl.textContent = totalExpenses.toFixed(2) + ' zł';
            if (balanceEl) {
                balanceEl.textContent = balance.toFixed(2) + ' zł';
                balanceEl.className = 'text-xl font-bold ' + (balance >= 0 ? 'text-green-600' : 'text-red-600');
            }
            
            // Aktualizuj wykres
            renderCashflowChart(transactions);
        }
        
        // Renderowanie wykresu cash flow
        function renderCashflowChart(transactions) {
            const canvas = document.getElementById('cashflow-chart');
            if (!canvas) return;
            
            // Sortuj transakcje po dacie
            transactions.sort((a, b) => new Date(a.date) - new Date(b.date));
            
            // Przygotuj dane dla wykresu
            const labels = [];
            const incomeData = [];
            const expenseData = [];
            const balanceData = [];
            let cumulativeBalance = 0;
            
            // Grupuj po datach
            const dateMap = new Map();
            transactions.forEach(t => {
                if (!dateMap.has(t.date)) {
                    dateMap.set(t.date, { income: 0, expense: 0 });
                }
                const entry = dateMap.get(t.date);
                if (t.type === 'income') {
                    entry.income += t.amount;
                } else {
                    entry.expense += t.amount;
                }
            });
            
            // Przekształć na tablice dla Chart.js
            dateMap.forEach((value, date) => {
                labels.push(new Date(date).toLocaleDateString('pl-PL'));
                incomeData.push(value.income);
                expenseData.push(value.expense);
                cumulativeBalance += (value.income - value.expense);
                balanceData.push(cumulativeBalance);
            });
            
            // Zniszcz poprzedni wykres
            if (cashflowChart) {
                cashflowChart.destroy();
            }
            
            // Utwórz nowy wykres
            cashflowChart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Przychody',
                            data: incomeData,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            borderWidth: 2,
                            fill: true
                        },
                        {
                            label: 'Wydatki',
                            data: expenseData,
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            fill: true
                        },
                        {
                            label: 'Bilans narastająco',
                            data: balanceData,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' zł';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(0) + ' zł';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>

{{-- Modal dodawania/edycji zadania --}}
<div id="frappe-task-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 id="modal-title" class="text-xl font-bold mb-4 text-gray-800">Dodaj nowe zadanie</h3>
        <form id="frappe-task-form">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nazwa zadania</label>
                <input type="text" id="task-name-input" class="w-full border rounded px-3 py-2" placeholder="Nazwa zadania" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Data rozpoczęcia</label>
                <input type="date" id="task-start-input" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Ilość dni (łącznie z weekendami)</label>
                <input type="number" id="task-duration-input" class="w-full border rounded px-3 py-2" min="1" value="1">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Data zakończenia</label>
                <input type="date" id="task-end-input" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Postęp (%)</label>
                <input type="number" id="task-progress-input" class="w-full border rounded px-3 py-2" min="0" max="100" value="0">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Zależność od zadania</label>
                <select id="task-dependency-input" class="w-full border rounded px-3 py-2">
                    <option value="">Brak (zadanie główne)</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Wybierz zadanie, po którym to zadanie może się rozpocząć</p>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" id="modal-cancel" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Anuluj
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Zapisz
                </button>
                <button type="button" id="modal-delete-task" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" style="display:none">
                    Usuń zadanie
                </button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.0/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.0/dist/frappe-gantt.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<style>
    #frappe-gantt { 
        min-height: 320px; 
        min-width: 100%; 
        background-color: white;
    }
    .gantt-container {
        background-color: white !important;
    }
    
    /* Style dla przeciąganych sekcji */
    .sortable-section {
        transition: all 0.3s ease;
        cursor: default; /* Sekcja sama nie jest przeciągalna */
    }
    
    .sortable-section:hover {
        border-color: #3b82f6 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    /* Tylko drag-handle (ikona trzech kropek) jest przeciągalny */
    .drag-handle {
        cursor: grab !important;
        user-select: none;
    }
    
    .drag-handle:active {
        cursor: grabbing !important;
    }
    
    .sortable-section.dragging {
        opacity: 0.5;
        transform: scale(0.98);
    }
</style>
<script>
function toggleGanttChangelog() {
    const content = document.getElementById('gantt-changelog');
    const icon = document.getElementById('gantt-changelog-icon');
    content.classList.toggle('hidden');
    icon.textContent = content.classList.contains('hidden') ? '▶' : '▼';
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof Gantt === 'undefined') {
        console.error('❌ Frappe Gantt nie został załadowany z CDN!');
        document.getElementById('frappe-gantt').innerHTML = '<div class="text-red-500 p-4">Błąd: Biblioteka Frappe Gantt nie została załadowana.</div>';
        return;
    }
    
    const PROJECT_ID = {{ $project->id }};
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    let frappeGanttInstance = null;
    let frappeTasks = [];
    let editingTaskId = null;
    
    function parseDate(dateStr) {
        if (!dateStr) return new Date();
        if (dateStr instanceof Date) return dateStr;
        const parts = dateStr.toString().split('-');
        if (parts.length === 3) {
            return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        }
        return new Date(dateStr);
    }
    
    function formatDateForInput(date) {
        if (!(date instanceof Date)) date = new Date(date);
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }
    
    function saveTasks() {
        // Zapisz zadania do bazy danych przez API
        console.log('💾 Zapisywanie kolejności ' + frappeTasks.length + ' zadań...');
        const tasksToSave = frappeTasks.map((t, index) => ({
            id: t.id,
            name: t.name,
            start: t.start instanceof Date ? t.start.toISOString().split('T')[0] : t.start,
            end: t.end instanceof Date ? t.end.toISOString().split('T')[0] : t.end,
            progress: t.progress || 0,
            dependencies: t.dependencies || '',
            order: index
        }));
        
        console.log('📤 Wysyłam kolejność zadań:', tasksToSave.map(t => `#${t.id}: ${t.name}`));
        
        fetch(`/api/gantt/${PROJECT_ID}/reorder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ order: tasksToSave.map(t => t.id) })
        }).then(response => {
            console.log('📥 Odpowiedź zapisu kolejności status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('❌ Błąd HTTP przy zapisie kolejności:', response.status, text);
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        }).then(data => {
            console.log('✅ Zapisano kolejność zadań w bazie danych');
        }).catch(error => {
            console.error('❌ Błąd zapisu kolejności:', error);
            alert('⚠️ Nie udało się zapisać kolejności zadań!\n' + error.message);
        });
    }
    
    function updateDependencySelect() {
        const select = document.getElementById('task-dependency-input');
        select.innerHTML = '<option value="">Brak (zadanie główne)</option>';
        frappeTasks.forEach(task => {
            if (!editingTaskId || task.id !== editingTaskId) {
                const option = document.createElement('option');
                option.value = task.id;
                option.textContent = task.name;
                select.appendChild(option);
            }
        });
    }
    
    function showTaskModal(taskId = null) {
        const modal = document.getElementById('frappe-task-modal');
        const form = document.getElementById('frappe-task-form');
        const title = document.getElementById('modal-title');
        const deleteBtn = document.getElementById('modal-delete-task');
        editingTaskId = taskId;
        if (taskId) {
            const task = frappeTasks.find(t => t.id === taskId);
            if (task) {
                title.textContent = 'Edytuj zadanie';
                document.getElementById('task-name-input').value = task.name;
                document.getElementById('task-start-input').value = formatDateForInput(task.start);
                document.getElementById('task-end-input').value = formatDateForInput(task.end);
                const duration = Math.ceil((task.end - task.start) / (1000 * 60 * 60 * 24)) + 1;
                document.getElementById('task-duration-input').value = duration;
                document.getElementById('task-progress-input').value = task.progress || 0;
                deleteBtn.style.display = 'inline-block';
            }
        } else {
            title.textContent = 'Dodaj nowe zadanie';
            form.reset();
            const today = new Date();
            document.getElementById('task-start-input').value = formatDateForInput(today);
            document.getElementById('task-end-input').value = formatDateForInput(today);
            document.getElementById('task-duration-input').value = 1;
            document.getElementById('task-progress-input').value = 0;
            deleteBtn.style.display = 'none';
        }
        updateDependencySelect();
        if (taskId) {
            const task = frappeTasks.find(t => t.id === taskId);
            if (task && task.dependencies) {
                document.getElementById('task-dependency-input').value = task.dependencies;
            }
        }
        modal.classList.remove('hidden');
        
        // Dodaj event listenery dla pól daty i dni
        setupDateDurationListeners();
    }
    
    function setupDateDurationListeners() {
        const startInput = document.getElementById('task-start-input');
        const endInput = document.getElementById('task-end-input');
        const durationInput = document.getElementById('task-duration-input');
        
        // Usuń stare listenery (jeśli były)
        const newStartInput = startInput.cloneNode(true);
        const newEndInput = endInput.cloneNode(true);
        const newDurationInput = durationInput.cloneNode(true);
        startInput.parentNode.replaceChild(newStartInput, startInput);
        endInput.parentNode.replaceChild(newEndInput, endInput);
        durationInput.parentNode.replaceChild(newDurationInput, durationInput);
        
        // Zmiana daty rozpoczęcia → data końcowa nie może być wcześniej
        newStartInput.addEventListener('change', function() {
            const start = parseDate(this.value);
            const end = parseDate(newEndInput.value);
            
            // Jeśli data końcowa jest wcześniejsza niż początkowa, ustaw ją na początkową
            if (end < start) {
                newEndInput.value = this.value;
            }
            
            // Przelicz ilość dni
            const duration = Math.ceil((parseDate(newEndInput.value) - start) / (1000 * 60 * 60 * 24)) + 1;
            newDurationInput.value = Math.max(1, duration);
        });
        
        // Zmiana ilości dni → przelicz datę końcową
        newDurationInput.addEventListener('input', function() {
            const start = parseDate(newStartInput.value);
            const duration = parseInt(this.value) || 1;
            const end = new Date(start);
            end.setDate(end.getDate() + duration - 1);
            newEndInput.value = formatDateForInput(end);
        });
        
        // Zmiana daty końcowej → przelicz ilość dni
        newEndInput.addEventListener('change', function() {
            const start = parseDate(newStartInput.value);
            const end = parseDate(this.value);
            
            // Data końcowa nie może być wcześniej niż początkowa
            if (end < start) {
                this.value = newStartInput.value;
                newDurationInput.value = 1;
            } else {
                const duration = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                newDurationInput.value = Math.max(1, duration);
            }
        });
    }
    
    function hideTaskModal() {
        document.getElementById('frappe-task-modal').classList.add('hidden');
        editingTaskId = null;
    }
    
    function renderGantt() {
        console.log('🎨 renderGantt() wywołane, liczba zadań:', frappeTasks.length);
        if (frappeTasks.length === 0) {
            console.log('ℹ️ Brak zadań Gantta dla tego projektu.');
            document.getElementById('frappe-gantt').innerHTML = `<div class="bg-blue-50 border border-blue-200 text-blue-700 p-6 text-center rounded">
                <div class="text-5xl mb-3">📊</div>
                <div class="text-lg font-semibold mb-2">Brak zadań w harmonogramie</div>
                <div class="text-sm">Kliknij <strong>"➕ Dodaj zadanie"</strong> powyżej, aby utworzyć pierwszy wpis w wykresie Gantta.</div>
                <div class="text-xs text-gray-500 mt-3">Projekt #${PROJECT_ID}</div>
            </div>`;
            document.getElementById('frappe-task-list').innerHTML = '';
            return;
        }
        try {
            document.getElementById('frappe-gantt').innerHTML = '';
            // Ustal parametry dla trybu "Half Month"
            let ganttConfig = {
                header_height: 50,
                column_width: 30,
                step: 24,
                view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
                bar_height: 20,
                bar_corner_radius: 3,
                arrow_curve: 5,
                padding: 18,
                view_mode: 'Month',
                date_format: 'YYYY-MM-DD',
                language: 'en',
                custom_popup_html: function(task) {
                    // Naprawa błędu undefined
                    let startDate = task._start || task.start;
                    let endDate = task._end || task.end;
                    let start = startDate ? new Date(startDate).toLocaleDateString('pl-PL') : 'Brak';
                    let end = endDate ? new Date(endDate).toLocaleDateString('pl-PL') : 'Brak';
                    let duration = (startDate && endDate) ? Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24)) : '-';
                    const depText = task.dependencies ? frappeTasks.find(t => t.id === task.dependencies)?.name || 'Nieznane' : 'Brak';
                    return '<div style="padding: 10px;"><h5 style="margin: 0 0 10px 0; font-weight: bold;">' + (task.name || 'Brak') + '</h5><p style="margin: 5px 0;"><strong>Start:</strong> ' + start + '</p><p style="margin: 5px 0;"><strong>Koniec:</strong> ' + end + '</p><p style="margin: 5px 0;"><strong>Czas trwania:</strong> ' + duration + ' dni</p><p style="margin: 5px 0;"><strong>Postęp:</strong> ' + (task.progress ?? '-') + '%</p><p style="margin: 5px 0;"><strong>Zależność:</strong> ' + depText + '</p><p style="margin: 10px 0 0 0; font-size: 11px; color: #666;">💡 Kliknij dwukrotnie, aby edytować</p></div>';
                },
                on_date_change: function(task, start, end) {
                    if (['warranty','archived'].includes('{{ $project->status }}')) {
                        // Zablokuj przesuwanie zadań
                        return;
                    }
                    const taskIndex = frappeTasks.findIndex(t => t.id === task.id);
                    if (taskIndex !== -1) {
                        frappeTasks[taskIndex].start = start;
                        frappeTasks[taskIndex].end = end;
                        updateTaskInDB(task.id, { start: start.toISOString().split('T')[0], end: end.toISOString().split('T')[0] });
                        renderTaskList();
                    }
                },
                on_progress_change: function(task, progress) {
                    if (['warranty','archived'].includes('{{ $project->status }}')) {
                        // Zablokuj zmianę postępu
                        return;
                    }
                    const taskIndex = frappeTasks.findIndex(t => t.id === task.id);
                    if (taskIndex !== -1) {
                        frappeTasks[taskIndex].progress = progress;
                        updateTaskInDB(task.id, { progress: progress });
                        renderTaskList();
                    }
                },
                on_view_change: function(mode) {
                    document.querySelectorAll('.frappe-view-btn').forEach(btn => {
                        if (btn.dataset.mode === mode) {
                            btn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        } else {
                            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                            btn.classList.add('bg-blue-500', 'hover:bg-blue-600');
                        }
                    });
                }
            };
            // Obsługa wszystkich trybów (bez Half Month)
            if (window.frappeLastViewMode) {
                ganttConfig.view_mode = window.frappeLastViewMode;
                if (window.frappeLastViewMode === 'Month') {
                    ganttConfig.step = 24 * 30;
                    ganttConfig.column_width = 60;
                } else if (window.frappeLastViewMode === 'Week') {
                    ganttConfig.step = 24 * 7;
                    ganttConfig.column_width = 40;
                } else if (window.frappeLastViewMode === 'Day') {
                    ganttConfig.step = 24;
                    ganttConfig.column_width = 30;
                } else if (window.frappeLastViewMode === 'Half Day') {
                    ganttConfig.step = 12;
                    ganttConfig.column_width = 18;
                } else if (window.frappeLastViewMode === 'Quarter Day') {
                    ganttConfig.step = 6;
                    ganttConfig.column_width = 12;
                }
            }
            frappeGanttInstance = new Gantt("#frappe-gantt", frappeTasks, ganttConfig);
            renderTaskList();
            console.log('✅ Frappe Gantt zrenderowany!');
        } catch(error) {
            console.error('❌ Błąd Frappe Gantt:', error);
            document.getElementById('frappe-gantt').innerHTML = '<div class="text-red-500 p-4">Błąd: ' + error.message + '</div>';
        }
    }

    function renderTaskList() {
        const container = document.getElementById('frappe-task-list');
        if (!frappeTasks.length) { container.innerHTML = ''; return; }
        // Wyświetl wg kolejności Gantt (frappeTasks)
        let html = '<h4 class="text-lg font-bold mb-2">Lista zadań (kolejność jak w Gantt)</h4>';
        html += '<ul class="divide-y divide-gray-200">';
        frappeTasks.forEach((task, idx) => {
            const end = task.end instanceof Date ? task.end : parseDate(task.end);
            html += `<li class="flex items-center justify-between py-2">
                <div>
                    <span class="font-semibold">${task.name}</span>
                    <span class="ml-2 text-xs text-gray-500">(koniec: ${formatDateForInput(end)})</span>
                </div>
                <div class="flex gap-1">
                    <button class="move-task-up px-2 py-1 bg-gray-200 rounded text-xs" data-idx="${idx}">⬆️</button>
                    <button class="move-task-down px-2 py-1 bg-gray-200 rounded text-xs" data-idx="${idx}">⬇️</button>
                </div>
            </li>`;
        });
        html += '</ul>';
        container.innerHTML = html;
        // Dodaj obsługę przesuwania (wg indeksu w frappeTasks)
        container.querySelectorAll('.move-task-up').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.idx);
                if (idx > 0) {
                    const temp = frappeTasks[idx];
                    frappeTasks[idx] = frappeTasks[idx-1];
                    frappeTasks[idx-1] = temp;
                    saveTasks();
                    // renderGantt zachowa window.frappeLastViewMode
                    renderGantt();
                }
            });
        });
        container.querySelectorAll('.move-task-down').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.idx);
                if (idx < frappeTasks.length - 1) {
                    const temp = frappeTasks[idx];
                    frappeTasks[idx] = frappeTasks[idx+1];
                    frappeTasks[idx+1] = temp;
                    saveTasks();
                    // renderGantt zachowa window.frappeLastViewMode
                    renderGantt();
                }
            });
        });
    }
    
    // Funkcje pomocnicze dla API
    function loadTasksFromDB() {
        console.log('📡 Próba pobrania zadań Gantta dla projektu #' + PROJECT_ID + '...');
        return fetch(`/api/gantt/${PROJECT_ID}`, {
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
        })
        .then(response => {
            console.log('📥 Otrzymano odpowiedź API:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(tasks => {
            console.log('📦 Otrzymano zadania z API:', tasks);
            
            // Konwertuj obiekt na tablicę jeśli trzeba (Laravel czasem zwraca obiekt zamiast tablicy)
            let tasksArray = tasks;
            if (!Array.isArray(tasks)) {
                console.warn('⚠️ API zwróciło obiekt zamiast tablicy, konwertuję...');
                // Sprawdź czy to obiekt z kluczami numerycznymi
                if (typeof tasks === 'object' && tasks !== null) {
                    tasksArray = Object.values(tasks);
                    console.log('🔄 Skonwertowano obiekt na tablicę:', tasksArray);
                } else {
                    console.error('❌ API nie zwróciło prawidłowych danych:', tasks);
                    frappeTasks = [];
                    return;
                }
            }
            
            // Loguj pierwsze zadanie do debugowania
            if (tasksArray.length > 0) {
                console.log('🔍 Pierwsze zadanie (sample):', tasksArray[0]);
                console.log('🔍 Klucze pierwszego zadania:', Object.keys(tasksArray[0]));
            }
            
            // Mapuj zadania z walidacją
            frappeTasks = tasksArray
                .filter(t => {
                    if (!t || typeof t !== 'object') {
                        console.warn('⚠️ Pomijam nieprawidłowy element:', t);
                        return false;
                    }
                    if (!t.id) {
                        console.error('❌ Zadanie bez ID - pomijam:', t);
                        return false;
                    }
                    if (!t.name) {
                        console.warn('⚠️ Zadanie bez nazwy - ID:', t.id);
                    }
                    return true;
                })
                .map(t => ({
                    id: t.id.toString(),
                    name: t.name || 'Bez nazwy',
                    start: parseDate(t.start),
                    end: parseDate(t.end),
                    progress: t.progress || 0,
                    dependencies: t.dependencies || ''
                }));
            
            console.log('✅ Załadowano ' + frappeTasks.length + ' zadań z bazy (z ' + tasksArray.length + ' otrzymanych)');
            if (frappeTasks.length < tasksArray.length) {
                console.warn('⚠️ Pominięto ' + (tasksArray.length - frappeTasks.length) + ' nieprawidłowych zadań');
            }
            if (frappeTasks.length === 0) {
                console.warn('⚠️ Brak zadań Gantta dla tego projektu. Kliknij "➕ Dodaj zadanie" aby utworzyć nowe.');
            }
        })
        .catch(error => {
            console.error('❌ Błąd ładowania zadań Gantta:', error);
            console.error('URL:', `/api/gantt/${PROJECT_ID}`);
            console.error('Szczegóły błędu:', error.message);
            frappeTasks = [];
            // Pokaż komunikat użytkownikowi
            const ganttDiv = document.getElementById('frappe-gantt');
            if (ganttDiv) {
                ganttDiv.innerHTML = `<div class="text-red-500 p-4 border border-red-300 rounded bg-red-50">
                    <strong>❌ Błąd ładowania wykresu Gantta</strong><br>
                    ${error.message}<br>
                    <small>Sprawdź konsolę przeglądarki (F12) dla więcej szczegółów.</small>
                </div>`;
            }
        });
    }
    
    function updateTaskInDB(taskId, data) {
        console.log('📝 Aktualizacja zadania #' + taskId + ':', data);
        return fetch(`/api/gantt/${PROJECT_ID}/${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('📥 Odpowiedź aktualizacji status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('❌ Błąd HTTP przy aktualizacji:', response.status, text);
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Zaktualizowano zadanie #' + taskId + ' w bazie');
            return data;
        })
        .catch(error => {
            console.error('❌ Błąd aktualizacji zadania:', error);
            alert('⚠️ Nie udało się zaktualizować zadania!\n' + error.message);
            throw error;
        });
    }
    
    function deleteTaskFromDB(taskId) {
        console.log('🗑️ Usuwanie zadania #' + taskId);
        return fetch(`/api/gantt/${PROJECT_ID}/${taskId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
        })
        .then(response => {
            console.log('📥 Odpowiedź usuwania status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('❌ Błąd HTTP przy usuwaniu:', response.status, text);
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Usunięto zadanie #' + taskId + ' z bazy');
            return data;
        })
        .catch(error => {
            console.error('❌ Błąd usuwania zadania:', error);
            alert('⚠️ Nie udało się usunąć zadania!\n' + error.message);
            throw error;
        });
    }
    
    function createTaskInDB(task) {
        console.log('📤 Wysyłam żądanie utworzenia zadania:', task);
        return fetch(`/api/gantt/${PROJECT_ID}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                name: task.name,
                start: task.start instanceof Date ? task.start.toISOString().split('T')[0] : task.start,
                end: task.end instanceof Date ? task.end.toISOString().split('T')[0] : task.end,
                progress: task.progress || 0,
                dependencies: task.dependencies || '',
                order: frappeTasks.length
            })
        })
        .then(response => {
            console.log('📥 Odpowiedź serwera status:', response.status, response.statusText);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('❌ Błąd HTTP:', response.status, text);
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Utworzono zadanie w bazie danych:', data);
            if (!data.id) {
                console.error('⚠️ UWAGA: Serwer nie zwrócił ID zadania!', data);
                throw new Error('Serwer nie zwrócił ID zadania');
            }
            return data;
        })
        .catch(error => {
            console.error('❌ Błąd tworzenia zadania:', error);
            alert('❌ Nie udało się zapisać zadania do bazy danych!\n' + error.message + '\n\nZadanie NIE zostało zapisane.');
            throw error;
        });
    }
    
    // Załaduj zadania z bazy przy starcie
    console.log('🚀 Inicjalizacja wykresu Gantta dla projektu #' + PROJECT_ID);
    loadTasksFromDB().then(() => {
        console.log('📊 Renderowanie wykresu Gantta z ' + frappeTasks.length + ' zadaniami...');
        renderGantt();
    }).catch(error => {
        console.error('❌ Krytyczny błąd inicjalizacji Gantta:', error);
    });
    
    document.querySelectorAll('.frappe-view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            window.frappeLastViewMode = this.dataset.mode;
            renderGantt();
        });
    });
    
    document.getElementById('frappe-today').addEventListener('click', function() {
        renderGantt();
    });
    
    document.getElementById('frappe-add-task').addEventListener('click', function() {
        showTaskModal();
    });
    
    document.getElementById('frappe-share-link').addEventListener('click', function() {
        fetch(`/projekty/${PROJECT_ID}/generate-public-gantt`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        })
        .then(response => response.json())
        .then(data => {
            const url = data.url;
            navigator.clipboard.writeText(url).then(() => {
                alert('✅ Link skopiowany do schowka!\n\n' + url + '\n\nMożesz go wysłać osobom, które mają obejrzeć harmonogram bez możliwości edycji.');
            }).catch(() => {
                prompt('Link publiczny do harmonogramu (skopiuj):', url);
            });
        })
        .catch(error => {
            alert('❌ Błąd generowania linku: ' + error.message);
        });
    });
    
    document.getElementById('frappe-export-excel').addEventListener('click', function() {
        if (typeof XLSX === 'undefined') {
            alert('❌ Biblioteka Excel nie została załadowana. Odśwież stronę.');
            return;
        }
        if (frappeTasks.length === 0) {
            alert('⚠️ Brak zadań do eksportu!');
            return;
        }
        try {
            const exportData = frappeTasks.map(task => {
                // Naprawiona logika wyszukiwania zadania zależności
                const depTask = task.dependencies ? frappeTasks.find(t => t.id.toString() === task.dependencies.toString()) : null;
                const startDate = task.start instanceof Date ? task.start : parseDate(task.start);
                const endDate = task.end instanceof Date ? task.end : parseDate(task.end);
                const duration = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                return {
                    'Nazwa zadania': task.name,
                    'Data rozpoczęcia': startDate.toLocaleDateString('pl-PL'),
                    'Data zakończenia': endDate.toLocaleDateString('pl-PL'),
                    'Czas trwania (dni)': duration,
                    'Postęp (%)': task.progress || 0,
                    'Zależność od': depTask ? depTask.name : 'Brak'
                };
            });
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(exportData);
            ws['!cols'] = [{ wch: 30 }, { wch: 15 }, { wch: 15 }, { wch: 18 }, { wch: 12 }, { wch: 25 }];
            XLSX.utils.book_append_sheet(wb, ws, 'Harmonogram');
            const today = new Date();
            const fileName = 'Gantt_Harmonogram_' + today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0') + '.xlsx';
            XLSX.writeFile(wb, fileName);
            alert('✅ Wyeksportowano do: ' + fileName);
        } catch(error) {
            alert('❌ Błąd eksportu: ' + error.message);
        }
    });
    
    document.getElementById('modal-cancel').addEventListener('click', function() {
        hideTaskModal();
    });
    document.getElementById('modal-delete-task').addEventListener('click', function() {
        if (editingTaskId && confirm('Czy na pewno chcesz usunąć to zadanie?')) {
            deleteTaskFromDB(editingTaskId).then(() => {
                frappeTasks = frappeTasks.filter(t => t.id != editingTaskId);
                // Usuń zależności do tego zadania
                frappeTasks.forEach(t => {
                    if (t.dependencies == editingTaskId) t.dependencies = '';
                });
                renderGantt();
                hideTaskModal();
            });
        }
    });
    
    document.getElementById('frappe-task-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('task-name-input').value;
        const start = parseDate(document.getElementById('task-start-input').value);
        const end = parseDate(document.getElementById('task-end-input').value);
        const progress = parseInt(document.getElementById('task-progress-input').value) || 0;
        const dependency = document.getElementById('task-dependency-input').value;
        
        if (editingTaskId) {
            const taskIndex = frappeTasks.findIndex(t => t.id == editingTaskId);
            if (taskIndex !== -1) {
                // Oblicz różnicę w dniach dla zadań zależnych
                const oldStart = frappeTasks[taskIndex].start;
                const oldEnd = frappeTasks[taskIndex].end;
                const startDiff = Math.floor((start - oldStart) / (1000 * 60 * 60 * 24));
                const endDiff = Math.floor((end - oldEnd) / (1000 * 60 * 60 * 24));
                
                frappeTasks[taskIndex].name = name;
                frappeTasks[taskIndex].start = start;
                frappeTasks[taskIndex].end = end;
                frappeTasks[taskIndex].progress = progress;
                frappeTasks[taskIndex].dependencies = dependency;
                
                // Przesuń wszystkie zadania zależne od tego zadania
                if (startDiff !== 0 || endDiff !== 0) {
                    frappeTasks.forEach((task, idx) => {
                        if (task.dependencies === editingTaskId) {
                            const newTaskStart = new Date(task.start);
                            const newTaskEnd = new Date(task.end);
                            newTaskStart.setDate(newTaskStart.getDate() + endDiff);
                            newTaskEnd.setDate(newTaskEnd.getDate() + endDiff);
                            
                            frappeTasks[idx].start = newTaskStart;
                            frappeTasks[idx].end = newTaskEnd;
                            
                            // Zapisz zmiany w bazie dla zadania zależnego
                            updateTaskInDB(task.id, {
                                start: newTaskStart.toISOString().split('T')[0],
                                end: newTaskEnd.toISOString().split('T')[0]
                            });
                        }
                    });
                }
                
                updateTaskInDB(editingTaskId, {
                    name: name,
                    start: start.toISOString().split('T')[0],
                    end: end.toISOString().split('T')[0],
                    progress: progress,
                    dependencies: dependency
                }).then(() => {
                    renderGantt();
                    hideTaskModal();
                });
            }
        } else {
            const newTask = {
                name: name,
                start: start,
                end: end,
                progress: progress,
                dependencies: dependency
            };
            createTaskInDB(newTask).then(data => {
                frappeTasks.push({
                    id: data.id.toString(),
                    name: data.name,
                    start: parseDate(data.start),
                    end: parseDate(data.end),
                    progress: data.progress,
                    dependencies: data.dependencies || ''
                });
                renderGantt();
                hideTaskModal();
            });
        }
    });
    
    document.getElementById('frappe-save-tasks').addEventListener('click', function() {
        saveTasks();
        alert('✅ Kolejność zadań została zapisana!');
    });
    
    const clearAllBtn = document.getElementById('frappe-clear-all');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            if (confirm('Czy na pewno chcesz usunąć wszystkie zadania?')) {
                Promise.all(frappeTasks.map(t => deleteTaskFromDB(t.id))).then(() => {
                    frappeTasks = [];
                    renderGantt();
                });
            }
        });
    }
    
    document.addEventListener('dblclick', function(e) {
        const barWrapper = e.target.closest('.bar-wrapper');
        if (barWrapper) {
            const taskId = barWrapper.getAttribute('data-id');
            showTaskModal(taskId);
        }
    });
});
</script>

{{-- MODAL: Wybierz listę projektową --}}
<div id="choose-list-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Wybierz listę projektową</h3>
            <button type="button" id="close-choose-list-modal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        
        <div class="space-y-2 max-h-96 overflow-y-auto">
            @foreach($projectLists as $list)
                <div class="border rounded p-3 hover:bg-gray-50 cursor-pointer list-item" data-list-id="{{ $list->id }}">
                    <div class="font-semibold">{{ $list->name }}</div>
                    @if($list->description)
                        <div class="text-sm text-gray-600">{{ $list->description }}</div>
                    @endif
                    <div class="text-xs text-gray-500 mt-1">
                        Produktów: {{ $list->items->count() }} | Utworzono: {{ $list->created_at->format('d.m.Y') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- MODAL: Podgląd produktów z listy --}}
<div id="list-preview-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-6xl w-full max-h-[90vh] overflow-y-auto mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" id="list-preview-title">Produkty z listy</h3>
            <button type="button" id="close-list-preview-modal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        
        <div id="list-preview-content" class="mb-4">
            <!-- Zawartość będzie wczytana przez JavaScript -->
        </div>
        
        <div class="flex justify-end gap-2 mt-4">
            <button type="button" id="cancel-list-load" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                Anuluj
            </button>
            <button type="button" id="confirm-list-load" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Dodaj produkty do projektu
            </button>
        </div>
    </div>
</div>

<script>
// Pokazywanie brakujących produktów w liście
function showMissingItems(loadedListId) {
    const details = document.getElementById('missing-items-' + loadedListId);
    if (details) {
        details.classList.toggle('hidden');
    }
}

// Pokazywanie produktów spoza list
function showOutsideProducts() {
    const details = document.getElementById('outside-products-details');
    if (details) {
        details.classList.toggle('hidden');
    }
}

// Obsługa dodawania brakujących produktów
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('add-missing-btn')) {
        const btn = e.target;
        const loadedListId = btn.dataset.loadedListId;
        const partId = btn.dataset.partId;
        const quantity = btn.dataset.quantity;
        const partName = btn.dataset.partName;
        
        if (!confirm(`Czy na pewno chcesz dodać produkt "${partName}" (ilość: ${quantity}) do projektu?`)) {
            return;
        }
        
        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = 'Dodawanie...';
        
        fetch(`/projekty/{{ $project->id }}/add-missing-product`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                loaded_list_id: loadedListId,
                part_id: partId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Błąd: ' + (data.message || 'Nie udało się dodać produktu'));
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            alert('Błąd połączenia: ' + error.message);
            btn.disabled = false;
            btn.textContent = originalText;
        });
    }
    
    // Obsługa usuwania listy z projektu
    if (e.target.classList.contains('remove-list-btn')) {
        const btn = e.target;
        const loadedListId = btn.dataset.loadedListId;
        const listName = btn.dataset.listName;
        
        if (!confirm(`Czy na pewno chcesz usunąć listę "${listName}" z tego projektu?\n\nUWAGA: Lista zostanie tylko odrzeżona od projektu. Produkty już dodane do projektu z tej listy POZOSTANĄ w projekcie.`)) {
            return;
        }
        
        btn.disabled = true;
        btn.textContent = 'Usuwanie...';
        
        fetch(`/projekty/{{ $project->id }}/remove-list/${loadedListId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Błąd: ' + (data.message || 'Nie udało się usunąć listy'));
                btn.disabled = false;
                btn.textContent = '🗑️ Usuń';
            }
        })
        .catch(error => {
            alert('Błąd połączenia: ' + error.message);
            btn.disabled = false;
            btn.textContent = '🗑️ Usuń';
        });
    }
});

// Obsługa wyboru listy projektowej
const chooseListBtn = document.getElementById('choose-list-btn');
const chooseListModal = document.getElementById('choose-list-modal');
const closeChooseListModal = document.getElementById('close-choose-list-modal');
const closeListPreviewModal = document.getElementById('close-list-preview-modal');
const cancelListLoad = document.getElementById('cancel-list-load');

if (chooseListBtn && chooseListModal) {
    chooseListBtn.addEventListener('click', function() {
        chooseListModal.classList.remove('hidden');
    });
}

if (closeChooseListModal && chooseListModal) {
    closeChooseListModal.addEventListener('click', function() {
        chooseListModal.classList.add('hidden');
    });
}

if (closeListPreviewModal) {
    closeListPreviewModal.addEventListener('click', function() {
        document.getElementById('list-preview-modal').classList.add('hidden');
    });
}

if (cancelListLoad) {
    cancelListLoad.addEventListener('click', function() {
        document.getElementById('list-preview-modal').classList.add('hidden');
    });
}

document.querySelectorAll('.list-item').forEach(item => {
    item.addEventListener('click', function() {
        const listId = this.dataset.listId;
        loadListPreview(listId);
    });
});

let selectedListId = null;

function loadListPreview(listId) {
    selectedListId = listId;
    document.getElementById('choose-list-modal').classList.add('hidden');
    
    // Pobierz szczegóły listy przez AJAX
    fetch(`/projekty/{{ $project->id }}/preview-list/${listId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('list-preview-title').textContent = `Produkty z listy: ${data.list_name}`;
            
            let html = `
                <div class="mb-4">
                    <p class="text-sm text-gray-600">${data.description || ''}</p>
                </div>
                <table class="w-full border border-collapse text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2 text-left">Produkt</th>
                            <th class="border p-2 text-center">Ilość na liście</th>
                            <th class="border p-2 text-center">Stan magazynu</th>
                            <th class="border p-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            data.items.forEach(item => {
                const isAvailable = item.stock >= item.quantity;
                const statusClass = isAvailable ? 'bg-green-50' : 'bg-red-50';
                const statusText = isAvailable ? '✓ Dostępny' : '⚠ Brak na magazynie';
                const statusColor = isAvailable ? 'text-green-700' : 'text-red-700';
                
                html += `
                    <tr class="${statusClass}">
                        <td class="border p-2">${item.name}</td>
                        <td class="border p-2 text-center">${item.quantity}</td>
                        <td class="border p-2 text-center ${item.stock < item.quantity ? 'text-red-600 font-bold' : ''}">${item.stock}</td>
                        <td class="border p-2 text-center ${statusColor} font-semibold">${statusText}</td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            if (data.is_already_loaded) {
                html += `
                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                        <p class="text-sm text-red-800">
                            <strong>⚠ Uwaga:</strong> Ta lista jest już załadowana w tym projekcie. Nie możesz załadować jej ponownie.
                        </p>
                    </div>
                `;
                // Zablokuj przycisk dodawania
                document.getElementById('confirm-list-load').disabled = true;
                document.getElementById('confirm-list-load').classList.add('opacity-50', 'cursor-not-allowed');
            } else if (data.has_missing) {
                html += `
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                        <p class="text-sm text-yellow-800">
                            <strong>⚠ Uwaga:</strong> Niektóre produkty nie są dostępne w wystarczającej ilości na magazynie. 
                            <strong>Te produkty NIE ZOSTANĄ dodane do projektu.</strong> Lista będzie niekompletna (z wykrzyknikiem).
                        </p>
                    </div>
                `;
                // Odblokuj przycisk
                document.getElementById('confirm-list-load').disabled = false;
                document.getElementById('confirm-list-load').classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                // Odblokuj przycisk
                document.getElementById('confirm-list-load').disabled = false;
                document.getElementById('confirm-list-load').classList.remove('opacity-50', 'cursor-not-allowed');
            }
            
            document.getElementById('list-preview-content').innerHTML = html;
            document.getElementById('list-preview-modal').classList.remove('hidden');
        })
        .catch(error => {
            alert('Błąd podczas ładowania podglądu listy: ' + error.message);
        });
}

document.getElementById('confirm-list-load').addEventListener('click', function() {
    if (!selectedListId) {
        alert('Nie wybrano listy');
        return;
    }
    
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Dodawanie...';
    
    // Wyślij żądanie dodania produktów z listy
    fetch(`/projekty/{{ $project->id }}/load-list`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            list_id: selectedListId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = data.message;
            if (!data.is_complete && data.missing_count > 0) {
                message += `\n\n⚠ Uwaga: ${data.missing_count} produktów nie zostało dodanych (brak na magazynie).`;
            }
            alert(message);
            window.location.reload();
        } else {
            alert('Błąd: ' + (data.message || 'Nie udało się dodać produktów'));
            btn.disabled = false;
            btn.textContent = 'Dodaj produkty do projektu';
        }
    })
    .catch(error => {
        alert('Błąd połączenia: ' + error.message);
        btn.disabled = false;
        btn.textContent = 'Dodaj produkty do projektu';
    });
});

// ===== OBSŁUGA CHECKBOXÓW I USUWANIA PRODUKTÓW =====
@if(auth()->user() && auth()->user()->is_admin)
const selectAllCheckbox = document.getElementById('select-all-checkbox');
const productCheckboxes = document.querySelectorAll('.product-checkbox');
const selectAllBtn = document.getElementById('select-all-products');
const deselectAllBtn = document.getElementById('deselect-all-products');
const deleteBtn = document.getElementById('delete-selected-products');
const selectedCountSpan = document.getElementById('selected-count');

function updateSelectedCount() {
    const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
    selectedCountSpan.textContent = checkedCount;
    deleteBtn.disabled = checkedCount === 0;
    
    // Aktualizuj stan checkboxa "zaznacz wszystkie"
    if (checkedCount === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedCount === productCheckboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    }
}

// Checkbox "zaznacz wszystkie" w nagłówku
if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
        productCheckboxes.forEach(cb => {
            cb.checked = this.checked;
        });
        updateSelectedCount();
    });
}

// Checkboxy produktów
productCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

// Przycisk "Zaznacz wszystkie"
if (selectAllBtn) {
    selectAllBtn.addEventListener('click', function() {
        productCheckboxes.forEach(cb => cb.checked = true);
        updateSelectedCount();
    });
}

// Przycisk "Odznacz wszystkie"
if (deselectAllBtn) {
    deselectAllBtn.addEventListener('click', function() {
        productCheckboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
    });
}

// Przycisk "Usuń zaznaczone"
if (deleteBtn) {
    deleteBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Nie zaznaczono żadnych produktów');
            return;
        }
        
        const partIds = Array.from(checkedBoxes).map(cb => cb.dataset.partId);
        const partNames = Array.from(checkedBoxes).map(cb => cb.dataset.partName).join(', ');
        
        if (!confirm(`Czy na pewno chcesz usunąć ${checkedBoxes.length} zaznaczonych produktów z projektu?\n\nProdukty: ${partNames}\n\nUWAGA: Usunięte zostaną WSZYSTKIE pobrania tych produktów w tym projekcie!`)) {
            return;
        }
        
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Usuwanie...';
        
        fetch(`/projekty/{{ $project->id }}/delete-products`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                part_ids: partIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Błąd: ' + (data.message || 'Nie udało się usunąć produktów'));
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '🗑️ Usuń zaznaczone (<span id="selected-count">' + checkedBoxes.length + '</span>)';
            }
        })
        .catch(error => {
            alert('Błąd połączenia: ' + error.message);
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '🗑️ Usuń zaznaczone (<span id="selected-count">' + checkedBoxes.length + '</span>)';
        });
    });
}

// Inicjalizacja licznika
updateSelectedCount();
@endif
</script>

</body>

</html>
</div>
