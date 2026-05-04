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

<div class="w-full p-4 lg:p-6 mt-2">
    
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
    <div class="bg-white border border-gray-200 rounded-lg px-5 py-4 mb-6 shadow-sm">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
            <div class="flex items-baseline gap-1">
                <span class="text-sm font-semibold text-gray-500">Nr projektu:</span>
                <span class="text-sm font-medium text-gray-900">{{ $project->project_number }}</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-sm font-semibold text-gray-500">Nazwa:</span>
                <span class="text-sm font-medium text-gray-900">{{ $project->name }}</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-sm font-semibold text-gray-500">Budżet:</span>
                <span class="text-sm font-medium text-gray-900">{{ $project->budget ? number_format($project->budget, 2, ',', ' ') . ' PLN' : '-' }}</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-sm font-semibold text-gray-500">Status:</span>
                <span class="text-sm font-medium text-gray-900">
                    @if($project->status === 'in_progress') W toku
                    @elseif($project->status === 'warranty') Na gwarancji
                    @elseif($project->status === 'archived') Archiwalny
                    @endif
                </span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-sm font-semibold text-gray-500">Osoba odpowiedzialna:</span>
                <span class="text-sm font-medium text-gray-900">
                    @if(isset($project->responsibleUser) && $project->responsibleUser)
                        {{ $project->responsibleUser->name ?? ($project->responsibleUser->short_name ?? '-') }}
                    @else
                        -
                    @endif
                </span>
            </div>
            {{-- PRZYCISKI AKCJI inline --}}
            <div class="ml-auto flex gap-2 items-center">
                @if(!in_array($project->status, ['warranty','archived']))
                    <a href="{{ route('magazyn.editProject', $project->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-semibold">
                        Edytuj projekt
                    </a>
                    @if($project->status === 'in_progress')
                    <button id="finish-project-btn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm font-semibold">
                        Zakończ projekt
                    </button>
                    @endif
                @else
                    <span class="text-gray-400 text-sm">Projekt zamknięty – tylko podgląd</span>
                @endif
            </div>
        </div>
    </div>

    {{-- KONTENER NA PRZESUWALNE SEKCJE --}}
    @php
        $visibleSections = $projectVisibleSections ?? ['pickup', 'changes', 'summary', 'frappe', 'finance', 'project_orders'];
    @endphp
    <div id="sortable-sections" class="space-y-8">
    
    {{-- SEKCJA 0: POBIERANIE --}}
        @if(in_array('pickup', $visibleSections, true))
    <div id="section-pickup" class="sortable-section bg-white border-2 border-indigo-200 rounded-lg p-4 shadow-sm" data-order="0">
        <div class="flex items-center gap-3 mb-4">
            <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600" draggable="true" title="Przeciągnij, aby zmienić kolejność">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </div>
            <button type="button" id="toggle-pickup-section" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <span id="toggle-pickup-arrow">▼</span>
            </button>
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <span class="text-indigo-600">📦</span>
                Pobieranie produktów
            </h3>
        </div>
        <div id="pickup-section-content">
            {{-- AUTORYZACJA POBRAŃ --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <span class="text-sm font-semibold text-gray-600">Autoryzacja pobrań:</span>
                @if($project->status === 'warranty' || $project->status === 'archived')
                    <div class="flex items-center gap-2 mt-2">
                        <input type="checkbox" disabled {{ $project->requires_authorization ? 'checked' : '' }} class="w-4 h-4 cursor-not-allowed opacity-50">
                        <label class="text-sm font-medium text-gray-400">
                            Pobranie produktów wymaga autoryzacji przez skanowanie
                        </label>
                    </div>
                    <span class="text-orange-600 font-semibold">✓ Wymagana</span>
                    <p class="text-xs text-gray-400 mt-1">Projekt zamknięty – nie można zmienić autoryzacji.</p>
                @else
                    @php
                        $canChangeAuthorization = auth()->check() && auth()->user()->is_admin;
                        $hasUnauthorized = \App\Models\ProjectRemoval::where('project_id', $project->id)->where('authorized', false)->exists();
                    @endphp
                    @if($hasUnauthorized)
                        {{-- Zablokuj zmianę gdy są nieautoryzowane produkty --}}
                        <div class="flex items-center gap-2 mt-2">
                            <input type="checkbox" disabled checked class="w-4 h-4 cursor-not-allowed opacity-50">
                            <label class="text-sm font-medium text-gray-400">
                                Pobranie produktów wymaga autoryzacji przez skanowanie
                            </label>
                        </div>
                        <p class="text-xs text-red-500 mt-1">⚠️ Nie można wyłączyć autoryzacji - masz produkty oczekujące na autoryzację. Najpierw zautoryzuj lub usuń te produkty.</p>
                        <span class="text-orange-600 font-semibold">✓ Wymagana</span>
                    @elseif(!$canChangeAuthorization)
                        <div class="flex items-center gap-2 mt-2">
                            <input type="checkbox" disabled {{ $project->requires_authorization ? 'checked' : '' }} class="w-4 h-4 cursor-not-allowed opacity-50">
                            <label class="text-sm font-medium text-gray-400">
                                Pobranie produktów wymaga autoryzacji przez skanowanie
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">🔒 Tylko administrator może zmienić to ustawienie.</p>
                        @if($project->requires_authorization)
                            <span class="text-orange-600 font-semibold">✓ Wymagana</span>
                        @else
                            <span class="text-gray-600">Nie wymagana</span>
                        @endif
                    @else
                        <form method="POST" action="{{ route('magazyn.projects.toggleAuthorization', $project->id) }}">
                            @csrf
                            <div class="flex items-center gap-2 mt-2">
                                <input type="checkbox" name="requires_authorization" id="requires_authorization" value="1" class="w-4 h-4 cursor-pointer" {{ $project->requires_authorization ? 'checked' : '' }}>
                                <label for="requires_authorization" class="text-sm font-medium cursor-pointer">
                                    Pobranie produktów wymaga autoryzacji przez skanowanie
                                </label>
                            </div>
                            <button type="submit" class="mt-2 px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Zapisz zmianę</button>
                            @if($project->requires_authorization)
                                <span class="ml-3 text-orange-600 font-semibold">✓ Wymagana</span>
                            @else
                                <span class="ml-3 text-gray-600">Nie wymagana</span>
                            @endif
                        </form>
                        <p class="text-xs text-gray-500 mt-1">Jeśli zaznaczone, produkty pobrane do projektu nie zostaną odjęte ze stanu magazynu dopóki nie zostaną zeskanowane</p>
                    @endif
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
                            @php
                                $hasMissingItems = !$loadedListData->is_complete && !empty($loadedListData->missing_items);
                                $hasUnauthorizedItems = isset($loadedListData->unauthorized_count) && $loadedListData->unauthorized_count > 0;
                                $isFullyAuthorized = !$hasMissingItems && !$hasUnauthorizedItems;
                            @endphp
                            @if($isFullyAuthorized)
                                <span class="ml-2 px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full font-semibold">✓ Kompletna i w pełni zautoryzowana</span>
                            @elseif($hasMissingItems)
                                <span class="ml-2 px-2 py-0.5 bg-red-200 text-red-800 text-xs rounded-full font-semibold">⚠ Lista niekompletna</span>
                                <button type="button" class="ml-2 text-orange-600 hover:text-orange-800 font-bold text-xl" onclick="showMissingItems({{ $loadedListData->id }})" title="Kliknij aby zobaczyć czego brakuje">❗</button>
                                @if(auth()->user() && auth()->user()->is_admin && !in_array($project->status, ['warranty','archived']))
                                <form method="POST" action="{{ route('magazyn.projects.authorizeList', [$project->id, $loadedListData->id]) }}" class="ml-2" onsubmit="this.method='POST';">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 bg-orange-600 text-white rounded hover:bg-orange-700 text-xs font-semibold" title="Autoryzuj tylko brakujące produkty, które są dostępne na magazynie">
                                        🔐 Autoryzuj
                                    </button>
                                </form>
                                @endif
                            @else
                                <span class="ml-2 px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs rounded-full font-semibold">⚠ Kompletna, ale nie w pełni zautoryzowana</span>
                                @if(auth()->user() && auth()->user()->is_admin && !in_array($project->status, ['warranty','archived']))
                                <form method="POST" action="{{ route('magazyn.projects.authorizeList', [$project->id, $loadedListData->id]) }}" class="ml-2" onsubmit="this.method='POST';">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 bg-orange-600 text-white rounded hover:bg-orange-700 text-xs font-semibold" title="Przejdź do autoryzacji przez skanowanie tej listy">
                                        📱 Skanuj produkty z listy
                                    </button>
                                </form>
                                @endif
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
                            <div id="missing-items-{{ $loadedListData->id }}" class="mt-2 p-2 bg-white border border-yellow-300 rounded text-xs">
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
                                            <span class="ml-2 px-3 py-1 {{ $currentStock > 0 ? 'bg-yellow-200 text-yellow-900' : 'bg-red-200 text-red-800' }} rounded text-xs font-semibold">
                                                {{ $currentStock > 0 ? 'Dostępny częściowo — użyj przycisku Autoryzuj przy liście' : '✗ Brak na magazynie' }}
                                            </span>
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
            
            {{-- PRZYCISKI POBIERANIA --}}
            <div class="mt-4 flex gap-2 justify-end">
                @if(!in_array($project->status, ['warranty','archived']))
                    <button type="button" id="choose-list-btn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        📋 Wybierz listę projektową
                    </button>
                    <a href="{{ route('magazyn.projects.pickup', $project->id) }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                        ➖ Pobierz produkty do projektu
                    </a>
                @else
                    <span class="text-gray-400 text-sm">Projekt zamknięty – brak możliwości pobierania produktów.</span>
                @endif
            </div>
        </div>
    </div>
    @endif
    {{-- KONIEC SEKCJI 0 --}}

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
        
        
        {{-- SEKCJA 1: ZMIANY W MAGAZYNIE --}}
        @if(in_array('changes', $visibleSections, true))
        <div id="section-changes" class="sortable-section bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm" data-order="2">
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
        @endif
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
    @if(in_array('summary', $visibleSections, true))
    <div id="section-summary" class="sortable-section bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm" data-order="3">
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
            <div class="flex items-center gap-2">
                <a href="{{ route('magazyn.projects.exportProductsXlsx', $project->id) }}"
                   class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 text-sm font-semibold">
                    📊 Eksport Excel
                </a>
                <a href="{{ route('magazyn.projects.exportProductsCsv', $project->id) }}"
                   class="bg-amber-600 text-white px-3 py-2 rounded hover:bg-amber-700 text-xs font-semibold">
                    CSV awaryjny
                </a>
                <a href="{{ route('magazyn.projects.exportProductsDiagnostics', $project->id) }}"
                   class="bg-slate-600 text-white px-3 py-2 rounded hover:bg-slate-700 text-xs font-semibold">
                    Diagnostyka eksportu
                </a>
            </div>
        </div>
        <div id="summary-section-content">
        @php
            // Grupowanie produktów i sumowanie ilości
            $summary = $removals->where('status', 'added')->where('authorized', true)->groupBy('part_id')->map(function($group) {
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
            <button type="button" id="delete-selected-products" class="px-3 py-1 bg-orange-600 text-white rounded hover:bg-orange-700 text-sm font-semibold" disabled>
                ↩️ Zwróć do magazynu (<span id="selected-count">0</span>)
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
    @endif
    {{-- KONIEC SEKCJI 2 --}}
    
    {{-- SEKCJA 3: GANTT FRAPPE --}}
    @if(in_array('frappe', $visibleSections, true))
    <div id="section-frappe" class="sortable-section bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm" data-order="4">
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
                <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Quarter Day">Ćwierć dnia</button>
                <button class="frappe-view-btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm" data-mode="Half Day">Pół dnia</button>
                <button class="frappe-view-btn bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm" data-mode="Day">Dzień</button>
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
    @endif
    {{-- KONIEC SEKCJI 3 --}}
    
    {{-- SEKCJA 4: HARMONOGRAM FINANSOWY --}}
    @if(in_array('finance', $visibleSections, true))
    <div id="section-finance" class="sortable-section bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm" data-order="5">
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

            <div class="w-full sm:max-w-[33.333%] mb-4">
                <table class="w-auto text-sm">
                    <tbody>
                        <tr class="bg-blue-50 text-blue-900">
                            <td class="px-3 py-2 font-semibold">Wartość projektu:</td>
                            <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float)($financeSummary['project_value'] ?? 0), 2, ',', ' ') }} zł</td>
                        </tr>
                        <tr class="bg-indigo-50 text-indigo-900">
                            <td class="px-3 py-2 font-semibold">Faktury kosztowe:</td>
                            <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float)($financeSummary['cost_invoices'] ?? 0), 2, ',', ' ') }} zł</td>
                        </tr>
                        <tr class="bg-emerald-50 text-emerald-900">
                            <td class="px-3 py-2 font-semibold">Koszty faktury wystawione:</td>
                            <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float)($financeSummary['issued_invoices'] ?? 0), 2, ',', ' ') }} zł</td>
                        </tr>
                        <tr class="bg-amber-50 text-amber-900">
                            <td class="px-3 py-2 font-semibold">Materiały i usługi zamówione:</td>
                            <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float)($financeSummary['ordered_materials_services'] ?? 0), 2, ',', ' ') }} zł</td>
                        </tr>
                        <tr class="bg-rose-50 text-rose-900">
                            <td class="px-3 py-2 font-semibold">Bilans:</td>
                            <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format((float)($financeSummary['balance'] ?? 0), 2, ',', ' ') }} zł</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- WYKRES CASHFLOW --}}
            @php
                $chartCostRows = collect($importedCostRows ?? [])
                    ->filter(fn($r) => ($r['date'] ?? '') !== '' && ($r['amount_net'] ?? '') !== '')
                    ->map(fn($r) => ['date' => $r['date'], 'amount' => (float) $r['amount_net'], 'type' => 'expense', 'label' => 'Faktura kosztowa'])
                    ->values()->all();
                $chartIssuedRows = collect($issuedInvoiceRows ?? [])
                    ->filter(fn($r) => ($r['date'] ?? '') !== '' && ($r['amount_net'] ?? '') !== '')
                    ->map(fn($r) => ['date' => $r['date'], 'amount' => (float) $r['amount_net'], 'type' => ($r['status'] ?? '') === 'Planowana' ? 'planned_income' : 'income', 'label' => 'Faktura wystawiona'])
                    ->values()->all();
                $chartOrderRows = collect($orderRows ?? [])
                    ->filter(fn($r) => ($r['date'] ?? '') !== '' && ($r['amount_net'] ?? '') !== '')
                    ->map(fn($r) => ['date' => $r['date'], 'amount' => (float) $r['amount_net'], 'type' => 'expense', 'label' => 'Zamówienie'])
                    ->values()->all();
                $allChartData = array_merge($chartCostRows, $chartIssuedRows, $chartOrderRows);
                $hasChartData = !empty($allChartData);
            @endphp
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                <button type="button"
                    onclick="(function(btn){var p=btn.nextElementSibling;p.classList.toggle('hidden');btn.querySelector('span').textContent=p.classList.contains('hidden')?'▶':'▼';if(!p.classList.contains('hidden')&&!btn.dataset.cfInit){btn.dataset.cfInit='1';if(typeof window.initCashflowChart==='function')window.initCashflowChart();}})(this)"
                    class="flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-gray-900 w-full text-left mb-1">
                    <span>▶</span> Wykres kosztów i przychodów (cash flow)
                </button>
                <div class="hidden">
                    @if($hasChartData)
                    {{-- Kontrolki okresu i nawigacji --}}
                    <div class="flex flex-wrap items-center gap-1 mt-3 mb-2">
                        <div class="flex rounded border border-gray-200 overflow-hidden text-xs font-medium">
                            <button class="cashflow-mode-btn px-3 py-1.5 hover:bg-gray-100 border-r border-gray-200" data-mode="day">Dzień</button>
                            <button class="cashflow-mode-btn px-3 py-1.5 hover:bg-gray-100 border-r border-gray-200" data-mode="week">Tydzień</button>
                            <button class="cashflow-mode-btn px-3 py-1.5 bg-indigo-600 text-white border-r border-gray-200" data-mode="month">Miesiąc</button>
                            <button class="cashflow-mode-btn px-3 py-1.5 hover:bg-gray-100" data-mode="year">Rok</button>
                        </div>
                        <button id="cashflow-prev" class="px-3 py-1.5 bg-gray-100 rounded border border-gray-200 text-xs hover:bg-gray-200 font-medium ml-1">‹ Wcześniej</button>
                        <span id="cashflow-range-label" class="text-xs text-gray-600 font-semibold px-2 py-1 bg-gray-50 rounded border border-gray-200 min-w-[170px] text-center"></span>
                        <button id="cashflow-next" class="px-3 py-1.5 bg-gray-100 rounded border border-gray-200 text-xs hover:bg-gray-200 font-medium">Dalej ›</button>
                        <button id="cashflow-reset" class="px-3 py-1.5 bg-gray-100 rounded border border-gray-200 text-xs hover:bg-gray-200 ml-auto">↺ Resetuj</button>
                    </div>
                    {{-- Główny wykres --}}
                    <div style="position:relative; height:260px;">
                        <canvas id="cashflow-chart"></canvas>
                    </div>
                    {{-- Przegląd / brush --}}
                    <div class="mt-2">
                        <p class="text-xs text-gray-400 mb-1">Przegląd całości — przeciągnij, aby przybliżyć zakres:</p>
                        <div id="cashflow-brush-wrap" style="position:relative; height:54px; user-select:none;">
                            <canvas id="cashflow-overview-chart" style="position:absolute; top:0; left:0; width:100%; height:100%;"></canvas>
                            <div id="cashflow-brush-sel" style="position:absolute; top:0; height:100%; display:none; background:rgba(99,102,241,0.14); border-left:2px solid #6366f1; border-right:2px solid #6366f1; pointer-events:none;"></div>
                            <div id="cashflow-brush-overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; cursor:crosshair;"></div>
                        </div>
                    </div>
                    <script>
                    (function(){
                        const rawData = @json($allChartData);
                        if (!rawData || !rawData.length) return;
                        rawData.sort((a,b) => a.date.localeCompare(b.date));

                        const minDateStr = rawData[0].date;
                        const maxDateStr = rawData[rawData.length-1].date;
                        const minTs = +new Date(minDateStr + 'T00:00:00');
                        const maxTs = +new Date(maxDateStr + 'T00:00:00');

                        const MONTHS_PL = ['Sty','Lut','Mar','Kwi','Maj','Cze','Lip','Sie','Wrz','Paź','Lis','Gru'];

                        function parseDate(str) {
                            const p = str.split('-');
                            return new Date(+p[0], +p[1]-1, +p[2]);
                        }
                        function fmtDate(d) { return d.toLocaleDateString('pl-PL'); }
                        function fmtAmt(v) { return v.toFixed(2).replace('.',',') + ' zł'; }

                        function getPeriodKey(dateStr, mode) {
                            if (mode === 'day') return dateStr;
                            if (mode === 'month') return dateStr.slice(0,7);
                            if (mode === 'year') return dateStr.slice(0,4);
                            if (mode === 'week') {
                                const d = parseDate(dateStr);
                                const dayOff = (d.getDay()+6)%7;
                                const mon = new Date(d); mon.setDate(d.getDate()-dayOff);
                                const y=mon.getFullYear(), m=String(mon.getMonth()+1).padStart(2,'0'), dd=String(mon.getDate()).padStart(2,'0');
                                return y+'-'+m+'-'+dd;
                            }
                        }

                        function getLabelForKey(key, mode) {
                            if (mode === 'day') return fmtDate(parseDate(key));
                            if (mode === 'month') { const [y,m]=key.split('-'); return MONTHS_PL[+m-1]+' '+y; }
                            if (mode === 'year') return key;
                            if (mode === 'week') {
                                const d=parseDate(key), e=new Date(d); e.setDate(d.getDate()+6);
                                return fmtDate(d)+'–'+fmtDate(e);
                            }
                        }

                        function groupData(mode, filterStart, filterEnd) {
                            const map = new Map();
                            rawData.forEach(t => {
                                const ts = +new Date(t.date+'T00:00:00');
                                if (filterStart && ts < filterStart.getTime()) return;
                                if (filterEnd && ts > filterEnd.getTime()) return;
                                const key = getPeriodKey(t.date, mode);
                                if (!map.has(key)) map.set(key, {income:0, expense:0, planned:0});
                                const e = map.get(key);
                                if (t.type === 'income') e.income += t.amount;
                                else if (t.type === 'planned_income') e.planned += t.amount;
                                else e.expense += t.amount;
                            });
                            return [...map.entries()].sort((a,b) => a[0].localeCompare(b[0])).map(([k,v]) => ({key:k,...v}));
                        }

                        const overviewGrouped = groupData('month', null, null);

                        let currentMode = 'month';
                        let windowStart = null, windowEnd = null;
                        let mainChart = null, overviewChart = null;

                        function getDefaultWindow(mode) {
                            const minD = parseDate(minDateStr), maxD = parseDate(maxDateStr);
                            if (mode === 'year') return { start: new Date(minD.getFullYear(),0,1), end: new Date(maxD.getFullYear(),11,31) };
                            const end = new Date(maxD);
                            let start;
                            if (mode === 'month') { start = new Date(end.getFullYear()-1, end.getMonth()+1, 1); }
                            else if (mode === 'week') { start = new Date(end); start.setDate(end.getDate()-83); }
                            else { start = new Date(end); start.setDate(end.getDate()-29); }
                            return { start, end };
                        }

                        function buildMainData() {
                            const g = groupData(currentMode, windowStart, windowEnd);
                            const labels = g.map(d => getLabelForKey(d.key, currentMode));
                            const income = g.map(d => d.income);
                            const expense = g.map(d => d.expense);
                            const planned = g.map(d => d.planned);
                            const balance = [];
                            let cum = 0;
                            g.forEach(d => { cum += d.income + d.planned - d.expense; balance.push(cum); });
                            return { labels, income, expense, planned, balance };
                        }

                        function updateRangeLabel() {
                            const el = document.getElementById('cashflow-range-label');
                            if (!el) return;
                            if (currentMode === 'year') {
                                el.textContent = windowStart.getFullYear() + ' – ' + windowEnd.getFullYear();
                            } else {
                                el.textContent = fmtDate(windowStart) + ' – ' + fmtDate(windowEnd);
                            }
                        }

                        function updateModeButtons() {
                            document.querySelectorAll('.cashflow-mode-btn').forEach(btn => {
                                const active = btn.dataset.mode === currentMode;
                                btn.classList.toggle('bg-indigo-600', active);
                                btn.classList.toggle('text-white', active);
                                btn.classList.remove('hover:bg-gray-100');
                                if (!active) btn.classList.add('hover:bg-gray-100');
                            });
                        }

                        function updateMainChart() {
                            if (!mainChart) return;
                            const { labels, income, expense, planned, balance } = buildMainData();
                            mainChart.data.labels = labels;
                            mainChart.data.datasets[0].data = income;
                            mainChart.data.datasets[1].data = expense;
                            mainChart.data.datasets[2].data = planned;
                            mainChart.data.datasets[3].data = balance;
                            mainChart.update('none');
                        }

                        function initMainChart() {
                            const canvas = document.getElementById('cashflow-chart');
                            if (!canvas || !window.Chart) return;
                            const { labels, income, expense, planned, balance } = buildMainData();
                            mainChart = new Chart(canvas, {
                                type: 'bar',
                                data: {
                                    labels,
                                    datasets: [
                                        { label: 'Przychody', data: income, backgroundColor: 'rgba(34,197,94,0.6)', borderColor: 'rgb(34,197,94)', borderWidth: 1, order: 2 },
                                        { label: 'Wydatki', data: expense, backgroundColor: 'rgba(239,68,68,0.6)', borderColor: 'rgb(239,68,68)', borderWidth: 1, order: 2 },
                                        { label: 'Planowane przychody', data: planned, backgroundColor: 'rgba(139,92,246,0.35)', borderColor: 'rgb(139,92,246)', borderWidth: 2, borderDash: [4,4], order: 2 },
                                        { label: 'Bilans narastająco', data: balance, type: 'line', borderColor: 'rgb(99,102,241)', backgroundColor: 'rgba(99,102,241,0.07)', borderWidth: 2, fill: true, tension: 0.3, pointRadius: 3, order: 1 }
                                    ]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false,
                                    animation: { duration: 200 },
                                    plugins: {
                                        legend: { display: true, position: 'top' },
                                        tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + fmtAmt(ctx.parsed.y) } }
                                    },
                                    scales: { y: { ticks: { callback: v => v.toFixed(0) + ' zł' } } }
                                }
                            });
                        }

                        function initOverviewChart() {
                            const canvas = document.getElementById('cashflow-overview-chart');
                            if (!canvas || !window.Chart) return;
                            overviewChart = new Chart(canvas, {
                                type: 'bar',
                                data: {
                                    labels: overviewGrouped.map(d => d.key),
                                    datasets: [
                                        { data: overviewGrouped.map(d => d.income), backgroundColor: 'rgba(34,197,94,0.45)', borderWidth: 0 },
                                        { data: overviewGrouped.map(d => d.expense), backgroundColor: 'rgba(239,68,68,0.4)', borderWidth: 0 },
                                        { data: overviewGrouped.map(d => d.planned), backgroundColor: 'rgba(139,92,246,0.35)', borderWidth: 0 }
                                    ]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false, animation: false,
                                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                                    scales: { x: { display: false, stacked: false }, y: { display: false } }
                                }
                            });
                        }

                        function fractionFromTs(ts) {
                            if (maxTs === minTs) return 0;
                            return Math.max(0, Math.min(1, (ts - minTs) / (maxTs - minTs)));
                        }
                        function tsFromFraction(frac) {
                            return minTs + frac * (maxTs - minTs);
                        }

                        function updateBrushSelection() {
                            const sel = document.getElementById('cashflow-brush-sel');
                            const wrap = document.getElementById('cashflow-brush-wrap');
                            if (!sel || !wrap || !windowStart || !windowEnd) return;
                            const W = wrap.offsetWidth; if (W === 0) return;
                            const f1 = fractionFromTs(windowStart.getTime());
                            const f2 = fractionFromTs(windowEnd.getTime());
                            sel.style.display = 'block';
                            sel.style.left = Math.round(f1 * W) + 'px';
                            sel.style.width = Math.max(4, Math.round((f2 - f1) * W)) + 'px';
                        }

                        function initBrush() {
                            const overlay = document.getElementById('cashflow-brush-overlay');
                            const wrap = document.getElementById('cashflow-brush-wrap');
                            if (!overlay || !wrap) return;
                            let dragging = false, dragF1 = 0;

                            function getF(e) {
                                const rect = wrap.getBoundingClientRect();
                                const cx = e.touches ? e.touches[0].clientX : e.clientX;
                                return Math.max(0, Math.min(1, (cx - rect.left) / wrap.offsetWidth));
                            }
                            function drawLive(f1, f2) {
                                const sel = document.getElementById('cashflow-brush-sel');
                                const W = wrap.offsetWidth;
                                const a = Math.min(f1,f2), b = Math.max(f1,f2);
                                sel.style.display = 'block';
                                sel.style.left = Math.round(a*W)+'px';
                                sel.style.width = Math.max(4, Math.round((b-a)*W))+'px';
                            }
                            function applyBrush(f1raw, f2raw) {
                                const f1 = Math.min(f1raw,f2raw), f2 = Math.max(f1raw,f2raw);
                                if (f2 - f1 < 0.01) return;
                                const t1 = tsFromFraction(f1), t2 = tsFromFraction(f2);
                                const spanDays = (t2 - t1) / 86400000;
                                if (spanDays <= 45) currentMode = 'day';
                                else if (spanDays <= 120) currentMode = 'week';
                                else if (spanDays <= 800) currentMode = 'month';
                                else currentMode = 'year';
                                windowStart = new Date(t1); windowEnd = new Date(t2);
                                updateModeButtons(); updateMainChart(); updateBrushSelection(); updateRangeLabel();
                            }

                            overlay.addEventListener('mousedown', e => { dragging=true; dragF1=getF(e); e.preventDefault(); });
                            document.addEventListener('mousemove', e => { if (!dragging) return; drawLive(dragF1, getF(e)); });
                            document.addEventListener('mouseup', e => { if (!dragging) return; dragging=false; applyBrush(dragF1, getF(e)); });
                            overlay.addEventListener('touchstart', e => { dragging=true; dragF1=getF(e); e.preventDefault(); }, {passive:false});
                            overlay.addEventListener('touchmove', e => { if (!dragging) return; drawLive(dragF1, getF(e)); e.preventDefault(); }, {passive:false});
                            overlay.addEventListener('touchend', e => {
                                if (!dragging) return; dragging=false;
                                const last = e.changedTouches && e.changedTouches[0];
                                if (!last) return;
                                const rect = wrap.getBoundingClientRect();
                                applyBrush(dragF1, Math.max(0, Math.min(1, (last.clientX-rect.left)/wrap.offsetWidth)));
                            });
                        }

                        window.initCashflowChart = function() {
                            const w = getDefaultWindow(currentMode);
                            windowStart = w.start; windowEnd = w.end;
                            initMainChart();
                            initOverviewChart();
                            setTimeout(function() {
                                updateBrushSelection();
                                updateRangeLabel();
                                updateModeButtons();
                                initBrush();
                            }, 60);

                            document.querySelectorAll('.cashflow-mode-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    currentMode = this.dataset.mode;
                                    const w = getDefaultWindow(currentMode);
                                    windowStart = w.start; windowEnd = w.end;
                                    updateModeButtons(); updateMainChart(); updateBrushSelection(); updateRangeLabel();
                                });
                            });
                            document.getElementById('cashflow-prev').addEventListener('click', function() {
                                if (currentMode === 'year') return;
                                const span = windowEnd.getTime() - windowStart.getTime();
                                windowStart = new Date(windowStart.getTime() - span);
                                windowEnd = new Date(windowEnd.getTime() - span);
                                updateMainChart(); updateBrushSelection(); updateRangeLabel();
                            });
                            document.getElementById('cashflow-next').addEventListener('click', function() {
                                if (currentMode === 'year') return;
                                const span = windowEnd.getTime() - windowStart.getTime();
                                windowStart = new Date(windowStart.getTime() + span);
                                windowEnd = new Date(windowEnd.getTime() + span);
                                updateMainChart(); updateBrushSelection(); updateRangeLabel();
                            });
                            document.getElementById('cashflow-reset').addEventListener('click', function() {
                                const w = getDefaultWindow(currentMode);
                                windowStart = w.start; windowEnd = w.end;
                                updateMainChart(); updateBrushSelection(); updateRangeLabel();
                            });
                        };
                    })();
                    </script>
                    @else
                    <p class="text-sm text-gray-500 mt-2">Brak danych do wyświetlenia wykresu. Dodaj faktury kosztowe, wystawione lub zamówienia.</p>
                    @endif
                </div>
            </div>
            {{-- KONIEC WYKRESU CASHFLOW --}}

            <div class="mb-4 border-b border-gray-200">
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="finance-tab-btn px-4 py-2 rounded-t bg-white border border-gray-200 border-b-white text-sm font-semibold" data-finance-tab-target="costs">Faktury kosztowe ({{ number_format((float)($financeSummary['cost_invoices'] ?? 0), 2, ',', ' ') }} zł)</button>
                    <button type="button" class="finance-tab-btn px-4 py-2 rounded-t bg-gray-100 border border-gray-200 text-sm" data-finance-tab-target="issued">Faktury wystawione ({{ number_format((float)($financeSummary['issued_invoices'] ?? 0), 2, ',', ' ') }} zł)</button>
                    <button type="button" class="finance-tab-btn px-4 py-2 rounded-t bg-gray-100 border border-gray-200 text-sm" data-finance-tab-target="orders">Zamówienia ({{ number_format((float)($financeSummary['ordered_materials_services'] ?? 0), 2, ',', ' ') }} zł)</button>
                </div>
            </div>

            <div id="finance-tab-costs" class="finance-tab-content">

            @php
                $canImportProjectCostsExcel = auth()->check() && (auth()->user()->is_admin || auth()->user()->can_import_project_costs_excel);
                $existingCostGroups = $importedCostGroups ?? [];
                $importedCostGroupSummariesData = $importedCostGroupSummaries ?? [];
            @endphp

            @if($canImportProjectCostsExcel)
            @if(($hasFinanceGroupColumn ?? false) && !empty($importedCostGroupSummariesData))
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-gray-800 mb-3">Grupy kosztów</h4>
                @error('group_action')
                    <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">{{ $message }}</div>
                @enderror
                @error('group_name')
                    <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">{{ $message }}</div>
                @enderror
                @error('new_group_name')
                    <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">{{ $message }}</div>
                @enderror

                @php
                    $groupRowStyles = [
                        'bg-blue-50 text-blue-900',
                        'bg-indigo-50 text-indigo-900',
                        'bg-emerald-50 text-emerald-900',
                        'bg-amber-50 text-amber-900',
                        'bg-rose-50 text-rose-900',
                    ];
                @endphp

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody>
                            @foreach($importedCostGroupSummariesData as $summaryIndex => $groupSummary)
                                @php
                                    $rowClass = $groupRowStyles[$summaryIndex % count($groupRowStyles)];
                                    $groupName = (string) ($groupSummary['group'] ?? '');
                                    $groupAmount = (float) ($groupSummary['total_amount'] ?? 0);
                                @endphp
                                <tr class="{{ $rowClass }} border-b border-white/60">
                                    <td class="px-3 py-2 font-semibold whitespace-nowrap">{{ $groupName }}</td>
                                    <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format($groupAmount, 2, ',', ' ') }} zł</td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-col md:flex-row gap-2 md:items-center md:justify-end">
                                            <form action="{{ route('magazyn.projects.importCostsExcel.groups', $project->id) }}" method="POST" class="flex gap-2 items-center">
                                                @csrf
                                                <input type="hidden" name="group_action" value="rename">
                                                <input type="hidden" name="group_name" value="{{ $groupName }}">
                                                <input type="text" name="new_group_name" required class="px-2 py-1.5 border border-gray-300 rounded bg-white text-xs text-gray-900" placeholder="Nowa nazwa grupy">
                                                <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-semibold">✏️ Edytuj</button>
                                            </form>

                                            <form action="{{ route('magazyn.projects.importCostsExcel.groups', $project->id) }}" method="POST" onsubmit="return confirm('Usunąć grupę ze wszystkich przypisanych pozycji?')">
                                                @csrf
                                                <input type="hidden" name="group_action" value="delete">
                                                <input type="hidden" name="group_name" value="{{ $groupName }}">
                                                <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-xs font-semibold">🗑️ Usuń</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-gray-800 mb-2">
                    Import kosztów z Excela
                    <span class="cursor-help font-normal text-sm ml-1" title="Oczekiwane kolumny: Data / Data księgowania, Podmiot / Przedmiot / Dostawca, Dokument, Kwota netto, Opis, Status, Data płatności. Inne kolumny są ignorowane, a brakujące kolumny zostaną uzupełnione pustą wartością.">ℹ️</span>
                </h4>
                @if(!($hasFinanceGroupColumn ?? false))
                    <div class="mb-3 p-3 rounded border border-amber-200 bg-amber-50 text-amber-800 text-sm">
                        Brak kolumny <strong>finance_group</strong> w bazie. Uruchom migracje na Railway, inaczej grupy nie będą zapisywane.
                    </div>
                @endif
                @if(!($hasProjectFinanceGroupsTable ?? false))
                    <div class="mb-3 p-3 rounded border border-amber-200 bg-amber-50 text-amber-800 text-sm">
                        Brak tabeli <strong>project_finance_groups</strong>. Uruchom migracje, aby działał przycisk „Dodaj grupę” i lista grup.
                    </div>
                @endif
                @if(session('success') && session('finance_import_feedback'))
                    <div class="mb-3 p-3 rounded border border-green-200 bg-green-50 text-green-800 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error') && session('finance_import_feedback'))
                    <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                @error('costs_file')
                    <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">
                        {{ $message }}
                    </div>
                @enderror
                @error('costs_group_existing')
                    <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">
                        {{ $message }}
                    </div>
                @enderror
                @error('group_name')
                    <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">
                        {{ $message }}
                    </div>
                @enderror
                <div class="flex flex-wrap gap-2 items-center">
                    <form id="costs-import-form" action="{{ route('magazyn.projects.importCostsExcel', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap gap-2 items-center">
                        @csrf
                        <input id="costs-file-input" type="file" name="costs_file" accept=".xlsx,.xls,.csv" required
                            class="px-3 py-2 border border-gray-300 rounded bg-white text-sm {{ empty($existingCostGroups) ? 'opacity-60 cursor-not-allowed' : '' }}"
                            {{ empty($existingCostGroups) ? 'disabled' : '' }}
                            title="{{ empty($existingCostGroups) ? 'Aby wybrać plik, najpierw dodaj co najmniej jedną grupę.' : '' }}">
                        <select id="costs-group-existing" name="costs_group_existing" required
                            class="px-3 py-2 border border-gray-300 rounded bg-white text-sm min-w-[180px]"
                            title="Najpierw dodaj grupę, potem wybierz ją z listy i zaimportuj plik.">
                            <option value="">Wybierz istniejącą grupę</option>
                            @foreach($existingCostGroups as $existingCostGroup)
                                <option value="{{ $existingCostGroup }}" {{ old('costs_group_existing') === $existingCostGroup ? 'selected' : '' }}>{{ $existingCostGroup }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm font-semibold">
                            📥 Importuj koszty
                        </button>
                    </form>
                    <div class="border-l border-gray-300 self-stretch mx-1 hidden md:block" style="min-height:2rem;"></div>
                    <form action="{{ route('magazyn.projects.importCostsExcel.groups.add', $project->id) }}" method="POST" class="flex gap-2 items-center">
                        @csrf
                        <input type="text" name="group_name" value="{{ old('group_name') }}" placeholder="Nowa nazwa grupy" class="px-2.5 py-1.5 border border-gray-300 rounded bg-white text-xs min-w-[180px]">
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-xs font-semibold whitespace-nowrap">➕ Dodaj grupę</button>
                    </form>
                </div>
                                @if(empty($existingCostGroups))
                    <p class="text-xs text-amber-700 mt-1">Aby wybrać plik, najpierw dodaj co najmniej jedną grupę.</p>
                @endif
            </div>
            @endif

            <div class="mb-3">
                <input
                    type="text"
                    id="imported-costs-search"
                    placeholder="Szukaj po wszystkich kolumnach..."
                    class="w-full md:w-96 px-3 py-2 border border-gray-300 rounded bg-white text-sm"
                    title="Wyszukiwarka aktywuje się po zaimportowaniu pierwszych pozycji."
                    {{ empty($importedCostRows ?? []) ? 'disabled' : '' }}
                >
            </div>

            {{-- PODSUMOWANIE DUPLIKATÓW IMPORTU --}}
            @if(!empty($importedCostMeta ?? []) && isset($importedCostMeta['existing_total_before']))
            @php
                $dupInserted = (int) ($importedCostMeta['inserted'] ?? 0);
                $dupSkipped = (int) ($importedCostMeta['duplicate_skipped'] ?? 0);
                $dupAmountInserted = (float) ($importedCostMeta['amount_inserted'] ?? 0);
                $dupAmountDuplicates = (float) ($importedCostMeta['amount_duplicates'] ?? 0);
                $dupExistingBefore = (float) ($importedCostMeta['existing_total_before'] ?? 0);
                $dupTotalAfter = $dupExistingBefore + $dupAmountInserted;
                $hasDuplicates = $dupSkipped > 0;
            @endphp
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-gray-800 mb-3">Podsumowanie importu</h4>
                <div class="overflow-x-auto">
                    <table class="w-auto text-sm">
                        <tbody>
                            <tr class="bg-gray-50">
                                <td class="px-3 py-2 font-semibold text-gray-700 whitespace-nowrap">Koszty w systemie przed importem:</td>
                                <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap text-gray-900">{{ number_format($dupExistingBefore, 2, ',', ' ') }} zł</td>
                                <td class="px-3 py-2 text-xs text-gray-500">suma wszystkich wydatków projektu</td>
                            </tr>
                            <tr class="bg-emerald-50">
                                <td class="px-3 py-2 font-semibold text-emerald-800 whitespace-nowrap">Zaimportowano nowych pozycji:</td>
                                <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap text-emerald-900">{{ number_format($dupAmountInserted, 2, ',', ' ') }} zł</td>
                                <td class="px-3 py-2 text-xs text-emerald-700">{{ $dupInserted }} {{ $dupInserted === 1 ? 'pozycja' : ($dupInserted >= 2 && $dupInserted <= 4 ? 'pozycje' : 'pozycji') }}</td>
                            </tr>
                            @if($hasDuplicates)
                            <tr class="bg-amber-50">
                                <td class="px-3 py-2 font-semibold text-amber-800 whitespace-nowrap">Pominięto duplikatów:</td>
                                <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap text-amber-900">{{ number_format($dupAmountDuplicates, 2, ',', ' ') }} zł</td>
                                <td class="px-3 py-2 text-xs text-amber-700">{{ $dupSkipped }} {{ $dupSkipped === 1 ? 'pozycja' : ($dupSkipped >= 2 && $dupSkipped <= 4 ? 'pozycje' : 'pozycji') }} — już w systemie (ten sam nr dokumentu i kwota netto)</td>
                            </tr>
                            @endif
                            <tr class="bg-blue-50 border-t-2 border-blue-200">
                                <td class="px-3 py-2 font-semibold text-blue-800 whitespace-nowrap">Koszty w systemie po imporcie:</td>
                                <td class="pl-2 pr-3 py-2 text-right font-bold whitespace-nowrap text-blue-900">{{ number_format($dupTotalAfter, 2, ',', ' ') }} zł</td>
                                <td class="px-3 py-2 text-xs text-blue-700">{{ $dupInserted > 0 ? '+' . number_format($dupAmountInserted, 2, ',', ' ') . ' zł względem stanu przed importem' : 'bez zmian' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if($hasDuplicates && !empty($importedCostDuplicates ?? []))
                <div class="mt-4">
                    <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden'); this.textContent = this.nextElementSibling.classList.contains('hidden') ? '▶ Pokaż pominięte duplikaty ({{ $dupSkipped }})' : '▼ Ukryj pominięte duplikaty ({{ $dupSkipped }})'"
                        class="text-sm font-semibold text-amber-700 hover:text-amber-900 flex items-center gap-1 mb-2">
                        ▶ Pokaż pominięte duplikaty ({{ $dupSkipped }})
                    </button>
                    <div class="hidden">
                        <p class="text-xs text-gray-500 mb-2">Poniższe pozycje zostały pominięte, ponieważ rekord z tym samym numerem dokumentu i kwotą netto już istnieje w systemie.</p>
                        <div class="w-full overflow-x-auto rounded border border-amber-200">
                            <table class="w-full table-auto text-xs">
                                <thead>
                                    <tr class="bg-amber-100 text-amber-900">
                                        <th class="px-2 py-2 text-left">Data</th>
                                        <th class="px-2 py-2 text-left">Dostawca</th>
                                        <th class="px-2 py-2 text-left">Dokument</th>
                                        <th class="px-2 py-2 text-left">Grupa</th>
                                        <th class="px-2 py-2 text-right">Kwota netto</th>
                                        <th class="px-2 py-2 text-left">Opis</th>
                                        <th class="px-2 py-2 text-left">Status</th>
                                        <th class="px-2 py-2 text-left">Data płatności</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($importedCostDuplicates as $dupRow)
                                    <tr class="bg-amber-50/60 even:bg-amber-50">
                                        <td class="px-2 py-1.5 whitespace-nowrap">{{ $dupRow['date'] ?? '' }}</td>
                                        <td class="px-2 py-1.5 truncate max-w-[150px]" title="{{ $dupRow['subject_or_supplier'] ?? '' }}">{{ $dupRow['subject_or_supplier'] ?? '' }}</td>
                                        <td class="px-2 py-1.5">{{ $dupRow['document'] ?? '' }}</td>
                                        <td class="px-2 py-1.5">{{ $dupRow['group'] ?? '' }}</td>
                                        <td class="px-2 py-1.5 text-right whitespace-nowrap font-semibold">{{ ($dupRow['amount_net'] ?? '') !== '' ? number_format((float) $dupRow['amount_net'], 2, ',', ' ') : '' }} zł</td>
                                        <td class="px-2 py-1.5 truncate max-w-[200px]" title="{{ $dupRow['description'] ?? '' }}">{{ \Illuminate\Support\Str::limit($dupRow['description'] ?? '', 40, '…') }}</td>
                                        <td class="px-2 py-1.5 whitespace-nowrap">{{ $dupRow['status'] ?? '' }}</td>
                                        <td class="px-2 py-1.5 whitespace-nowrap">{{ $dupRow['payment_date'] ?? '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif
            {{-- KONIEC PODSUMOWANIA DUPLIKATÓW --}}

            @if(!empty($importedCostRows ?? []))
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <h4 class="font-semibold text-gray-800">Zaimportowane koszty</h4>
                    @if(!empty($importedCostMeta ?? []))
                    <div class="text-xs text-gray-600">
                        Zaimportowano: <strong>{{ $importedCostMeta['inserted'] ?? 0 }}</strong>
                        @if(($importedCostMeta['skipped'] ?? 0) > 0)
                            | Pominięto: <strong>{{ $importedCostMeta['skipped'] ?? 0 }}</strong>
                        @endif
                        @if(($importedCostMeta['duplicate_skipped'] ?? 0) > 0)
                            | Duplikatów: <strong>{{ $importedCostMeta['duplicate_skipped'] }}</strong>
                        @endif
                    </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('magazyn.projects.importCostsExcel.bulk', $project->id) }}" id="imported-costs-bulk-form">
                    @csrf
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <button type="button" id="select-all-imported-costs" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm">Zaznacz wszystkie</button>
                        <button type="button" id="deselect-all-imported-costs" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm">Odznacz wszystkie</button>
                        <button type="button" id="enable-edit-selected" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Edytuj zaznaczone</button>
                        <button type="submit" name="bulk_action" value="update" id="save-selected-edits" class="hidden px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">Zapisz edytowane</button>
                        <button type="submit" name="bulk_action" value="mark_paid" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-sm" onclick="return confirm('Oznaczyć zaznaczone pozycje jako Opłacono?')">✅ Oznacz jako Opłacono</button>
                        <button type="submit" name="bulk_action" value="mark_unpaid" class="px-3 py-1.5 bg-amber-500 text-white rounded hover:bg-amber-600 text-sm" onclick="return confirm('Oznaczyć zaznaczone pozycje jako Nie opłacono?')">🕐 Oznacz jako Nie opłacono</button>
                        <button type="submit" name="bulk_action" value="delete" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm" onclick="return confirm('Czy na pewno usunąć zaznaczone pozycje?')">Usuń zaznaczone</button>
                    </div>

                    <div class="w-full overflow-hidden rounded border border-gray-200">
                        <table class="w-full table-fixed text-xs leading-4">
                            <thead>
                                <tr class="bg-gray-100 text-gray-800">
                                    <th class="px-3 py-2 text-center w-10">
                                        <input type="checkbox" id="header-select-imported-costs" class="w-4 h-4">
                                    </th>
                                    <th class="px-2 py-2 text-left imported-sortable-header cursor-pointer" data-sort-key="date">Data</th>
                                    <th class="px-2 py-2 text-left imported-sortable-header cursor-pointer" data-sort-key="supplier">Dostawca</th>
                                    <th class="px-2 py-2 text-left imported-sortable-header cursor-pointer" data-sort-key="document">Dokument</th>
                                    <th class="px-2 py-2 text-left imported-sortable-header cursor-pointer" data-sort-key="group">Grupa</th>
                                    <th class="px-2 py-2 text-right imported-sortable-header cursor-pointer" data-sort-key="amount">Kwota netto</th>
                                    <th class="px-2 py-2 text-left imported-sortable-header cursor-pointer" data-sort-key="description">Opis</th>
                                    <th class="px-2 py-2 text-left imported-sortable-header cursor-pointer" data-sort-key="status">Status</th>
                                    <th class="px-2 py-2 text-left imported-sortable-header cursor-pointer" data-sort-key="payment_date">Data płatności</th>
                                </tr>
                            </thead>
                            <tbody id="imported-costs-tbody">
                                @foreach(($importedCostRows ?? []) as $importedRow)
                                @php
                                    $rowId = (int) ($importedRow['id'] ?? 0);
                                @endphp
                                <tr class="bg-white even:bg-gray-50/80 hover:bg-blue-50/50 imported-cost-row" data-row-id="{{ $rowId }}">
                                    <td class="px-3 py-2 text-center align-middle">
                                        @if($rowId > 0)
                                        <input type="checkbox" name="selected_ids[]" value="{{ $rowId }}" class="imported-cost-row-checkbox w-4 h-4">
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 truncate whitespace-nowrap">
                                        <span class="import-display">{{ $importedRow['date'] ?? '' }}</span>
                                        <input type="date" name="rows[{{ $rowId }}][date]" value="{{ $importedRow['date'] ?? '' }}" class="import-input hidden w-full px-1.5 py-1 rounded border border-gray-300 bg-white text-xs" disabled>
                                    </td>
                                    <td class="px-2 py-2 truncate">
                                        <span class="import-display">{{ $importedRow['subject_or_supplier'] ?? '' }}</span>
                                        <input type="text" name="rows[{{ $rowId }}][subject_or_supplier]" value="{{ $importedRow['subject_or_supplier'] ?? '' }}" class="import-input hidden w-full px-1.5 py-1 rounded border border-gray-300 bg-white text-xs" placeholder="Dostawca" disabled>
                                    </td>
                                    <td class="px-2 py-2 truncate">
                                        <span class="import-display">{{ $importedRow['document'] ?? '' }}</span>
                                        <input type="text" name="rows[{{ $rowId }}][document]" value="{{ $importedRow['document'] ?? '' }}" class="import-input hidden w-full px-1.5 py-1 rounded border border-gray-300 bg-white text-xs" placeholder="Dokument" disabled>
                                    </td>
                                    <td class="px-2 py-2 truncate">
                                        <span class="import-display">{{ $importedRow['group'] ?? '' }}</span>
                                        <input type="text" name="rows[{{ $rowId }}][group]" value="{{ $importedRow['group'] ?? '' }}" class="import-input hidden w-full px-1.5 py-1 rounded border border-gray-300 bg-white text-xs" placeholder="Grupa" disabled>
                                    </td>
                                    <td class="px-2 py-2 text-right truncate whitespace-nowrap">
                                        <span class="import-display">{{ ($importedRow['amount_net'] ?? '') !== '' ? number_format((float) str_replace(',', '.', $importedRow['amount_net']), 2, ',', ' ') : '' }}</span>
                                        <input type="text" name="rows[{{ $rowId }}][amount_net]" value="{{ $importedRow['amount_net'] ?? '' }}" class="import-input hidden w-full px-1.5 py-1 rounded border border-gray-300 bg-white text-xs text-right" placeholder="0,00" disabled>
                                    </td>
                                    <td class="px-2 py-2 truncate">
                                        @php
                                            $importedDescription = (string) ($importedRow['description'] ?? '');
                                            $importedDescriptionShort = \Illuminate\Support\Str::limit($importedDescription, 40, '…');
                                        @endphp
                                        <span class="import-display" title="{{ $importedDescription }}">{{ $importedDescriptionShort }}</span>
                                        <input type="text" name="rows[{{ $rowId }}][description]" value="{{ $importedRow['description'] ?? '' }}" class="import-input hidden w-full px-1.5 py-1 rounded border border-gray-300 bg-white text-xs" placeholder="Opis" disabled>
                                    </td>
                                    <td class="px-2 py-2 truncate">
                                        @php
                                            $costStatus = $importedRow['status'] ?? 'Nie opłacono';
                                            $costStatusClass = $costStatus === 'Opłacono' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800';
                                        @endphp
                                        @if($rowId > 0)
                                        <select name="cost_status_display" data-url="{{ route('magazyn.projects.importedCosts.status', [$project->id, $rowId]) }}" class="import-display cost-status-select px-2 py-1 rounded border border-gray-300 text-xs font-semibold {{ $costStatusClass }}">
                                            <option value="Opłacono" {{ $costStatus === 'Opłacono' ? 'selected' : '' }}>Opłacono</option>
                                            <option value="Nie opłacono" {{ $costStatus === 'Nie opłacono' ? 'selected' : '' }}>Nie opłacono</option>
                                        </select>
                                        @else
                                        <span class="import-display px-2 py-0.5 rounded text-xs font-semibold {{ $costStatusClass }}">{{ $costStatus }}</span>
                                        @endif
                                        <select name="rows[{{ $rowId }}][status]" class="import-input hidden w-full px-1.5 py-1 rounded border border-gray-300 bg-white text-xs" disabled>
                                            <option value="Nie wysłano" {{ ($costStatus === 'Oczekiwanie' || $costStatus === 'Nie wysłano') ? 'selected' : '' }}>Nie wysłano</option>
                                            <option value="Wysłano" {{ ($costStatus === 'Nie opłacono' || $costStatus === 'Wysłano') ? 'selected' : '' }}>Wysłano</option>
                                            <option value="W trakcie" {{ ($costStatus === 'Opłacono' || $costStatus === 'W trakcie') ? 'selected' : '' }}>W trakcie</option>
                                            <option value="Zrealizowany" {{ ($costStatus === 'Zrealizowane' || $costStatus === 'Zrealizowany') ? 'selected' : '' }}>Zrealizowany</option>
                                        </select>
                                    </td>
                                    <td class="px-2 py-2 truncate whitespace-nowrap">
                                        <span class="import-display">{{ $importedRow['payment_date'] ?? '' }}</span>
                                        <input type="date" name="rows[{{ $rowId }}][payment_date]" value="{{ $importedRow['payment_date'] ?? '' }}" class="import-input hidden w-full px-1.5 py-1 rounded border border-gray-300 bg-white text-xs" disabled>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                @if(($importedCostMeta['preview_truncated'] ?? false) === true)
                <div class="text-xs text-amber-700 mt-2 flex items-center gap-1">
                    <span title="Tabela wyświetla maksymalnie 300 wierszy. Pełne dane są zapisane w bazie.">⚠️</span>
                    Wyświetlono pierwsze 300 wierszy podglądu.
                </div>
                @endif
            </div>

            {{-- UKRYTY FORMULARZ ZMIANY STATUSU KOSZTU --}}
            <form id="cost-status-change-form" method="POST" action="" class="hidden">
                @csrf
                <input type="hidden" name="cost_status" id="cost-status-change-value">
            </form>
            <script>
            (function() {
                function initCostStatusSelects() {
                    document.querySelectorAll('.cost-status-select').forEach(function(sel) {
                        sel.addEventListener('change', function() {
                            var form = document.getElementById('cost-status-change-form');
                            form.action = this.dataset.url;
                            document.getElementById('cost-status-change-value').value = this.value;
                            form.submit();
                        });
                    });
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initCostStatusSelects);
                } else {
                    initCostStatusSelects();
                }
            })();
            </script>
            @endif
            </div>

            <div id="finance-tab-issued" class="finance-tab-content hidden">
                <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Import faktur wystawionych z Excela</h4>
                    @error('issued_invoices_file')
                        <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">
                            {{ $message }}
                        </div>
                    @enderror
                    <form action="{{ route('magazyn.projects.issuedInvoices.importExcel', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-2 md:items-center">
                        @csrf
                        <input type="file" name="issued_invoices_file" accept=".xlsx,.xls,.csv" required class="px-3 py-2 border border-gray-300 rounded bg-white text-sm">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm font-semibold">
                            📥 Importuj faktury wystawione
                        </button>
                    </form>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Dodaj fakturę wystawioną</h4>
                    @if(session('success') && session('finance_issued_feedback'))
                        <div class="mb-3 p-3 rounded border border-green-200 bg-green-50 text-green-800 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error') && session('finance_issued_feedback'))
                        <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if($errors->any() && session('finance_issued_feedback'))
                        <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('magazyn.projects.issuedInvoices.store', $project->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <div class="finance-fixed-row">
                            <div class="finance-field finance-field-date">
                                <label class="block text-xs text-gray-600 mb-1">Data</label>
                                <input type="date" name="issued_invoice_date" value="{{ old('issued_invoice_date') }}" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                            </div>
                            <div class="finance-field finance-field-number">
                                <label class="block text-xs text-gray-600 mb-1">Nr faktury</label>
                                <input type="text" name="issued_invoice_number" value="{{ old('issued_invoice_number') }}" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="FV/2026/...">
                            </div>
                            <div class="finance-field finance-field-flex">
                                <label class="block text-xs text-gray-600 mb-1">Opis</label>
                                <input type="text" name="issued_invoice_description" value="{{ old('issued_invoice_description') }}" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="Opis faktury">
                            </div>
                        </div>

                        <div class="finance-fixed-row">
                            <div class="finance-field finance-field-amount">
                                <label class="block text-xs text-gray-600 mb-1">Kwota netto</label>
                                <input type="text" name="issued_invoice_amount_net" value="{{ old('issued_invoice_amount_net') }}" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="0,00">
                            </div>
                            <div class="finance-field finance-field-date">
                                <label class="block text-xs text-gray-600 mb-1">Termin płatności</label>
                                <input type="date" name="issued_invoice_payment_date" value="{{ old('issued_invoice_payment_date') }}" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                            </div>
                            <div class="finance-field finance-field-status">
                                <label class="block text-xs text-gray-600 mb-1">Status</label>
                                <select name="issued_invoice_status" required class="finance-status-select px-2 py-1.5 border border-gray-300 rounded text-sm">
                                    <option value="Nie opłacono" {{ old('issued_invoice_status', 'Nie opłacono') === 'Nie opłacono' ? 'selected' : '' }}>Nie opłacono</option>
                                    <option value="Opłacono" {{ old('issued_invoice_status') === 'Opłacono' ? 'selected' : '' }}>Opłacono</option>
                                    <option value="Planowana" {{ old('issued_invoice_status') === 'Planowana' ? 'selected' : '' }}>Planowana (brak fizycznej faktury)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm font-semibold">
                                💾 Zapisz fakturę
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4 text-sm text-gray-700">
                    <h4 class="font-semibold text-gray-800 mb-3">Faktury wystawione</h4>
                    <div class="w-full overflow-x-auto rounded border border-gray-200">
                        <table class="w-full table-auto text-xs">
                            <thead>
                                <tr class="bg-gray-100 text-gray-800">
                                    <th class="px-2 py-2 text-left">Data</th>
                                    <th class="px-2 py-2 text-left">Nr faktury</th>
                                    <th class="px-2 py-2 text-left">Opis</th>
                                    <th class="px-2 py-2 text-right">Kwota netto</th>
                                    <th class="px-2 py-2 text-left">Termin płatności</th>
                                    <th class="px-2 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($issuedInvoiceRows ?? []) as $issuedInvoice)
                                    @php
                                        $issuedStatus = (string) ($issuedInvoice['status'] ?? 'Nie opłacono');
                                        $issuedPaymentDate = $issuedInvoice['payment_date'] ?? null;
                                        $isIssuedOverdue = false;
                                        if ($issuedPaymentDate && $issuedStatus === 'Nie opłacono') {
                                            try {
                                                $isIssuedOverdue = \Carbon\Carbon::parse($issuedPaymentDate)->lt(now()->startOfDay());
                                            } catch (\Throwable $e) {
                                                $isIssuedOverdue = false;
                                            }
                                        }

                                        $issuedStatusClass = 'bg-amber-100 text-amber-800';
                                        if ($issuedStatus === 'Opłacono') {
                                            $issuedStatusClass = 'bg-green-100 text-green-800';
                                        } elseif ($issuedStatus === 'Planowana') {
                                            $issuedStatusClass = 'bg-violet-100 text-violet-800';
                                        }
                                        $isPlannedInvoice = $issuedStatus === 'Planowana';
                                    @endphp
                                    <tr class="bg-white even:bg-gray-50/80{{ $isPlannedInvoice ? ' border-l-4 border-violet-400' : '' }}">
                                        <td class="px-2 py-2 whitespace-nowrap{{ $isPlannedInvoice ? ' italic' : '' }}">{{ $issuedInvoice['date'] ?? '' }}</td>
                                        <td class="px-2 py-2{{ $isPlannedInvoice ? ' italic' : '' }}">
                                            {{ $issuedInvoice['invoice_number'] ?? '' }}
                                            @if($isPlannedInvoice)<span class="ml-1 inline-block px-1 py-0 rounded bg-violet-200 text-violet-800 text-xs font-bold">PLAN</span>@endif
                                        </td>
                                        @php
                                            $issuedDescription = (string) ($issuedInvoice['description'] ?? '');
                                            $issuedDescriptionShort = \Illuminate\Support\Str::limit($issuedDescription, 40, '…');
                                        @endphp
                                        <td class="px-2 py-2" title="{{ $issuedDescription }}">{{ $issuedDescriptionShort }}</td>
                                        <td class="px-2 py-2 text-right whitespace-nowrap">{{ ($issuedInvoice['amount_net'] ?? '') !== '' ? number_format((float) str_replace(',', '.', $issuedInvoice['amount_net']), 2, ',', ' ') : '' }}</td>
                                        <td class="px-2 py-2 whitespace-nowrap">{{ $issuedInvoice['payment_date'] ?? '' }}</td>
                                        <td class="px-2 py-2 whitespace-nowrap">
                                            <form action="{{ route('magazyn.projects.issuedInvoices.status', [$project->id, $issuedInvoice['id']]) }}" method="POST" class="inline-block">
                                                @csrf
                                                <select name="issued_invoice_status" class="px-2 py-1 rounded border border-gray-300 text-xs font-semibold {{ $issuedStatusClass }}" onchange="this.form.submit()">
                                                    <option value="Nie opłacono" {{ $issuedStatus === 'Nie opłacono' ? 'selected' : '' }}>Nie opłacono</option>
                                                    <option value="Opłacono" {{ $issuedStatus === 'Opłacono' ? 'selected' : '' }}>Opłacono</option>
                                                    <option value="Planowana" {{ $issuedStatus === 'Planowana' ? 'selected' : '' }}>Planowana</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-2 py-3 text-center text-gray-500">Brak faktur wystawionych.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="finance-tab-orders" class="finance-tab-content hidden">
                {{-- Import kompaktowy: Excel + PDF w jednej linii --}}
                <div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
                    <h4 class="font-semibold text-gray-800 text-sm mb-2">Import zamówień</h4>
                    @error('orders_file')
                        <div class="mb-2 p-2 rounded border border-red-200 bg-red-50 text-red-800 text-xs">{{ $message }}</div>
                    @enderror
                    @error('pdf_file')
                        <div class="mb-2 p-2 rounded border border-red-200 bg-red-50 text-red-800 text-xs">{{ $message }}</div>
                    @enderror
                    @if(session('finance_orders_feedback') && session('pdf_parsed'))
                        @php $pp = session('pdf_parsed'); @endphp
                        <div class="mb-2 p-2 rounded border border-yellow-200 bg-yellow-50 text-yellow-900 text-xs">
                            <strong>Dane wyodrębnione z PDF:</strong>
                            <ul class="mt-1 space-y-0.5">
                                @if(!empty($pp['date']))<li>Data: {{ $pp['date'] }}</li>@endif
                                @if(!empty($pp['order_number']))<li>Nr zamówienia: {{ $pp['order_number'] }}</li>@endif
                                @if(isset($pp['amount_net']))<li>Kwota netto: {{ number_format($pp['amount_net'], 2, ',', ' ') }} zł</li>@endif
                                @if(!empty($pp['payment_date']))<li>Termin płatności: {{ $pp['payment_date'] }}</li>@endif
                                @if(!empty($pp['delivery_date']))<li>Termin dostawy: {{ $pp['delivery_date'] }}</li>@endif
                                @if(!empty($pp['items']))<li>Pozycje:<pre class="whitespace-pre-wrap mt-1 text-xs text-gray-700">{{ $pp['items'] }}</pre></li>@endif
                                @if(!empty($pp['nip']))<li>NIP dostawcy: {{ $pp['nip'] }}</li>@endif
                                @if(!empty($pp['supplier_name']))<li>Dostawca ({{ ($pp['supplier_source'] ?? 'gus') === 'db' ? 'z bazy danych' : 'z GUS' }}): {{ $pp['supplier_name'] }}</li>@endif
                            </ul>
                        </div>
                    @endif
                    <div class="flex flex-wrap items-end gap-3">
                        {{-- Excel --}}
                        <form action="{{ route('magazyn.projects.orders.importExcel', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-1.5">
                            @csrf
                            <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Excel:</span>
                            <input type="file" name="orders_file" accept=".xlsx,.xls,.csv" required class="px-2 py-1 border border-gray-300 rounded bg-white text-xs">
                            <button type="submit" class="px-2.5 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs font-semibold whitespace-nowrap">📥 Importuj</button>
                        </form>
                        <div class="w-px bg-gray-300 self-stretch hidden sm:block"></div>
                        {{-- PDF --}}
                        <form action="{{ route('magazyn.projects.orders.importPdf', $project->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-1.5">
                            @csrf
                            <span class="text-xs text-gray-500 font-medium whitespace-nowrap">PDF:</span>
                            <input type="file" name="pdf_file" accept=".pdf" required class="px-2 py-1 border border-gray-300 rounded bg-white text-xs">
                            <button type="submit"
                                class="px-2.5 py-1 bg-rose-600 text-white rounded hover:bg-rose-700 text-xs font-semibold whitespace-nowrap"
                                title="Wgraj plik PDF zamówienia — system automatycznie wyodrębni datę, numer, pozycje, kwotę netto, termin płatności i dostawy.">
                                📄 Importuj z PDF
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Dodaj zamówienie</h4>
                    @if(session('success') && session('finance_orders_feedback'))
                        <div class="mb-3 p-3 rounded border border-green-200 bg-green-50 text-green-800 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error') && session('finance_orders_feedback'))
                        <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if($errors->any() && session('finance_orders_feedback'))
                        <div class="mb-3 p-3 rounded border border-red-200 bg-red-50 text-red-800 text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('magazyn.projects.orders.store', $project->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <div class="finance-fixed-row">
                            <div class="finance-field finance-field-date">
                                <label class="block text-xs text-gray-600 mb-1">Data</label>
                                <input type="date" name="order_date" value="{{ old('order_date') }}" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                            </div>
                            <div class="finance-field finance-field-number">
                                <label class="block text-xs text-gray-600 mb-1">Nr zamówienia</label>
                                <input type="text" name="order_number" value="{{ old('order_number') }}" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="ZAM/2026/...">
                            </div>
                            <div class="finance-field finance-field-type">
                                <label class="block text-xs text-gray-600 mb-1">Typ</label>
                                <select name="order_category" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                    <option value="materials" {{ old('order_category', 'materials') === 'materials' ? 'selected' : '' }}>Materiały</option>
                                    <option value="services" {{ old('order_category') === 'services' ? 'selected' : '' }}>Usługi</option>
                                </select>
                            </div>
                            <div class="finance-field finance-field-flex">
                                <label class="block text-xs text-gray-600 mb-1">Opis</label>
                                <input type="text" name="order_description" value="{{ old('order_description') }}" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="Opis zamówienia">
                            </div>
                        </div>

                        <div class="finance-fixed-row">
                            <div class="finance-field finance-field-amount">
                                <label class="block text-xs text-gray-600 mb-1">Kwota netto</label>
                                <input type="text" name="order_amount_net" value="{{ old('order_amount_net') }}" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="0,00">
                            </div>
                            <div class="finance-field finance-field-date">
                                <label class="block text-xs text-gray-600 mb-1">Termin płatności</label>
                                <input type="date" name="order_payment_date" value="{{ old('order_payment_date') }}" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                            </div>
                            <div class="finance-field finance-field-status">
                                <label class="block text-xs text-gray-600 mb-1">Status</label>
                                <select name="order_status" required class="finance-status-select px-2 py-1.5 border border-gray-300 rounded text-sm">
                                    <option value="Nie wysłano" {{ old('order_status', 'Nie wysłano') === 'Nie wysłano' ? 'selected' : '' }}>Nie wysłano</option>
                                    <option value="Wysłano" {{ old('order_status') === 'Wysłano' ? 'selected' : '' }}>Wysłano</option>
                                    <option value="W trakcie" {{ old('order_status') === 'W trakcie' ? 'selected' : '' }}>W trakcie</option>
                                    <option value="Zrealizowany" {{ old('order_status') === 'Zrealizowany' ? 'selected' : '' }}>Zrealizowany</option>
                                </select>
                            </div>
                            {{-- Supplier picker --}}
                            <div class="finance-field finance-field-flex" id="supplier-picker-wrap">
                                <label class="block text-xs text-gray-600 mb-1">Dostawca</label>
                                <div class="flex gap-1 items-center">
                                    <div class="relative flex-1">
                                        <input type="text" id="order-supplier-input" name="order_supplier" value="{{ old('order_supplier') }}"
                                            autocomplete="off"
                                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"
                                            placeholder="Nazwa lub NIP…">
                                        <div id="supplier-dropdown" class="hidden absolute z-50 left-0 top-full mt-0.5 w-72 max-h-48 overflow-y-auto bg-white border border-gray-300 rounded shadow-lg text-xs"></div>
                                    </div>
                                    <button type="button" id="supplier-nip-toggle"
                                        class="px-2 py-1.5 bg-gray-100 border border-gray-300 rounded text-xs hover:bg-gray-200 whitespace-nowrap"
                                        title="Wpisz NIP i pobierz dane z GUS">NIP/GUS</button>
                                </div>
                                {{-- NIP panel (hidden by default) --}}
                                <div id="supplier-nip-panel" class="hidden mt-1.5 p-2 border border-blue-200 bg-blue-50 rounded text-xs space-y-1.5">
                                    <div class="flex gap-1 items-center">
                                        <input type="text" id="supplier-nip-input" maxlength="13"
                                            class="flex-1 px-2 py-1 border border-gray-300 rounded text-xs" placeholder="Wpisz NIP (10 cyfr)">
                                        <button type="button" id="supplier-nip-fetch"
                                            class="px-2.5 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-semibold whitespace-nowrap">
                                            🔍 Pobierz z GUS
                                        </button>
                                    </div>
                                    <div id="supplier-nip-result" class="hidden p-2 bg-white border border-gray-200 rounded space-y-1">
                                        <div id="supplier-nip-result-name" class="font-semibold text-gray-800"></div>
                                        <div id="supplier-nip-result-detail" class="text-gray-600"></div>
                                        <div class="flex gap-1 pt-1 flex-wrap">
                                            <button type="button" id="supplier-nip-use"
                                                class="px-2 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-xs font-semibold">
                                                ✔ Użyj tej nazwy
                                            </button>
                                            <button type="button" id="supplier-nip-save-db"
                                                class="px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs font-semibold hidden">
                                                💾 Dodaj do bazy
                                            </button>
                                            <span id="supplier-nip-in-db-badge" class="hidden px-2 py-1 bg-green-100 text-green-800 rounded text-xs">✓ Jest w bazie</span>
                                        </div>
                                        <div id="supplier-nip-save-status" class="text-xs text-gray-600 hidden"></div>
                                    </div>
                                    <div id="supplier-nip-error" class="hidden text-red-600 text-xs"></div>
                                    <div id="supplier-nip-loading" class="hidden text-blue-600 text-xs">⏳ Pobieranie danych…</div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm font-semibold">
                                💾 Zapisz zamówienie
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4 text-sm text-gray-700">
                    <h4 class="font-semibold text-gray-800 mb-3">Zamówienia</h4>
                    <div class="w-full overflow-x-auto rounded border border-gray-200">
                        <table class="w-full table-auto text-xs">
                            <thead>
                                <tr class="bg-gray-100 text-gray-800">
                                    <th class="px-2 py-2 text-left">Data</th>
                                    <th class="px-2 py-2 text-left">Nr zamówienia</th>
                                    <th class="px-2 py-2 text-left">Typ</th>
                                    <th class="px-2 py-2 text-left">Dostawca</th>
                                    <th class="px-2 py-2 text-left">Opis</th>
                                    <th class="px-2 py-2 text-right">Kwota netto</th>
                                    <th class="px-2 py-2 text-left">Termin płatności</th>
                                    <th class="px-2 py-2 text-left">Status</th>
                                    <th class="px-2 py-2 text-center">Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($orderRows ?? []) as $orderRow)
                                    @php
                                        $orderStatus = (string) ($orderRow['status'] ?? 'Oczekiwanie');
                                        $orderPaymentDate = $orderRow['payment_date'] ?? null;
                                        $isOrderOverdue = false;
                                        if ($orderPaymentDate && $orderStatus !== 'Opłacono') {
                                            try {
                                                $isOrderOverdue = \Carbon\Carbon::parse($orderPaymentDate)->lt(now()->startOfDay());
                                            } catch (\Throwable $e) {
                                                $isOrderOverdue = false;
                                            }
                                        }

                                        $orderStatusClass = 'bg-gray-100 text-gray-700';
                                        if ($orderStatus === 'Zrealizowany') {
                                            $orderStatusClass = 'bg-green-100 text-green-800';
                                        } elseif ($orderStatus === 'W trakcie') {
                                            $orderStatusClass = 'bg-blue-100 text-blue-800';
                                        } elseif ($orderStatus === 'Wysłano') {
                                            $orderStatusClass = 'bg-amber-100 text-amber-800';
                                        } elseif ($orderStatus === 'Nie wysłano') {
                                            $orderStatusClass = 'bg-gray-100 text-gray-700';
                                        // backward compat
                                        } elseif ($orderStatus === 'Opłacono') {
                                            $orderStatusClass = 'bg-green-100 text-green-800';
                                        } elseif ($orderStatus === 'Nie opłacono' || $orderStatus === 'Oczekiwanie') {
                                            $orderStatusClass = 'bg-amber-100 text-amber-800';
                                        } elseif ($orderStatus === 'Zrealizowane') {
                                            $orderStatusClass = 'bg-gray-200 text-gray-500 line-through';
                                        }

                                        $orderDescription = (string) ($orderRow['description'] ?? '');
                                        $orderDescriptionShort = \Illuminate\Support\Str::limit($orderDescription, 40, '…');
                                        $orderRowId = (int) ($orderRow['id'] ?? 0);
                                    @endphp
                                    <tr class="bg-white even:bg-gray-50/80{{ ($orderRow['status'] ?? '') === 'Zrealizowane' ? ' opacity-60' : '' }}">
                                        <td class="px-2 py-2 whitespace-nowrap">{{ $orderRow['date'] ?? '' }}</td>
                                        <td class="px-2 py-2">{{ $orderRow['order_number'] ?? '' }}</td>
                                        <td class="px-2 py-2">{{ ($orderRow['category'] ?? 'materials') === 'services' ? 'Usługi' : 'Materiały' }}</td>
                                        <td class="px-2 py-2 text-gray-700">{{ $orderRow['supplier'] ?? '' }}</td>
                                        <td class="px-2 py-2" title="{{ $orderDescription }}">{{ $orderDescriptionShort }}</td>
                                        <td class="px-2 py-2 text-right whitespace-nowrap">{{ ($orderRow['amount_net'] ?? '') !== '' ? number_format((float) str_replace(',', '.', $orderRow['amount_net']), 2, ',', ' ') : '' }}</td>
                                        <td class="px-2 py-2 whitespace-nowrap">{{ $orderRow['payment_date'] ?? '' }}</td>
                                        <td class="px-2 py-2 whitespace-nowrap">
                                            <form action="{{ route('magazyn.projects.orders.status', [$project->id, $orderRowId]) }}" method="POST" class="inline-block">
                                                @csrf
                                                <select name="order_status" class="px-2 py-1 rounded border border-gray-300 text-xs font-semibold {{ $orderStatusClass }}" onchange="this.form.submit()">
                                                    <option value="Nie wysłano" {{ $orderStatus === 'Nie wysłano' || $orderStatus === 'Oczekiwanie' ? 'selected' : '' }}>Nie wysłano</option>
                                                    <option value="Wysłano" {{ $orderStatus === 'Wysłano' || $orderStatus === 'Nie opłacono' ? 'selected' : '' }}>Wysłano</option>
                                                    <option value="W trakcie" {{ $orderStatus === 'W trakcie' || $orderStatus === 'Opłacono' ? 'selected' : '' }}>W trakcie</option>
                                                    <option value="Zrealizowany" {{ $orderStatus === 'Zrealizowany' || $orderStatus === 'Zrealizowane' ? 'selected' : '' }}>Zrealizowany</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="px-2 py-2 text-center whitespace-nowrap">
                                            <button type="button"
                                                class="order-edit-btn px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-semibold"
                                                data-id="{{ $orderRowId }}"
                                                data-date="{{ $orderRow['date'] ?? '' }}"
                                                data-number="{{ $orderRow['order_number'] ?? '' }}"
                                                data-category="{{ $orderRow['category'] ?? 'materials' }}"
                                                data-supplier="{{ $orderRow['supplier'] ?? '' }}"
                                                data-description="{{ $orderDescription }}"
                                                data-amount="{{ $orderRow['amount_net'] ?? '' }}"
                                                data-payment-date="{{ $orderRow['payment_date'] ?? '' }}"
                                                data-status="{{ $orderStatus }}"
                                                data-url="{{ route('magazyn.projects.orders.update', [$project->id, $orderRowId]) }}"
                                            >✏️ Edytuj</button>
                                            <button type="button"
                                                class="order-delete-btn px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs font-semibold ml-1"
                                                data-id="{{ $orderRowId }}"
                                                data-number="{{ $orderRow['order_number'] ?? '' }}"
                                                data-url="{{ route('magazyn.projects.orders.destroy', [$project->id, $orderRowId]) }}"
                                            >🗑️ Usuń</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-2 py-3 text-center text-gray-500">Brak zamówień.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    {{-- KONIEC SEKCJI 4 --}}

    {{-- SEKCJA 5: ZAMÓWIENIA PROJEKTOWE --}}
    @if(in_array('project_orders', $visibleSections, true))
    <div id="section-project-orders" class="sortable-section bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm" data-order="6">
        <div class="flex items-center gap-3 mb-4">
            <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600" draggable="true" title="Przeciągnij, aby zmienić kolejność">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </div>
            <button type="button" id="toggle-proj-orders-section" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <span id="toggle-proj-orders-arrow">▶</span>
            </button>
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <span class="text-indigo-600">📋</span>
                Zamówienia projektowe
            </h3>
        </div>
        <div id="proj-orders-section-content" class="hidden">

            {{-- Notification --}}
            <div id="proj-ord-notification" class="hidden mb-3 p-3 rounded border text-sm"></div>

            {{-- Form card --}}
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-gray-800 mb-3">Utwórz zamówienie z pozycjami</h4>
                <div class="space-y-3">
                    {{-- Row 1: order number, category, dates --}}
                    <div class="flex flex-wrap gap-2">
                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-xs text-gray-600 mb-1">Nr zamówienia</label>
                            <input type="text" id="proj-ord-number" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="ZAM/{{ date('Y') }}/...">
                        </div>
                        <div class="w-32">
                            <label class="block text-xs text-gray-600 mb-1">Kategoria</label>
                            <select id="proj-ord-category" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                <option value="materials">Materiały</option>
                                <option value="services">Usługi</option>
                            </select>
                        </div>
                        <div class="w-44">
                            <label class="block text-xs text-gray-600 mb-1">Data zamówienia</label>
                            <input type="date" id="proj-ord-date" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="w-44">
                            <label class="block text-xs text-gray-600 mb-1">Termin płatności</label>
                            <input type="date" id="proj-ord-payment-date" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                    </div>
                    {{-- Row 2: delivery, payment method, payment days, offer number --}}
                    <div class="flex flex-wrap gap-2">
                        <div class="flex-1 min-w-[120px]">
                            <label class="block text-xs text-gray-600 mb-1">Termin dostawy</label>
                            <input type="text" id="proj-ord-delivery" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="np. 14 dni">
                        </div>
                        <div class="flex-1 min-w-[120px]">
                            <label class="block text-xs text-gray-600 mb-1">Forma płatności</label>
                            <input type="text" id="proj-ord-payment-method" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="np. przelew">
                        </div>
                        <div class="w-28">
                            <label class="block text-xs text-gray-600 mb-1">Dni płatności</label>
                            <input type="text" id="proj-ord-payment-days" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="30">
                        </div>
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-xs text-gray-600 mb-1">Nr oferty dostawcy</label>
                            <input type="text" id="proj-ord-offer-number" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="OF/2026/...">
                        </div>
                    </div>
                    {{-- Row 3: Supplier picker --}}
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Dostawca</label>
                        <div id="proj-ord-supplier-wrap">
                            <div class="flex gap-1 items-center">
                                <div class="relative flex-1 max-w-sm">
                                    <input type="text" id="proj-ord-supplier-input" autocomplete="off"
                                        class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"
                                        placeholder="Nazwa lub NIP…">
                                    <div id="proj-ord-supplier-dropdown" class="hidden absolute z-50 left-0 top-full mt-0.5 w-72 max-h-48 overflow-y-auto bg-white border border-gray-300 rounded shadow-lg text-xs"></div>
                                </div>
                                <button type="button" id="proj-ord-nip-toggle"
                                    class="px-2 py-1.5 bg-gray-100 border border-gray-300 rounded text-xs hover:bg-gray-200 whitespace-nowrap"
                                    title="Wpisz NIP i pobierz dane z GUS">NIP/GUS</button>
                            </div>
                            <div id="proj-ord-nip-panel" class="hidden mt-1.5 p-2 border border-blue-200 bg-blue-50 rounded text-xs space-y-1.5 max-w-md">
                                <div class="flex gap-1 items-center">
                                    <input type="text" id="proj-ord-nip-input" maxlength="13"
                                        class="flex-1 px-2 py-1 border border-gray-300 rounded text-xs" placeholder="Wpisz NIP (10 cyfr)">
                                    <button type="button" id="proj-ord-nip-fetch"
                                        class="px-2.5 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-semibold whitespace-nowrap">
                                        🔍 Pobierz z GUS
                                    </button>
                                </div>
                                <div id="proj-ord-nip-result" class="hidden p-2 bg-white border border-gray-200 rounded space-y-1">
                                    <div id="proj-ord-nip-name" class="font-semibold text-gray-800"></div>
                                    <div id="proj-ord-nip-detail" class="text-gray-600"></div>
                                    <div class="flex gap-1 pt-1 flex-wrap">
                                        <button type="button" id="proj-ord-nip-use"
                                            class="px-2 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-xs font-semibold">
                                            ✔ Użyj tej nazwy
                                        </button>
                                        <button type="button" id="proj-ord-nip-save-db"
                                            class="px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs font-semibold hidden">
                                            💾 Dodaj do bazy
                                        </button>
                                        <span id="proj-ord-nip-in-db" class="hidden px-2 py-1 bg-green-100 text-green-800 rounded text-xs">✓ Jest w bazie</span>
                                    </div>
                                    <div id="proj-ord-nip-save-st" class="text-xs text-gray-600 hidden"></div>
                                </div>
                                <div id="proj-ord-nip-error" class="hidden text-red-600 text-xs"></div>
                                <div id="proj-ord-nip-loading" class="hidden text-blue-600 text-xs">⏳ Pobieranie danych…</div>
                            </div>
                        </div>
                    </div>
                    {{-- Row 4: Line items --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-gray-700">Pozycje zamówienia</label>
                            <button type="button" id="proj-ord-add-line"
                                class="px-2 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-xs font-semibold">
                                ➕ Dodaj pozycję
                            </button>
                        </div>
                        <div class="overflow-x-auto rounded border border-gray-200">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-700">
                                        <th class="px-2 py-1.5 text-left">Nazwa</th>
                                        <th class="px-2 py-1.5 text-right w-16">Ilość</th>
                                        <th class="px-2 py-1.5 text-right w-24">Cena netto</th>
                                        <th class="px-2 py-1.5 text-left w-16">Jedn.</th>
                                        <th class="px-2 py-1.5 text-right w-24">Wartość</th>
                                        <th class="px-2 py-1.5 w-8"></th>
                                    </tr>
                                </thead>
                                <tbody id="proj-ord-lines-tbody">
                                    <tr id="proj-ord-no-lines">
                                        <td colspan="6" class="px-2 py-3 text-center text-gray-400">Brak pozycji. Kliknij „Dodaj pozycję".</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 border-t border-gray-200">
                                        <td colspan="4" class="px-2 py-1.5 text-right text-xs font-semibold text-gray-700">Suma netto:</td>
                                        <td class="px-2 py-1.5 text-right text-xs font-bold text-gray-900" id="proj-ord-total">0,00 zł</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    {{-- Submit --}}
                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" id="proj-ord-submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm font-semibold">
                            📋 Utwórz zamówienie
                        </button>
                        <span id="proj-ord-submitting" class="hidden text-sm text-gray-500">⏳ Tworzenie…</span>
                    </div>
                </div>
            </div>

            {{-- Existing project orders table --}}
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3">Zamówienia do projektu</h4>
                <div class="overflow-x-auto rounded border border-gray-200">
                    <table class="w-full table-auto text-xs">
                        <thead>
                            <tr class="bg-gray-100 text-gray-800">
                                <th class="px-2 py-2 text-left">Data</th>
                                <th class="px-2 py-2 text-left">Nr zamówienia</th>
                                <th class="px-2 py-2 text-left">Dostawca</th>
                                <th class="px-2 py-2 text-left">Nr oferty</th>
                                <th class="px-2 py-2 text-right">Wartość netto</th>
                                <th class="px-2 py-2 text-center">Pobierz</th>
                            </tr>
                        </thead>
                        <tbody id="proj-ord-table-tbody">
                            @forelse(($projectOrders ?? []) as $pOrder)
                            @php
                                $pOrderTotal = 0;
                                foreach ($pOrder->products ?? [] as $pProd) {
                                    $pOrderTotal += (float) str_replace(',', '.', $pProd['price'] ?? 0) * (int) ($pProd['quantity'] ?? 1);
                                }
                            @endphp
                            <tr class="bg-white even:bg-gray-50/80">
                                <td class="px-2 py-2 whitespace-nowrap">{{ $pOrder->issued_at ? $pOrder->issued_at->format('Y-m-d') : '' }}</td>
                                <td class="px-2 py-2">{{ $pOrder->order_number }}</td>
                                <td class="px-2 py-2 text-gray-700">{{ $pOrder->supplier }}</td>
                                <td class="px-2 py-2 text-gray-500">{{ $pOrder->supplier_offer_number }}</td>
                                <td class="px-2 py-2 text-right whitespace-nowrap">{{ number_format($pOrderTotal, 2, ',', ' ') }} zł</td>
                                <td class="px-2 py-2 text-center whitespace-nowrap">
                                    <a href="{{ route('magazyn.order.generateWord', $pOrder->id) }}"
                                        class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-semibold mr-1"
                                        title="Pobierz Word">📄 Word</a>
                                    <a href="{{ route('magazyn.order.generatePdf', $pOrder->id) }}"
                                        class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs font-semibold"
                                        title="Pobierz PDF">📄 PDF</a>
                                </td>
                            </tr>
                            @empty
                            <tr id="proj-ord-empty-row">
                                <td colspan="6" class="px-2 py-3 text-center text-gray-500">Brak zamówień projektowych.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    @endif
    {{-- KONIEC SEKCJI 5 --}}

    </div>
    {{-- KONIEC KONTENERA PRZESUWANYCH SEKCJI --}}
    
</div>

{{-- SUPPLIER PICKER JS --}}
<script>
(function () {
    var input      = document.getElementById('order-supplier-input');
    var dropdown   = document.getElementById('supplier-dropdown');
    var nipToggle  = document.getElementById('supplier-nip-toggle');
    var nipPanel   = document.getElementById('supplier-nip-panel');
    var nipInput   = document.getElementById('supplier-nip-input');
    var nipFetch   = document.getElementById('supplier-nip-fetch');
    var nipResult  = document.getElementById('supplier-nip-result');
    var nipName    = document.getElementById('supplier-nip-result-name');
    var nipDetail  = document.getElementById('supplier-nip-result-detail');
    var nipUse     = document.getElementById('supplier-nip-use');
    var nipSaveDb  = document.getElementById('supplier-nip-save-db');
    var nipInDb    = document.getElementById('supplier-nip-in-db-badge');
    var nipSaveSt  = document.getElementById('supplier-nip-save-status');
    var nipError   = document.getElementById('supplier-nip-error');
    var nipLoading = document.getElementById('supplier-nip-loading');

    if (!input) return;

    var _gusData = null;

    // --- Autocomplete from DB ---
    var debTimer = null;
    input.addEventListener('input', function () {
        clearTimeout(debTimer);
        var q = this.value.trim();
        if (q.length < 2) { hideDropdown(); return; }
        debTimer = setTimeout(function () { fetchSuppliers(q); }, 220);
    });
    input.addEventListener('focus', function () {
        if (this.value.trim().length >= 2) fetchSuppliers(this.value.trim());
    });

    function fetchSuppliers(q) {
        fetch('{{ route("api.order-suppliers") }}?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (!res.success || !res.data.length) { hideDropdown(); return; }
            showDropdown(res.data);
        }).catch(function () { hideDropdown(); });
    }

    function showDropdown(items) {
        dropdown.innerHTML = '';
        items.forEach(function (s) {
            var div = document.createElement('div');
            div.className = 'px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0';
            div.innerHTML = '<span class="font-semibold">' + escHtml(s.name) + '</span>'
                + (s.nip ? ' <span class="text-gray-400 ml-1">' + escHtml(s.nip) + '</span>' : '')
                + (s.city ? '<div class="text-gray-500">' + escHtml(s.city) + '</div>' : '');
            div.addEventListener('mousedown', function (e) {
                e.preventDefault();
                input.value = s.name;
                hideDropdown();
            });
            dropdown.appendChild(div);
        });
        dropdown.classList.remove('hidden');
    }

    function hideDropdown() { dropdown.classList.add('hidden'); }
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#supplier-picker-wrap')) hideDropdown();
    });

    // --- NIP toggle ---
    nipToggle.addEventListener('click', function () {
        nipPanel.classList.toggle('hidden');
        nipResult.classList.add('hidden');
        nipError.classList.add('hidden');
    });

    // --- GUS fetch ---
    nipFetch.addEventListener('click', function () {
        var nip = nipInput.value.replace(/[^0-9]/g, '');
        if (nip.length !== 10) { showNipError('NIP musi mieć dokładnie 10 cyfr.'); return; }
        nipError.classList.add('hidden');
        nipResult.classList.add('hidden');
        nipLoading.classList.remove('hidden');
        fetch('{{ route("api.order-supplier.nip") }}?nip=' + nip, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); }).then(function (res) {
            nipLoading.classList.add('hidden');
            if (!res.success) { showNipError(res.message || 'Nie znaleziono firmy.'); return; }
            _gusData = res.data;
            nipName.textContent = res.data.name;
            var detail = [];
            if (res.data.nip) detail.push('NIP: ' + res.data.nip);
            if (res.data.address) detail.push(res.data.address);
            if (res.data.postal_code || res.data.city) detail.push((res.data.postal_code + ' ' + res.data.city).trim());
            nipDetail.textContent = detail.join(' | ');
            if (res.in_db) {
                nipSaveDb.classList.add('hidden');
                nipInDb.classList.remove('hidden');
            } else {
                nipSaveDb.classList.remove('hidden');
                nipInDb.classList.add('hidden');
            }
            nipSaveSt.classList.add('hidden');
            nipResult.classList.remove('hidden');
        }).catch(function () {
            nipLoading.classList.add('hidden');
            showNipError('Błąd połączenia z serwerem.');
        });
    });

    nipInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); nipFetch.click(); } });

    nipUse.addEventListener('click', function () {
        if (_gusData) { input.value = _gusData.name; nipPanel.classList.add('hidden'); }
    });

    nipSaveDb.addEventListener('click', function () {
        if (!_gusData) return;
        nipSaveDb.disabled = true;
        nipSaveSt.textContent = 'Zapisywanie…';
        nipSaveSt.classList.remove('hidden');
        var token = document.querySelector('meta[name="csrf-token"]');
        fetch('{{ route("api.order-supplier.save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
            },
            body: JSON.stringify(_gusData)
        }).then(function (r) { return r.json(); }).then(function (res) {
            nipSaveDb.disabled = false;
            if (res.success) {
                nipSaveSt.textContent = '✓ ' + res.message;
                nipSaveDb.classList.add('hidden');
                nipInDb.classList.remove('hidden');
                input.value = _gusData.name;
            } else {
                nipSaveSt.textContent = '✗ ' + (res.message || 'Błąd zapisu.');
            }
        }).catch(function () {
            nipSaveDb.disabled = false;
            nipSaveSt.textContent = '✗ Błąd połączenia.';
        });
    });

    function showNipError(msg) {
        nipError.textContent = msg;
        nipError.classList.remove('hidden');
        nipResult.classList.add('hidden');
    }
    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

{{-- ZAMÓWIENIA PROJEKTOWE JS --}}
<script>
(function () {
    // ---- Supplier picker for project orders section ----
    var poInput      = document.getElementById('proj-ord-supplier-input');
    var poDropdown   = document.getElementById('proj-ord-supplier-dropdown');
    var poNipToggle  = document.getElementById('proj-ord-nip-toggle');
    var poNipPanel   = document.getElementById('proj-ord-nip-panel');
    var poNipInput   = document.getElementById('proj-ord-nip-input');
    var poNipFetch   = document.getElementById('proj-ord-nip-fetch');
    var poNipResult  = document.getElementById('proj-ord-nip-result');
    var poNipName    = document.getElementById('proj-ord-nip-name');
    var poNipDetail  = document.getElementById('proj-ord-nip-detail');
    var poNipUse     = document.getElementById('proj-ord-nip-use');
    var poNipSaveDb  = document.getElementById('proj-ord-nip-save-db');
    var poNipInDb    = document.getElementById('proj-ord-nip-in-db');
    var poNipSaveSt  = document.getElementById('proj-ord-nip-save-st');
    var poNipError   = document.getElementById('proj-ord-nip-error');
    var poNipLoading = document.getElementById('proj-ord-nip-loading');

    var _poGusData = null;

    function escHtmlPo(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    if (poInput) {
        var poDebTimer = null;
        poInput.addEventListener('input', function () {
            clearTimeout(poDebTimer);
            var q = this.value.trim();
            if (q.length < 2) { poHideDropdown(); return; }
            poDebTimer = setTimeout(function () { poFetchSuppliers(q); }, 220);
        });
        poInput.addEventListener('focus', function () {
            if (this.value.trim().length >= 2) poFetchSuppliers(this.value.trim());
        });
    }

    function poFetchSuppliers(q) {
        fetch('{{ route("api.order-suppliers") }}?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (!res.success || !res.data.length) { poHideDropdown(); return; }
            poShowDropdown(res.data);
        }).catch(function () { poHideDropdown(); });
    }

    function poShowDropdown(items) {
        if (!poDropdown) return;
        poDropdown.innerHTML = '';
        items.forEach(function (s) {
            var div = document.createElement('div');
            div.className = 'px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0';
            div.innerHTML = '<span class="font-semibold">' + escHtmlPo(s.name) + '</span>'
                + (s.nip ? ' <span class="text-gray-400 ml-1">' + escHtmlPo(s.nip) + '</span>' : '')
                + (s.city ? '<div class="text-gray-500">' + escHtmlPo(s.city) + '</div>' : '');
            div.addEventListener('mousedown', function (e) {
                e.preventDefault();
                poInput.value = s.name;
                poHideDropdown();
            });
            poDropdown.appendChild(div);
        });
        poDropdown.classList.remove('hidden');
    }

    function poHideDropdown() { if (poDropdown) poDropdown.classList.add('hidden'); }
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#proj-ord-supplier-wrap')) poHideDropdown();
    });

    if (poNipToggle) {
        poNipToggle.addEventListener('click', function () {
            if (!poNipPanel) return;
            poNipPanel.classList.toggle('hidden');
            if (poNipResult) poNipResult.classList.add('hidden');
            if (poNipError) poNipError.classList.add('hidden');
        });
    }

    if (poNipFetch) {
        poNipFetch.addEventListener('click', function () {
            var nip = poNipInput ? poNipInput.value.replace(/[^0-9]/g, '') : '';
            if (nip.length !== 10) { poShowNipError('NIP musi mieć dokładnie 10 cyfr.'); return; }
            if (poNipError) poNipError.classList.add('hidden');
            if (poNipResult) poNipResult.classList.add('hidden');
            if (poNipLoading) poNipLoading.classList.remove('hidden');
            fetch('{{ route("api.order-supplier.nip") }}?nip=' + nip, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (poNipLoading) poNipLoading.classList.add('hidden');
                if (!res.success) { poShowNipError(res.message || 'Nie znaleziono firmy.'); return; }
                _poGusData = res.data;
                if (poNipName) poNipName.textContent = res.data.name;
                var detail = [];
                if (res.data.nip) detail.push('NIP: ' + res.data.nip);
                if (res.data.address) detail.push(res.data.address);
                if (res.data.postal_code || res.data.city) detail.push((res.data.postal_code + ' ' + res.data.city).trim());
                if (poNipDetail) poNipDetail.textContent = detail.join(' | ');
                if (res.in_db) {
                    if (poNipSaveDb) poNipSaveDb.classList.add('hidden');
                    if (poNipInDb) poNipInDb.classList.remove('hidden');
                } else {
                    if (poNipSaveDb) poNipSaveDb.classList.remove('hidden');
                    if (poNipInDb) poNipInDb.classList.add('hidden');
                }
                if (poNipSaveSt) poNipSaveSt.classList.add('hidden');
                if (poNipResult) poNipResult.classList.remove('hidden');
            }).catch(function () {
                if (poNipLoading) poNipLoading.classList.add('hidden');
                poShowNipError('Błąd połączenia z serwerem.');
            });
        });
    }

    if (poNipUse) {
        poNipUse.addEventListener('click', function () {
            if (_poGusData && poInput) { poInput.value = _poGusData.name; if (poNipPanel) poNipPanel.classList.add('hidden'); }
        });
    }

    if (poNipSaveDb) {
        poNipSaveDb.addEventListener('click', function () {
            if (!_poGusData) return;
            poNipSaveDb.disabled = true;
            if (poNipSaveSt) { poNipSaveSt.textContent = 'Zapisywanie…'; poNipSaveSt.classList.remove('hidden'); }
            var token = document.querySelector('meta[name="csrf-token"]');
            fetch('{{ route("api.order-supplier.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                },
                body: JSON.stringify(_poGusData)
            }).then(function (r) { return r.json(); }).then(function (res) {
                poNipSaveDb.disabled = false;
                if (res.success) {
                    if (poNipSaveSt) poNipSaveSt.textContent = '✓ ' + res.message;
                    poNipSaveDb.classList.add('hidden');
                    if (poNipInDb) poNipInDb.classList.remove('hidden');
                    if (poInput && _poGusData) poInput.value = _poGusData.name;
                } else {
                    if (poNipSaveSt) poNipSaveSt.textContent = '✗ ' + (res.message || 'Błąd zapisu.');
                }
            }).catch(function () {
                poNipSaveDb.disabled = false;
                if (poNipSaveSt) poNipSaveSt.textContent = '✗ Błąd połączenia.';
            });
        });
    }

    function poShowNipError(msg) {
        if (poNipError) { poNipError.textContent = msg; poNipError.classList.remove('hidden'); }
        if (poNipResult) poNipResult.classList.add('hidden');
    }

    // ---- Line items ----
    var projLines = [];
    var projLineIdx = 0;

    function formatAmountPo(v) {
        if (!v && v !== 0) return '';
        return parseFloat(v).toFixed(2).replace('.', ',');
    }

    function renderProjLines() {
        var tbody = document.getElementById('proj-ord-lines-tbody');
        var noLines = document.getElementById('proj-ord-no-lines');
        var totalEl = document.getElementById('proj-ord-total');
        if (!tbody) return;

        // Remove existing data rows
        Array.from(tbody.querySelectorAll('tr.proj-line-row')).forEach(function (r) { r.remove(); });

        var total = 0;
        projLines.forEach(function (line, i) {
            var val = (parseFloat(line.price) || 0) * (parseInt(line.quantity) || 0);
            total += val;
            var tr = document.createElement('tr');
            tr.className = 'proj-line-row bg-white even:bg-gray-50/80';
            tr.innerHTML =
                '<td class="px-1 py-1"><input type="text" class="w-full px-1 py-1 border border-gray-200 rounded text-xs" value="' + escHtmlPo(line.name) + '" data-idx="' + i + '" data-field="name"></td>' +
                '<td class="px-1 py-1"><input type="number" class="w-full px-1 py-1 border border-gray-200 rounded text-xs text-right" value="' + escHtmlPo(line.quantity) + '" min="1" data-idx="' + i + '" data-field="quantity"></td>' +
                '<td class="px-1 py-1"><input type="text" class="w-full px-1 py-1 border border-gray-200 rounded text-xs text-right" value="' + escHtmlPo(line.price) + '" data-idx="' + i + '" data-field="price" placeholder="0,00"></td>' +
                '<td class="px-1 py-1"><input type="text" class="w-full px-1 py-1 border border-gray-200 rounded text-xs" value="' + escHtmlPo(line.unit) + '" data-idx="' + i + '" data-field="unit" placeholder="szt."></td>' +
                '<td class="px-1 py-1 text-right whitespace-nowrap font-semibold">' + formatAmountPo(val) + ' zł</td>' +
                '<td class="px-1 py-1 text-center"><button type="button" class="proj-line-del text-red-500 hover:text-red-700 text-sm font-bold" data-idx="' + i + '">✕</button></td>';
            tbody.appendChild(tr);
        });

        if (noLines) noLines.style.display = projLines.length === 0 ? '' : 'none';
        if (totalEl) totalEl.textContent = formatAmountPo(total) + ' zł';

        // Bind input changes
        tbody.querySelectorAll('input[data-idx]').forEach(function (inp) {
            inp.addEventListener('input', function () {
                var idx = parseInt(this.dataset.idx);
                var field = this.dataset.field;
                if (projLines[idx] !== undefined) {
                    projLines[idx][field] = this.value;
                    // Re-render only value column
                    var val2 = (parseFloat(projLines[idx].price) || 0) * (parseInt(projLines[idx].quantity) || 0);
                    var row = this.closest('tr');
                    if (row) {
                        var valCell = row.cells[4];
                        if (valCell) valCell.textContent = formatAmountPo(val2) + ' zł';
                    }
                    // Update total
                    var t = 0;
                    projLines.forEach(function (l) { t += (parseFloat(l.price) || 0) * (parseInt(l.quantity) || 0); });
                    if (totalEl) totalEl.textContent = formatAmountPo(t) + ' zł';
                }
            });
        });

        // Bind delete buttons
        tbody.querySelectorAll('.proj-line-del').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var idx = parseInt(this.dataset.idx);
                projLines.splice(idx, 1);
                renderProjLines();
            });
        });
    }

    var poAddLineBtn = document.getElementById('proj-ord-add-line');
    if (poAddLineBtn) {
        poAddLineBtn.addEventListener('click', function () {
            projLines.push({ name: '', quantity: 1, price: '', unit: 'szt.' });
            renderProjLines();
            // Focus name input of last row
            var tbody2 = document.getElementById('proj-ord-lines-tbody');
            if (tbody2) {
                var rows = tbody2.querySelectorAll('tr.proj-line-row');
                if (rows.length > 0) {
                    var lastInp = rows[rows.length - 1].querySelector('input');
                    if (lastInp) lastInp.focus();
                }
            }
        });
    }

    // ---- Form submission ----
    var poSubmitBtn = document.getElementById('proj-ord-submit');
    var poSubmitting = document.getElementById('proj-ord-submitting');
    var poNotification = document.getElementById('proj-ord-notification');

    function poShowNotification(msg, isError) {
        if (!poNotification) return;
        poNotification.textContent = msg;
        poNotification.className = 'mb-3 p-3 rounded border text-sm ' + (isError
            ? 'border-red-200 bg-red-50 text-red-800'
            : 'border-green-200 bg-green-50 text-green-800');
        poNotification.classList.remove('hidden');
        setTimeout(function () { poNotification.classList.add('hidden'); }, 6000);
    }

    if (poSubmitBtn) {
        poSubmitBtn.addEventListener('click', function () {
            var orderName = document.getElementById('proj-ord-number') ? document.getElementById('proj-ord-number').value.trim() : '';
            if (!orderName) { poShowNotification('Podaj numer zamówienia.', true); return; }
            if (projLines.length === 0) { poShowNotification('Dodaj co najmniej jedną pozycję.', true); return; }
            var hasEmpty = projLines.some(function (l) { return !l.name || !l.name.trim(); });
            if (hasEmpty) { poShowNotification('Wypełnij nazwy wszystkich pozycji.', true); return; }

            var payload = {
                order_name: orderName,
                category: document.getElementById('proj-ord-category') ? document.getElementById('proj-ord-category').value : 'materials',
                order_date: document.getElementById('proj-ord-date') ? document.getElementById('proj-ord-date').value : '',
                payment_date: document.getElementById('proj-ord-payment-date') ? document.getElementById('proj-ord-payment-date').value : null,
                delivery_time: document.getElementById('proj-ord-delivery') ? document.getElementById('proj-ord-delivery').value : '',
                payment_method: document.getElementById('proj-ord-payment-method') ? document.getElementById('proj-ord-payment-method').value : '',
                payment_days: document.getElementById('proj-ord-payment-days') ? document.getElementById('proj-ord-payment-days').value : '',
                supplier_offer_number: document.getElementById('proj-ord-offer-number') ? document.getElementById('proj-ord-offer-number').value : '',
                supplier: poInput ? poInput.value.trim() : '',
                products: projLines.map(function (l) {
                    return { name: l.name, quantity: parseInt(l.quantity) || 1, price: l.price || '0', unit: l.unit || 'szt.' };
                })
            };

            poSubmitBtn.disabled = true;
            if (poSubmitting) poSubmitting.classList.remove('hidden');

            var token = document.querySelector('meta[name="csrf-token"]');
            fetch('{{ route("magazyn.project.order.create", $project->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                },
                body: JSON.stringify(payload)
            }).then(function (r) { return r.json(); }).then(function (res) {
                poSubmitBtn.disabled = false;
                if (poSubmitting) poSubmitting.classList.add('hidden');
                if (!res.success) { poShowNotification(res.message || 'Błąd tworzenia zamówienia.', true); return; }

                poShowNotification('✓ Zamówienie ' + res.order.order_number + ' zostało utworzone.', false);

                // Reset form
                if (document.getElementById('proj-ord-number')) document.getElementById('proj-ord-number').value = '';
                if (poInput) poInput.value = '';
                if (document.getElementById('proj-ord-payment-date')) document.getElementById('proj-ord-payment-date').value = '';
                if (document.getElementById('proj-ord-delivery')) document.getElementById('proj-ord-delivery').value = '';
                if (document.getElementById('proj-ord-payment-method')) document.getElementById('proj-ord-payment-method').value = '';
                if (document.getElementById('proj-ord-payment-days')) document.getElementById('proj-ord-payment-days').value = '';
                if (document.getElementById('proj-ord-offer-number')) document.getElementById('proj-ord-offer-number').value = '';
                projLines = [];
                renderProjLines();

                // Add row to table
                var tbody3 = document.getElementById('proj-ord-table-tbody');
                var emptyRow = document.getElementById('proj-ord-empty-row');
                if (emptyRow) emptyRow.remove();
                if (tbody3) {
                    var o = res.order;
                    var total3 = 0;
                    if (o.products) o.products.forEach(function (p) {
                        total3 += (parseFloat(p.price) || 0) * (parseInt(p.quantity) || 1);
                    });
                    var tr2 = document.createElement('tr');
                    tr2.className = 'bg-white even:bg-gray-50/80';
                    tr2.innerHTML =
                        '<td class="px-2 py-2 whitespace-nowrap">' + escHtmlPo(o.issued_at ? o.issued_at.substring(0,10) : '') + '</td>' +
                        '<td class="px-2 py-2">' + escHtmlPo(o.order_number) + '</td>' +
                        '<td class="px-2 py-2 text-gray-700">' + escHtmlPo(o.supplier || '') + '</td>' +
                        '<td class="px-2 py-2 text-gray-500">' + escHtmlPo(o.supplier_offer_number || '') + '</td>' +
                        '<td class="px-2 py-2 text-right whitespace-nowrap">' + total3.toFixed(2).replace('.',',') + ' zł</td>' +
                        '<td class="px-2 py-2 text-center whitespace-nowrap">' +
                            '<a href="{{ url("/magazyn/zamowienia") }}/' + o.id + '/generate-word" class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs font-semibold mr-1">📄 Word</a>' +
                            '<a href="{{ url("/magazyn/zamowienia") }}/' + o.id + '/generate-pdf" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs font-semibold">📄 PDF</a>' +
                        '</td>';
                    tbody3.insertBefore(tr2, tbody3.firstChild);
                }
            }).catch(function () {
                poSubmitBtn.disabled = false;
                if (poSubmitting) poSubmitting.classList.add('hidden');
                poShowNotification('Błąd połączenia z serwerem.', true);
            });
        });
    }

    // Initial render
    renderProjLines();
})();
</script>

{{-- MODAL EDYCJI ZAMÓWIENIA --}}
<div id="order-edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display:none!important">
    <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4 shadow-xl">
        <h3 class="text-base font-bold mb-4 text-gray-800">Edytuj zamówienie</h3>
        <form id="order-edit-form" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Data</label>
                    <input type="date" name="order_date" id="oe-date" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Nr zamówienia</label>
                    <input type="text" name="order_number" id="oe-number" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Typ</label>
                    <select name="order_category" id="oe-category" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        <option value="materials">Materiały</option>
                        <option value="services">Usługi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Kwota netto</label>
                    <input type="text" name="order_amount_net" id="oe-amount" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="0,00">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Termin płatności</label>
                    <input type="date" name="order_payment_date" id="oe-payment-date" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Status</label>
                    <select name="order_status" id="oe-status" required class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        <option value="Nie wysłano">Nie wysłano</option>
                        <option value="Wysłano">Wysłano</option>
                        <option value="W trakcie">W trakcie</option>
                        <option value="Zrealizowany">Zrealizowany</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-gray-600 mb-1">Dostawca</label>
                    <input type="text" name="order_supplier" id="oe-supplier" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="Nazwa dostawcy">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Opis</label>
                <textarea name="order_description" id="oe-description" rows="3" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="Opis zamówienia / pozycje"></textarea>
            </div>
            <div class="flex gap-2 justify-end pt-2">
                <button type="button" id="order-edit-cancel" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">Anuluj</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-semibold">💾 Zapisz zmiany</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL USUNIĘCIA ZAMÓWIENIA --}}
<div id="order-delete-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display:none!important">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4 shadow-xl">
        <h3 class="text-base font-bold mb-3 text-gray-800">Usuń zamówienie</h3>
        <p class="text-sm text-gray-700 mb-4">Czy na pewno chcesz usunąć zamówienie <strong id="order-delete-number"></strong>?</p>
        <form id="order-delete-form" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex gap-2 justify-end">
                <button type="button" id="order-delete-cancel" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">Anuluj</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-semibold">🗑️ Usuń</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    // ---- Edit modal ----
    const editModal   = document.getElementById('order-edit-modal');
    const editForm    = document.getElementById('order-edit-form');
    const editCancel  = document.getElementById('order-edit-cancel');

    document.querySelectorAll('.order-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            editForm.action = btn.dataset.url;
            document.getElementById('oe-date').value         = btn.dataset.date || '';
            document.getElementById('oe-number').value       = btn.dataset.number || '';
            document.getElementById('oe-category').value     = btn.dataset.category || 'materials';
            document.getElementById('oe-description').value  = btn.dataset.description || '';
            document.getElementById('oe-amount').value       = btn.dataset.amount || '';
            document.getElementById('oe-payment-date').value = btn.dataset.paymentDate || '';
            document.getElementById('oe-status').value       = btn.dataset.status || 'Oczekiwanie';
            document.getElementById('oe-supplier').value     = btn.dataset.supplier || '';
            editModal.style.removeProperty('display');
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
        });
    });

    function closeEditModal() {
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
        editModal.style.setProperty('display', 'none', 'important');
    }
    if (editCancel) editCancel.addEventListener('click', closeEditModal);
    editModal.addEventListener('click', function (e) { if (e.target === editModal) closeEditModal(); });

    // ---- Delete modal ----
    const deleteModal  = document.getElementById('order-delete-modal');
    const deleteForm   = document.getElementById('order-delete-form');
    const deleteNumber = document.getElementById('order-delete-number');
    const deleteCancel = document.getElementById('order-delete-cancel');

    document.querySelectorAll('.order-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            deleteForm.action = btn.dataset.url;
            deleteNumber.textContent = btn.dataset.number || '';
            deleteModal.style.removeProperty('display');
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
        });
    });

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
        deleteModal.style.setProperty('display', 'none', 'important');
    }
    if (deleteCancel) deleteCancel.addEventListener('click', closeDeleteModal);
    deleteModal.addEventListener('click', function (e) { if (e.target === deleteModal) closeDeleteModal(); });
})();
</script>

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
        
        // Obsługa rozwijania sekcji Pobieranie
        const pickupToggleBtn = document.getElementById('toggle-pickup-section');
        const pickupContent = document.getElementById('pickup-section-content');
        const pickupArrow = document.getElementById('toggle-pickup-arrow');
        if (pickupToggleBtn && pickupContent && pickupArrow) {
            // Domyślnie zamknięta
            pickupContent.classList.add('hidden');
            pickupArrow.textContent = '▶';
            pickupToggleBtn.addEventListener('click', function() {
                pickupContent.classList.toggle('hidden');
                pickupArrow.textContent = pickupContent.classList.contains('hidden') ? '▶' : '▼';
            });
        }
        
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
            // Domyślnie zamknięta
            summaryContent.classList.add('hidden');
            summaryArrow.textContent = '▶';
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
        const financeSectionStateKey = 'projectFinanceSectionOpen_{{ $project->id }}';
        const shouldOpenFinanceSection = @json((bool) (
            session('finance_import_feedback')
            || session('finance_issued_feedback')
            || $errors->has('group_name')
            || $errors->has('group_action')
            || $errors->has('group_name')
            || $errors->has('new_group_name')
            || $errors->has('costs_file')
            || $errors->has('costs_group_existing')
        ));
        const financeInitialTab = 'costs';
        if (financeToggleBtn && financeContent && financeArrow) {
            const storedFinanceSectionState = localStorage.getItem(financeSectionStateKey);
            const shouldOpenFromStorage = storedFinanceSectionState === '1';
            const shouldOpenNow = shouldOpenFinanceSection || shouldOpenFromStorage;

            if (shouldOpenNow) {
                financeContent.classList.remove('hidden');
                financeArrow.textContent = '▼';
                localStorage.setItem(financeSectionStateKey, '1');
            } else {
                financeContent.classList.add('hidden');
                financeArrow.textContent = '▶';
                localStorage.setItem(financeSectionStateKey, '0');
            }

            financeToggleBtn.addEventListener('click', function() {
                financeContent.classList.toggle('hidden');
                financeArrow.textContent = financeContent.classList.contains('hidden') ? '▶' : '▼';
                localStorage.setItem(financeSectionStateKey, financeContent.classList.contains('hidden') ? '0' : '1');
            });
        }

        // Obsługa rozwijania sekcji Zamówienia projektowe
        const projOrdersToggleBtn = document.getElementById('toggle-proj-orders-section');
        const projOrdersContent = document.getElementById('proj-orders-section-content');
        const projOrdersArrow = document.getElementById('toggle-proj-orders-arrow');
        if (projOrdersToggleBtn && projOrdersContent && projOrdersArrow) {
            projOrdersContent.classList.add('hidden');
            projOrdersArrow.textContent = '▶';
            projOrdersToggleBtn.addEventListener('click', function() {
                projOrdersContent.classList.toggle('hidden');
                projOrdersArrow.textContent = projOrdersContent.classList.contains('hidden') ? '▶' : '▼';
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

        const financeTabButtons = document.querySelectorAll('.finance-tab-btn');
        const costsImportForm = document.getElementById('costs-import-form');
        const costsFileInput = document.getElementById('costs-file-input');
        const costsGroupExisting = document.getElementById('costs-group-existing');
        const financeTabContents = {
            costs: document.getElementById('finance-tab-costs'),
            issued: document.getElementById('finance-tab-issued'),
            orders: document.getElementById('finance-tab-orders'),
        };

        function activateFinanceTab(tabName) {
            Object.entries(financeTabContents).forEach(([key, content]) => {
                if (!content) return;
                content.classList.toggle('hidden', key !== tabName);
            });

            financeTabButtons.forEach(btn => {
                const active = btn.dataset.financeTabTarget === tabName;
                btn.classList.toggle('bg-white', active);
                btn.classList.toggle('border-b-white', active);
                btn.classList.toggle('font-semibold', active);
                btn.classList.toggle('bg-gray-100', !active);
            });
        }

        financeTabButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                activateFinanceTab(this.dataset.financeTabTarget || 'costs');
            });
        });

        if (costsImportForm) {
            costsImportForm.addEventListener('submit', function(e) {
                const hasGroups = costsGroupExisting && Array.from(costsGroupExisting.options).some(opt => opt.value !== '');
                if (!hasGroups) {
                    e.preventDefault();
                    alert('Najpierw dodaj grupę przyciskiem „Dodaj grupę”.');
                    return;
                }

                if (!costsGroupExisting.value) {
                    e.preventDefault();
                    alert('Wybierz grupę z listy przed importem pliku.');
                }
            });
        }

        if (costsFileInput && costsFileInput.disabled) {
            const blockedMessage = document.createElement('p');
            blockedMessage.className = 'text-xs text-amber-700 mt-1';
            blockedMessage.textContent = 'Najpierw dodaj grupę, aby odblokować wybór pliku.';
            costsFileInput.parentElement?.appendChild(blockedMessage);
        }

        activateFinanceTab(financeInitialTab);

        const importedCostsForm = document.getElementById('imported-costs-bulk-form');
        const headerSelectImportedCosts = document.getElementById('header-select-imported-costs');
        const selectAllImportedCostsBtn = document.getElementById('select-all-imported-costs');
        const deselectAllImportedCostsBtn = document.getElementById('deselect-all-imported-costs');
        const enableEditSelectedBtn = document.getElementById('enable-edit-selected');
        const saveSelectedEditsBtn = document.getElementById('save-selected-edits');
        const importedSortableHeaders = document.querySelectorAll('.imported-sortable-header');
        const importedCostsSearchInput = document.getElementById('imported-costs-search');
        let importedSortState = { key: null, dir: 'asc' };

        function getImportedCostRows() {
            return Array.from(document.querySelectorAll('.imported-cost-row'));
        }

        function getImportedCostRowCheckboxes() {
            return Array.from(document.querySelectorAll('.imported-cost-row-checkbox'));
        }

        function getSelectedImportedRows() {
            return getImportedCostRows().filter(row => row.querySelector('.imported-cost-row-checkbox')?.checked);
        }

        function setRowEditable(row, editable) {
            row.querySelectorAll('.import-display').forEach(el => el.classList.toggle('hidden', editable));
            row.querySelectorAll('.import-input').forEach(el => {
                el.classList.toggle('hidden', !editable);
                el.disabled = !editable;
            });
            row.classList.toggle('bg-blue-50', editable);
        }

        function lockAllRows() {
            getImportedCostRows().forEach(row => setRowEditable(row, false));
            if (saveSelectedEditsBtn) {
                saveSelectedEditsBtn.classList.add('hidden');
            }
        }

        function setAllImportedCostSelections(checked) {
            const checkboxes = getImportedCostRowCheckboxes();
            checkboxes.forEach(cb => { cb.checked = checked; });
            if (headerSelectImportedCosts) {
                headerSelectImportedCosts.checked = checked && checkboxes.length > 0;
            }
        }

        function syncHeaderCheckboxState() {
            const checkboxes = getImportedCostRowCheckboxes();
            const checked = checkboxes.filter(cb => cb.checked).length;
            if (headerSelectImportedCosts) {
                headerSelectImportedCosts.checked = checkboxes.length > 0 && checked === checkboxes.length;
                headerSelectImportedCosts.indeterminate = checked > 0 && checked < checkboxes.length;
            }
        }

        function getRowSortValue(row, key) {
            const rowId = row.dataset.rowId;
            if (!rowId) return '';

            const fieldMap = {
                date: `rows[${rowId}][date]`,
                supplier: `rows[${rowId}][subject_or_supplier]`,
                document: `rows[${rowId}][document]`,
                group: `rows[${rowId}][group]`,
                amount: `rows[${rowId}][amount_net]`,
                description: `rows[${rowId}][description]`,
                status: `rows[${rowId}][status]`,
                payment_date: `rows[${rowId}][payment_date]`,
            };

            const selector = fieldMap[key];
            if (!selector) return '';
            const field = row.querySelector(`[name="${selector}"]`);
            const value = (field?.value || '').toString().trim();

            if (key === 'amount') {
                const normalized = value.replace(/\s+/g, '').replace(',', '.');
                return parseFloat(normalized) || 0;
            }

            if (key === 'date' || key === 'payment_date') {
                return value || '0000-00-00';
            }

            return value.toLowerCase();
        }

        function sortImportedRowsBy(key) {
            const tbody = document.getElementById('imported-costs-tbody');
            if (!tbody) return;

            const rows = getImportedCostRows();
            const dir = importedSortState.key === key && importedSortState.dir === 'asc' ? 'desc' : 'asc';
            importedSortState = { key, dir };

            rows.sort((a, b) => {
                const av = getRowSortValue(a, key);
                const bv = getRowSortValue(b, key);

                if (av < bv) return dir === 'asc' ? -1 : 1;
                if (av > bv) return dir === 'asc' ? 1 : -1;
                return 0;
            });

            rows.forEach(row => tbody.appendChild(row));
            applyImportedCostsSearchFilter();
        }

        function applyImportedCostsSearchFilter() {
            const search = (importedCostsSearchInput?.value || '').toLowerCase().trim();
            getImportedCostRows().forEach(row => {
                if (search === '') {
                    row.classList.remove('hidden');
                    return;
                }

                const rowText = (row.textContent || '').toLowerCase();
                row.classList.toggle('hidden', !rowText.includes(search));
            });
        }

        if (headerSelectImportedCosts) {
            headerSelectImportedCosts.addEventListener('change', function() {
                setAllImportedCostSelections(this.checked);
                syncHeaderCheckboxState();
            });
        }

        if (selectAllImportedCostsBtn) {
            selectAllImportedCostsBtn.addEventListener('click', function() {
                setAllImportedCostSelections(true);
                syncHeaderCheckboxState();
            });
        }

        if (deselectAllImportedCostsBtn) {
            deselectAllImportedCostsBtn.addEventListener('click', function() {
                setAllImportedCostSelections(false);
                lockAllRows();
                syncHeaderCheckboxState();
            });
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('imported-cost-row-checkbox')) {
                syncHeaderCheckboxState();
            }
        });

        if (enableEditSelectedBtn) {
            enableEditSelectedBtn.addEventListener('click', function() {
                const selectedRows = getSelectedImportedRows();
                if (selectedRows.length === 0) {
                    alert('Zaznacz co najmniej jeden wiersz do edycji.');
                    return;
                }

                lockAllRows();
                selectedRows.forEach(row => setRowEditable(row, true));
                if (saveSelectedEditsBtn) {
                    saveSelectedEditsBtn.classList.remove('hidden');
                }
            });
        }

        if (importedCostsForm && saveSelectedEditsBtn) {
            importedCostsForm.addEventListener('submit', function(e) {
                const submitter = e.submitter;
                if (!submitter) return;

                if (submitter.name === 'bulk_action' && submitter.value === 'update') {
                    const selectedRows = getSelectedImportedRows();
                    if (selectedRows.length === 0) {
                        e.preventDefault();
                        alert('Zaznacz co najmniej jeden wiersz do zapisania.');
                        return;
                    }
                }
            });
        }

        importedSortableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const key = this.dataset.sortKey;
                if (key) {
                    sortImportedRowsBy(key);
                }
            });
        });

        if (importedCostsSearchInput) {
            importedCostsSearchInput.addEventListener('input', applyImportedCostsSearchFilter);
        }

        lockAllRows();
        syncHeaderCheckboxState();
        applyImportedCostsSearchFilter();
        
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
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Opis (opcjonalny)</label>
                <textarea id="task-description-input" class="w-full border rounded px-3 py-2 text-sm" rows="3" placeholder="Opis zadania, notatki..."></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Osoba odpowiedzialna</label>
                <div class="relative">
                    <input type="text" id="task-assignee-input" class="w-full border rounded px-3 py-2 text-sm" placeholder="Wpisz imię lub inicjały..." autocomplete="off">
                    <input type="hidden" id="task-assignee-user-id" value="">
                    <ul id="assignee-dropdown" class="absolute z-50 w-full bg-white border border-gray-300 rounded shadow-lg mt-1 max-h-48 overflow-y-auto hidden text-sm"></ul>
                </div>
                <p class="text-xs text-gray-500 mt-1">Wybierz z listy, aby automatycznie utworzyć zadanie w CRM.</p>
            </div>
            @if(auth()->user() && auth()->user()->is_admin)
            <div class="mb-4 hidden" id="completed-at-row">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Data wykonania <span class="text-xs text-gray-400">(admin)</span></label>
                <input type="date" id="task-completed-at-input" class="w-full border rounded px-3 py-2 text-sm">
                <p class="text-xs text-gray-500 mt-1">Zmiana daty zaktualizuje liczbę dni przed/po terminie.</p>
            </div>
            @endif
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

    #frappe-gantt .bar-wrapper.overdue-task .bar {
        fill: #ef4444 !important;
    }

    #frappe-gantt .bar-wrapper.overdue-task .bar-progress {
        fill: #b91c1c !important;
    }

    #frappe-gantt .bar-wrapper.overdue-task .bar-label {
        fill: #7f1d1d !important;
        font-weight: 700;
    }

    /* Gdy etykieta nie mieści się w okienku — wyświetl po prawej, czarna czcionka */
    #frappe-gantt .bar-label.big {
        fill: #111111 !important;
        font-weight: 600;
    }
    /* Zapobiegaj przycinaniu etykiet wystających poza okienko */
    #frappe-gantt svg,
    #frappe-gantt .gantt svg {
        overflow: visible !important;
    }
    #frappe-gantt .bar-group {
        overflow: visible !important;
    }

    .finance-fixed-row {
        display: flex;
        gap: 0.5rem;
        align-items: flex-end;
    }

    .finance-field {
        min-width: 0;
    }

    .finance-field-date,
    .finance-field-amount {
        flex: 0 0 170px;
        max-width: 170px;
    }

    .finance-field-number {
        flex: 0 0 220px;
        max-width: 220px;
    }

    .finance-field-type {
        flex: 0 0 140px;
        max-width: 140px;
    }

    .finance-field-status {
        flex: 0 0 auto;
    }

    .finance-status-select {
        width: auto;
        min-width: 145px;
    }

    .finance-field-flex {
        flex: 1 1 auto;
    }

    @media (max-width: 1024px) {
        .finance-fixed-row {
            flex-wrap: wrap;
        }

        .finance-field-date,
        .finance-field-amount,
        .finance-field-number,
        .finance-field-type,
        .finance-field-status,
        .finance-field-flex {
            flex: 1 1 100%;
            max-width: 100%;
        }

        .finance-status-select {
            width: 100%;
            min-width: 0;
        }
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
    const isProjectReadonly = ['warranty','archived'].includes('{{ $project->status }}');
    const IS_ADMIN = {{ auth()->user() && auth()->user()->is_admin ? 'true' : 'false' }};
    const projectEndDateStr = @json($project->finished_at ? $project->finished_at->format('Y-m-d') : null);
    const projectEndDate = projectEndDateStr ? (function() { const p = projectEndDateStr.split('-'); return new Date(+p[0], +p[1]-1, +p[2]); })() : null;
    let frappeGanttInstance = null;
    let frappeTasks = [];
    let editingTaskId = null;

    function applyProgressSliderFill(slider) {
        if (!slider) return;
        const value = Number(slider.value || 0);
        slider.style.background = `linear-gradient(to right, #2563eb 0%, #2563eb ${value}%, #e5e7eb ${value}%, #e5e7eb 100%)`;
    }
    
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
    
    // -------- Picker osoby odpowiedzialnej (użytkownicy systemu) --------
    let ganttUsers = [];
    fetch('/api/users-for-gantt', { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(users => {
            ganttUsers = Array.isArray(users) ? users : [];
            console.log('✅ Załadowano ' + ganttUsers.length + ' użytkowników do pickera Gantt');
        })
        .catch(err => { console.warn('⚠️ Picker Gantt: nie udało się pobrać użytkowników:', err); });

    const assigneeInput = document.getElementById('task-assignee-input');
    const assigneeUserId = document.getElementById('task-assignee-user-id');
    const assigneeDropdown = document.getElementById('assignee-dropdown');

    function renderAssigneeDropdown(filter) {
        const lower = filter.toLowerCase();
        const filtered = ganttUsers.filter(u =>
            u.name.toLowerCase().includes(lower) ||
            (u.short_name && u.short_name.toLowerCase().includes(lower))
        );
        assigneeDropdown.innerHTML = '';
        if (filtered.length === 0) {
            assigneeDropdown.classList.add('hidden');
            return;
        }
        // Opcja wyczyszczenia wyboru
        const clearLi = document.createElement('li');
        clearLi.textContent = '— Brak przypisania —';
        clearLi.className = 'px-3 py-2 cursor-pointer hover:bg-gray-100 text-gray-400 italic';
        clearLi.addEventListener('mousedown', function(e) {
            e.preventDefault();
            assigneeInput.value = '';
            assigneeUserId.value = '';
            assigneeDropdown.classList.add('hidden');
        });
        assigneeDropdown.appendChild(clearLi);
        filtered.forEach(u => {
            const li = document.createElement('li');
            li.textContent = u.display;
            li.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50';
            li.addEventListener('mousedown', function(e) {
                e.preventDefault();
                assigneeInput.value = u.display;
                assigneeUserId.value = u.id;
                assigneeDropdown.classList.add('hidden');
            });
            assigneeDropdown.appendChild(li);
        });
        assigneeDropdown.classList.remove('hidden');
    }

    if (assigneeInput) {
        assigneeInput.addEventListener('input', function() {
            if (this.value.length === 0) {
                assigneeUserId.value = '';
            }
            renderAssigneeDropdown(this.value);
        });
        assigneeInput.addEventListener('focus', function() {
            renderAssigneeDropdown(this.value);
        });
        assigneeInput.addEventListener('blur', function() {
            setTimeout(() => assigneeDropdown.classList.add('hidden'), 150);
        });
    }
    // -------- Koniec pickera --------

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
                document.getElementById('task-description-input').value = task.description || '';
                document.getElementById('task-assignee-input').value = task.assignee || '';
                document.getElementById('task-assignee-user-id').value = task.assigned_user_id || '';
                // Jeśli zapisany user_id — pokaż display name z listy
                if (task.assigned_user_id) {
                    const found = ganttUsers.find(u => u.id == task.assigned_user_id);
                    if (found) document.getElementById('task-assignee-input').value = found.display;
                }
                // Admin: date-of-completion + completed_at row visibility
                const completedAtRow = document.getElementById('completed-at-row');
                const progressInput = document.getElementById('task-progress-input');
                if (Number(task.progress || 0) >= 100) {
                    if (completedAtRow) {
                        completedAtRow.classList.remove('hidden');
                        const cInput = document.getElementById('task-completed-at-input');
                        if (cInput) cInput.value = task.completed_at ? String(task.completed_at).substring(0, 10) : '';
                    }
                    // Lock progress for non-admin; admin can still change it
                    if (!IS_ADMIN) {
                        progressInput.setAttribute('readonly', 'readonly');
                        progressInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                    } else {
                        progressInput.removeAttribute('readonly');
                        progressInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    }
                } else {
                    if (completedAtRow) completedAtRow.classList.add('hidden');
                    progressInput.removeAttribute('readonly');
                    progressInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
                }
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
            document.getElementById('task-description-input').value = '';
            document.getElementById('task-assignee-input').value = '';
            document.getElementById('task-assignee-user-id').value = '';
            const completedAtRow = document.getElementById('completed-at-row');
            if (completedAtRow) completedAtRow.classList.add('hidden');
            const progressInput = document.getElementById('task-progress-input');
            progressInput.removeAttribute('readonly');
            progressInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
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
                view_mode: 'Day',
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
            const currentViewMode = window.frappeCurrentViewMode || 'Month';
            ganttConfig.view_mode = currentViewMode;
            if (currentViewMode === 'Month') {
                ganttConfig.step = 24 * 30;
                ganttConfig.column_width = 60;
            } else if (currentViewMode === 'Week') {
                ganttConfig.step = 24 * 7;
                ganttConfig.column_width = 40;
            } else if (currentViewMode === 'Day') {
                ganttConfig.step = 24;
                ganttConfig.column_width = 30;
            } else if (currentViewMode === 'Half Day') {
                ganttConfig.step = 12;
                ganttConfig.column_width = 18;
            } else if (currentViewMode === 'Quarter Day') {
                ganttConfig.step = 6;
                ganttConfig.column_width = 12;
            }
            frappeGanttInstance = new Gantt("#frappe-gantt", frappeTasks, ganttConfig);
            renderTaskList();
            applyOverdueTaskStyles();
            drawProjectEndLine();
            drawTodayLine();
            addTaskHoverTooltips();
            fixGanttBarLabels();
            // Przewiń do aktualnej daty
            (function scrollToToday() {
                const container = document.querySelector('#frappe-gantt .gantt-container') || document.querySelector('#frappe-gantt');
                const svg = document.querySelector('#frappe-gantt svg');
                if (!container || !svg || !frappeGanttInstance) return;
                const ganttStart = frappeGanttInstance.gantt_start;
                if (!ganttStart) return;
                const step = frappeGanttInstance.options.step;
                const colWidth = frappeGanttInstance.options.column_width;
                const today = new Date(); today.setHours(0,0,0,0);
                const diffHours = (today.getTime() - new Date(ganttStart).getTime()) / 3600000;
                const x = Math.round((diffHours / step) * colWidth);
                if (x > 0) {
                    const scrollTo = Math.max(0, x - container.clientWidth / 2);
                    container.scrollLeft = scrollTo;
                }
            })();
            console.log('✅ Frappe Gantt zrenderowany!');
        } catch(error) {
            console.error('❌ Błąd Frappe Gantt:', error);
            document.getElementById('frappe-gantt').innerHTML = '<div class="text-red-500 p-4">Błąd: ' + error.message + '</div>';
        }
    }

    function drawTodayLine() {
        if (!frappeGanttInstance) return;
        const svg = document.querySelector('#frappe-gantt svg');
        if (!svg) return;
        const prev = svg.querySelector('.gantt-today-group');
        if (prev) prev.remove();

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const ganttStart = frappeGanttInstance.gantt_start;
        if (!ganttStart) return;
        const step = frappeGanttInstance.options.step;
        const colWidth = frappeGanttInstance.options.column_width;
        const diffHours = (today.getTime() - new Date(ganttStart).getTime()) / 3600000;
        const x = Math.round((diffHours / step) * colWidth);
        if (x < 0) return;

        const svgHeight = parseInt(svg.getAttribute('height') || svg.getBoundingClientRect().height || 400);

        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.classList.add('gantt-today-group');
        g.style.pointerEvents = 'none';

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x); line.setAttribute('y1', 0);
        line.setAttribute('x2', x); line.setAttribute('y2', svgHeight);
        line.setAttribute('stroke', '#16a34a');
        line.setAttribute('stroke-width', '1');
        line.setAttribute('stroke-dasharray', '4,4');
        line.setAttribute('opacity', '0.55');
        g.appendChild(line);

        const d = today;
        const label = d.getDate().toString().padStart(2,'0') + '.' + (d.getMonth()+1).toString().padStart(2,'0');
        const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rect.setAttribute('x', x - 14); rect.setAttribute('y', 2);
        rect.setAttribute('width', 28); rect.setAttribute('height', 14);
        rect.setAttribute('rx', 3); rect.setAttribute('fill', '#16a34a');
        rect.setAttribute('opacity', '0.7');
        g.appendChild(rect);

        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', x); text.setAttribute('y', 13);
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('fill', '#ffffff');
        text.setAttribute('font-size', '8');
        text.setAttribute('font-family', 'sans-serif');
        text.textContent = label;
        g.appendChild(text);

        svg.appendChild(g);
    }

    function drawProjectEndLine() {
        const svg = document.querySelector('#frappe-gantt svg');
        if (!svg) return;
        const prev = svg.querySelector('.gantt-project-end-group');
        if (prev) prev.remove();

        const ganttStart = frappeGanttInstance.gantt_start;
        if (!ganttStart) return;
        const step = frappeGanttInstance.options.step;
        const colWidth = frappeGanttInstance.options.column_width;
        const diffHours = (projectEndDate.getTime() - new Date(ganttStart).getTime()) / 3600000;
        const x = Math.round((diffHours / step) * colWidth);
        if (x < 0) return;

        const svgHeight = parseInt(svg.getAttribute('height') || svg.getBoundingClientRect().height || 400);

        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.classList.add('gantt-project-end-group');
        g.style.pointerEvents = 'none';

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x); line.setAttribute('y1', 0);
        line.setAttribute('x2', x); line.setAttribute('y2', svgHeight);
        line.setAttribute('stroke', '#dc2626');
        line.setAttribute('stroke-width', '1.5');
        line.setAttribute('stroke-dasharray', '6,4');
        line.setAttribute('opacity', '0.75');
        g.appendChild(line);

        const d = projectEndDate;
        const label = d.getDate().toString().padStart(2,'0') + '.' + (d.getMonth()+1).toString().padStart(2,'0') + '.' + d.getFullYear();
        const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rect.setAttribute('x', x - 36); rect.setAttribute('y', 2);
        rect.setAttribute('width', 72); rect.setAttribute('height', 16);
        rect.setAttribute('rx', 3); rect.setAttribute('fill', '#dc2626');
        rect.setAttribute('opacity', '0.85');
        g.appendChild(rect);

        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', x); text.setAttribute('y', 14);
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('fill', '#ffffff');
        text.setAttribute('font-size', '9');
        text.setAttribute('font-family', 'sans-serif');
        text.textContent = label;
        g.appendChild(text);

        svg.appendChild(g);
    }

    function fixGanttBarLabels() {
        document.querySelectorAll('#frappe-gantt .bar-wrapper').forEach(function(wrapper) {
            const bar = wrapper.querySelector('.bar');
            const label = wrapper.querySelector('.bar-label');
            if (!bar || !label) return;
            const barW = parseFloat(bar.getAttribute('width') || 0);
            const barX = parseFloat(bar.getAttribute('x') || 0);
            try {
                const lw = label.getBBox().width;
                if (lw > barW - 8) {
                    label.setAttribute('x', barX + barW + 5);
                    label.setAttribute('text-anchor', 'start');
                    label.style.fill = '#111111';
                    label.classList.add('big');
                }
            } catch(e) {}
        });
    }

    function addTaskHoverTooltips() {
        let tooltip = document.getElementById('gantt-task-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.id = 'gantt-task-tooltip';
            tooltip.style.cssText = 'position:fixed;z-index:10000;background:#1e293b;color:#f1f5f9;border-radius:7px;padding:10px 14px;font-size:12px;line-height:1.7;pointer-events:none;display:none;box-shadow:0 6px 18px rgba(0,0,0,0.35);min-width:180px;';
            document.body.appendChild(tooltip);
        }
        let tooltipTimer = null;

        document.querySelectorAll('#frappe-gantt .bar-wrapper:not([data-tooltip-bound])').forEach(wrapper => {
            wrapper.setAttribute('data-tooltip-bound', '1');

            wrapper.addEventListener('mouseenter', function(e) {
                const taskId = this.getAttribute('data-id');
                const task = frappeTasks.find(t => String(t.id) === String(taskId))
                           || frappeTasks[Array.from(document.querySelectorAll('#frappe-gantt .bar-wrapper')).indexOf(this)];
                if (!task) return;
                tooltipTimer = setTimeout(() => {
                    const start = task._start instanceof Date ? task._start : parseDate(task.start);
                    const end   = task._end   instanceof Date ? task._end   : parseDate(task.end);
                    const progress = Math.round(Number(task.progress || 0));
                    const fmtStart = start.toLocaleDateString('pl-PL');
                    const fmtEnd   = end.toLocaleDateString('pl-PL');
                    tooltip.innerHTML =
                        '<div style="font-weight:700;margin-bottom:5px;font-size:13px;">' + task.name + '</div>' +
                        '<div>▶ Rozpoczęcie: <strong>' + fmtStart + '</strong></div>' +
                        '<div>◼ Zakończenie: <strong>' + fmtEnd + '</strong></div>' +
                        '<div style="margin-top:4px;">✅ Wykonanie: <strong style="color:#4ade80;">' + progress + '%</strong></div>' +
                        (task.description ? '<div style="margin-top:6px;border-top:1px solid #334155;padding-top:5px;color:#cbd5e1;font-size:11px;">' + task.description.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' : '');
                    tooltip.style.display = 'block';
                    const box = this.getBoundingClientRect();
                    const ttW = tooltip.offsetWidth;
                    const ttH = tooltip.offsetHeight;
                    let left = box.left + box.width / 2 - ttW / 2;
                    let top  = box.top - ttH - 8;
                    if (left < 4) left = 4;
                    if (left + ttW > window.innerWidth - 4) left = window.innerWidth - ttW - 4;
                    if (top < 4) top = box.bottom + 8;
                    tooltip.style.left = left + 'px';
                    tooltip.style.top  = top  + 'px';
                }, 1000);
            });
            wrapper.addEventListener('mouseleave', function() {
                clearTimeout(tooltipTimer);
                tooltip.style.display = 'none';
            });
        });
    }

    function isTaskOverdue(task) {
        const taskEnd = task.end instanceof Date ? task.end : parseDate(task.end);
        const progressValue = Number(task.progress || 0);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return progressValue < 100 && taskEnd < today;
    }

    function getTaskDaysInfo(task) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const end = task.end instanceof Date ? task.end : parseDate(task.end);
        const progress = Number(task.progress || 0);
        if (progress >= 100) {
            let completedAt = null;
            if (task.completed_at) {
                const raw = typeof task.completed_at === 'string' ? task.completed_at : String(task.completed_at);
                completedAt = new Date(raw.substring(0, 10) + 'T00:00:00');
            }
            if (!completedAt || isNaN(completedAt.getTime())) {
                completedAt = today;
            }
            const days = Math.round((end - completedAt) / (1000 * 60 * 60 * 24));
            const label = days > 0 ? `+${days}` : `${days}`;
            return { status: 'done', days, label };
        } else {
            const days = Math.round((end - today) / (1000 * 60 * 60 * 24));
            const label = days > 0 ? `+${days}` : `${days}`;
            return { status: days >= 0 ? 'ok' : 'overdue', days, label };
        }
    }

    function applyOverdueTaskStyles() {
        const wrappers = document.querySelectorAll('#frappe-gantt .bar-wrapper');
        wrappers.forEach((wrapper, idx) => {
            const task = frappeTasks[idx];
            if (!task) return;

            if (isTaskOverdue(task)) {
                wrapper.classList.add('overdue-task');
            } else {
                wrapper.classList.remove('overdue-task');
            }
        });
    }

    function renderTaskList() {
        const container = document.getElementById('frappe-task-list');
        if (!frappeTasks.length) { container.innerHTML = ''; return; }
        let html = '<h4 class="text-lg font-bold mb-2">Lista zadań (kolejność jak w Gantt)</h4>';
        html += '<div class="overflow-x-auto"><table class="w-full text-sm border border-gray-200">';
        html += '<thead class="bg-gray-50"><tr>';
        html += '<th class="px-3 py-2 text-left border-b">Zadanie</th>';
        html += '<th class="px-3 py-2 text-left border-b">Osoba</th>';
        html += '<th class="px-3 py-2 text-left border-b">Opis</th>';
        html += '<th class="px-3 py-2 text-left border-b">Termin</th>';
        html += '<th class="px-3 py-2 text-left border-b">Wykonanie</th>';
        html += '<th class="px-3 py-2 text-left border-b">Status</th>';
        html += '<th class="px-3 py-2 text-center border-b">Dni</th>';
        html += '<th class="px-3 py-2 text-right border-b">Akcje</th>';
        html += '</tr></thead><tbody>';

        frappeTasks.forEach((task, idx) => {
            const end = task.end instanceof Date ? task.end : parseDate(task.end);
            const progressValue = Math.max(0, Math.min(100, Number(task.progress || 0)));
            const daysInfo = getTaskDaysInfo(task);
            const overdue = daysInfo.status === 'overdue';
            const rowClass = overdue ? 'bg-red-50' : (daysInfo.status === 'done' ? 'bg-green-50/40' : 'bg-white');

            let statusBadge, daysCell;
            if (daysInfo.status === 'done') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full bg-green-100 text-green-800 text-xs font-semibold">✓ Wykonano</span>';
                const daysColor = daysInfo.days >= 0 ? 'text-green-700' : 'text-red-700';
                const daysTitle = daysInfo.days > 0 ? `${daysInfo.days} dni przed terminem`
                                : (daysInfo.days < 0 ? `${Math.abs(daysInfo.days)} dni po terminie` : 'dokładnie w terminie');
                daysCell = `<span class="${daysColor} font-semibold text-xs" title="${daysTitle}">${daysInfo.label}</span>`;
            } else if (daysInfo.status === 'overdue') {
                statusBadge = '<span class="px-2 py-0.5 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Po terminie</span>';
                daysCell = `<span class="text-red-700 font-semibold text-xs" title="${Math.abs(daysInfo.days)} dni po terminie">${daysInfo.label}</span>`;
            } else {
                statusBadge = '<span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Termin OK</span>';
                daysCell = `<span class="text-blue-700 font-semibold text-xs" title="${daysInfo.days} dni do terminu">${daysInfo.label}</span>`;
            }

            const isDone = daysInfo.status === 'done';
            const sliderDisabled = isProjectReadonly || (isDone && !IS_ADMIN);

            html += `<tr class="${rowClass} border-b border-gray-100">
                <td class="px-3 py-2 font-semibold">${task.name}</td>
                <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap">${task.assignee ? '<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-700">' + task.assignee + '</span>' : '<span class="text-gray-300">—</span>'}</td>
                <td class="px-3 py-2 text-xs text-gray-500 max-w-[200px]">${task.description ? '<span title="' + task.description.replace(/"/g, '&quot;') + '">' + (task.description.length > 60 ? task.description.substring(0, 60) + '…' : task.description) + '</span>' : '<span class="text-gray-300">—</span>'}</td>
                <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap">${formatDateForInput(end)}</td>
                <td class="px-3 py-2">
                    <div class="flex items-center gap-2 min-w-[180px]">
                        <input
                            type="range"
                            min="0"
                            max="100"
                            value="${progressValue}"
                            data-idx="${idx}"
                            class="task-progress-slider w-full h-2 rounded-lg appearance-none ${sliderDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}"
                            ${sliderDisabled ? 'disabled' : ''}
                        >
                        <span class="task-progress-label text-xs font-bold ${overdue ? 'text-red-700' : 'text-gray-800'} whitespace-nowrap">${progressValue}%</span>
                    </div>
                </td>
                <td class="px-3 py-2">${statusBadge}</td>
                <td class="px-3 py-2 text-center">${daysCell}</td>
                <td class="px-3 py-2">
                    <div class="flex gap-1 justify-end flex-wrap">
                        <button class="edit-task-row px-2 py-1 bg-blue-600 text-white rounded text-xs ${isProjectReadonly ? 'opacity-50 cursor-not-allowed' : ''}" data-id="${task.id}" ${isProjectReadonly ? 'disabled' : ''}>✏️</button>
                        ${IS_ADMIN && isDone ? '<button class="unmark-done-btn px-2 py-1 bg-amber-500 text-white rounded text-xs" data-idx="' + idx + '" title="Przywróć jako niewykonane">↩</button>' : ''}
                        <button class="move-task-up px-2 py-1 bg-gray-200 rounded text-xs" data-idx="${idx}">⬆️</button>
                        <button class="move-task-down px-2 py-1 bg-gray-200 rounded text-xs" data-idx="${idx}">⬇️</button>
                    </div>
                </td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;

        container.querySelectorAll('.task-progress-slider').forEach(slider => {
            applyProgressSliderFill(slider);

            slider.addEventListener('input', function() {
                applyProgressSliderFill(this);
                const row = this.closest('td');
                const label = row ? row.querySelector('.task-progress-label') : null;
                if (label) {
                    label.textContent = `${this.value}%`;
                }
            });

            slider.addEventListener('change', function() {
                if (isProjectReadonly) return;

                const idx = parseInt(this.dataset.idx, 10);
                const task = frappeTasks[idx];
                if (!task) return;

                const nextProgress = Math.max(0, Math.min(100, Number(this.value || 0)));
                task.progress = nextProgress;

                updateTaskInDB(task.id, { progress: nextProgress })
                    .then(updatedTask => {
                        if (updatedTask && updatedTask.completed_at !== undefined) {
                            task.completed_at = updatedTask.completed_at;
                        } else if (nextProgress >= 100 && !task.completed_at) {
                            task.completed_at = new Date().toISOString().split('T')[0];
                        } else if (nextProgress < 100) {
                            task.completed_at = null;
                        }
                        renderGantt();
                    })
                    .catch(() => {
                        this.value = task.progress || 0;
                        applyProgressSliderFill(this);
                    });
            });
        });

        container.querySelectorAll('.edit-task-row').forEach(btn => {
            btn.addEventListener('click', function() {
                if (isProjectReadonly) return;
                const taskId = this.dataset.id;
                if (taskId) {
                    showTaskModal(taskId.toString());
                }
            });
        });

        // Admin: "Przywróć jako niewykonane" button
        container.querySelectorAll('.unmark-done-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.idx);
                const task = frappeTasks[idx];
                if (!task) return;
                if (!confirm('Przywrócić zadanie "' + task.name + '" jako niewykonane (postęp 0%)?')) return;
                task.progress = 0;
                task.completed_at = null;
                updateTaskInDB(task.id, { progress: 0 }).then(() => { renderGantt(); });
            });
        });

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
                    dependencies: t.dependencies || '',
                    description: t.description || '',
                    completed_at: t.completed_at || null,
                    assignee: t.assignee || '',
                    assigned_user_id: t.assigned_user_id || null
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
                description: task.description || '',
                assignee: task.assignee || '',
                assigned_user_id: task.assigned_user_id || null,
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
    window.frappeCurrentViewMode = 'Day';
    loadTasksFromDB().then(() => {
        console.log('📊 Renderowanie wykresu Gantta z ' + frappeTasks.length + ' zadaniami...');
        renderGantt();
    }).catch(error => {
        console.error('❌ Krytyczny błąd inicjalizacji Gantta:', error);
    });
    
    document.querySelectorAll('.frappe-view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            window.frappeCurrentViewMode = this.dataset.mode;
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
        const description = document.getElementById('task-description-input').value.trim();
        const assignee = document.getElementById('task-assignee-input').value.trim();
        const assigneeUserId = parseInt(document.getElementById('task-assignee-user-id').value) || null;
        const completedAtInput = document.getElementById('task-completed-at-input');
        const completedAtRaw = completedAtInput ? completedAtInput.value : null;
        
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
                frappeTasks[taskIndex].description = description;
                frappeTasks[taskIndex].assignee = assignee;
                frappeTasks[taskIndex].assigned_user_id = assigneeUserId;
                // Handle completed_at: admin can override; non-admin: auto
                if (IS_ADMIN && completedAtRaw) {
                    frappeTasks[taskIndex].completed_at = completedAtRaw;
                } else if (progress < 100) {
                    frappeTasks[taskIndex].completed_at = null;
                }
                
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
                    dependencies: dependency,
                    description: description,
                    assignee: assignee,
                    assigned_user_id: assigneeUserId,
                    ...(IS_ADMIN && completedAtRaw ? { completed_at: completedAtRaw } : {})
                }).then(updatedTask => {
                    if (updatedTask && updatedTask.completed_at !== undefined) {
                        frappeTasks[taskIndex].completed_at = updatedTask.completed_at;
                    }
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
                dependencies: dependency,
                description: description,
                assignee: assignee,
                assigned_user_id: assigneeUserId
            };
            createTaskInDB(newTask).then(data => {
                frappeTasks.push({
                    id: data.id.toString(),
                    name: data.name,
                    start: parseDate(data.start),
                    end: parseDate(data.end),
                    progress: data.progress,
                    dependencies: data.dependencies || '',
                    description: data.description || '',
                    completed_at: data.completed_at || null,
                    assignee: data.assignee || '',
                    assigned_user_id: data.assigned_user_id || null
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

document.addEventListener('click', function(e) {
    // Obsługa usuwania listy z projektu
    if (e.target.classList.contains('remove-list-btn')) {
        const btn = e.target;
        const loadedListId = btn.dataset.loadedListId;
        const listName = btn.dataset.listName;
        
        if (!confirm(`Czy na pewno chcesz odłączyć listę "${listName}" od tego projektu?\n\nUWAGA: Produkty które zostały dodane do projektu z tej listy POZOSTANĄ w projekcie.`)) {
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
    btn.textContent = 'Sprawdzanie...';
    
    // Funkcja do ładowania listy
    function loadList(linkExisting = false) {
        btn.disabled = true;
        btn.textContent = linkExisting ? 'Podpinanie...' : 'Dodawanie...';
        
        fetch(`/projekty/{{ $project->id }}/load-list`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                list_id: selectedListId,
                link_existing: linkExisting
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.requires_confirmation) {
                // Produkty już są w projekcie - pytaj użytkownika
                const productList = data.existing_products.join('\n• ');
                const confirmed = confirm(data.message + '\n\nProdukty już w projekcie:\n• ' + productList + '\n\nKliknij OK aby podpiąć istniejące produkty pod tę listę (nie dodając nowych).\nKliknij Anuluj aby zrezygnować.');
                
                if (confirmed) {
                    // Wyślij ponownie z parametrem link_existing=true
                    loadList(true);
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Dodaj produkty do projektu';
                }
                return;
            }
            
            if (data.success) {
                let message = data.message;
                if (!data.is_complete && data.missing_count > 0) {
                    message += `\n\n⚠ Uwaga: ${data.missing_count} pozycji zostało dodanych niekompletnie lub pominiętych.`;
                    if (Array.isArray(data.missing_items) && data.missing_items.length > 0) {
                        message += '\n\nBraki:';
                        data.missing_items.forEach(item => {
                            const missingQty = item.quantity ?? 0;
                            const availableQty = item.available ?? 0;
                            message += `\n• ${item.name}: brakuje ${missingQty} (dostępne teraz: ${availableQty})`;
                        });
                    }
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
    }
    
    // Rozpocznij ładowanie
    loadList(false);
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

// Przycisk "Zwróć do magazynu"
if (deleteBtn) {
    deleteBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Nie zaznaczono żadnych produktów');
            return;
        }
        
        const partIds = Array.from(checkedBoxes).map(cb => cb.dataset.partId);
        const partNames = Array.from(checkedBoxes).map(cb => cb.dataset.partName).join(', ');
        
        if (!confirm(`Czy na pewno chcesz zwrócić ${checkedBoxes.length} zaznaczonych produktów do magazynu?\n\nProdukty: ${partNames}\n\nUWAGA: Produkty zostaną zwrócone do magazynu i usunięte z projektu!`)) {
            return;
        }
        
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Zwracanie...';
        
        fetch(`/projekty/{{ $project->id }}/return-products`, {
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
                alert('Błąd: ' + (data.message || 'Nie udało się zwrócić produktów'));
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '↩️ Zwróć do magazynu (<span id="selected-count">' + checkedBoxes.length + '</span>)';
            }
        })
        .catch(error => {
            alert('Błąd połączenia: ' + error.message);
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '↩️ Zwróć do magazynu (<span id="selected-count">' + checkedBoxes.length + '</span>)';
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
