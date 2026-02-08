<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Szczegóły projektu - {{ $project->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
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
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-semibold text-gray-600">Nr projektu:</span>
                <p class="text-lg">{{ $project->project_number }}</p>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-600">Nazwa:</span>
                <p class="text-lg">{{ $project->name }}</p>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-600">Budżet:</span>
                <p class="text-lg">{{ $project->budget ? number_format($project->budget, 2) . ' PLN' : '-' }}</p>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-600">Osoba odpowiedzialna:</span>
                <p class="text-lg">
                    @if(isset($project->responsibleUser) && $project->responsibleUser)
                        {{ $project->responsibleUser->name ?? ($project->responsibleUser->short_name ?? '-') }}
                    @else
                        -
                    @endif
                </p>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-600">Status:</span>
                <p class="text-lg">
                    @if($project->status === 'in_progress') W toku
                    @elseif($project->status === 'warranty') Na gwarancji
                    @elseif($project->status === 'archived') Archiwalny
                    @endif
                </p>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-600">Autoryzacja pobrań:</span>
                @php
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
            </div>
        </div>
        
        {{-- INFORMACJA O UŻYTEJ LIŚCIE --}}
        @if($project->loaded_list_id && method_exists($project, 'loadedList') && $project->loadedList)
            @php
                $loadedList = $project->loadedList->load('items');
                
                // Pobierz aktualne produkty w projekcie (agregowane)
                $projectProducts = \App\Models\ProjectRemoval::where('project_id', $project->id)
                    ->where('status', 'added')
                    ->get()
                    ->groupBy('part_id')
                    ->map(fn($group) => $group->sum('quantity'));
                
                // Pobierz produkty z listy (agregowane)
                $listProducts = $loadedList->items->groupBy('part_id')
                    ->map(fn($group) => $group->sum('quantity'));
                
                // Porównaj
                $isListCurrent = $projectProducts->count() === $listProducts->count() 
                    && $projectProducts->diffAssoc($listProducts)->isEmpty() 
                    && $listProducts->diffAssoc($projectProducts)->isEmpty();
            @endphp
            <div class="mt-4 p-3 rounded border {{ $isListCurrent ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }}">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold">📋 Użyta lista:</span>
                    <span class="text-sm font-bold">{{ $loadedList->name }}</span>
                    @if($isListCurrent)
                        <span class="ml-2 px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full font-semibold">✓ Lista aktualna</span>
                    @else
                        <span class="ml-2 px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs rounded-full font-semibold">⚠ Lista zmodyfikowana</span>
                    @endif
                </div>
                @if(!$isListCurrent)
                    <p class="text-xs text-yellow-700 mt-1">Produkty w projekcie różnią się od oryginalnej listy (dodano lub usunięto produkty).</p>
                @endif
            </div>
        @endif
        
        <div class="mt-4 flex gap-2 justify-end">
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
                                <form method="POST" action="{{ route('magazyn.projects.removalDelete', [$project->id, $removal->id]) }}" onsubmit="return confirm('Czy na pewno chcesz usunąć/wycofać ten produkt z projektu? Operacja nie zmienia stanu magazynu.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 text-xs">Usuń / Zwrot</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        {{-- SEKCJA AUTORYZOWANYCH/ZWYKŁYCH --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">
                @if($project->requires_authorization)
                    ✅ Produkty autoryzowane ({{ $authorized->count() }})
                @else
                    Pobrane produkty
                @endif
            </h3>
            <div class="flex gap-2">
                <button id="save-list-btn" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                    💾 Zapisz jako lista
                </button>
                <button id="load-list-btn" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">
                    📥 Załaduj listę
                </button>
            </div>
        </div>
    </div>
    
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
                        @if($removal->status === 'added')
                            <form action="{{ route('magazyn.returnProduct', ['project' => $project->id, 'removal' => $removal->id]) }}" method="POST" class="inline" onsubmit="return confirm('Czy na pewno chcesz zwrócić ten produkt do katalogu?');">
                                @csrf
                                <button type="submit" class="text-green-600 hover:underline text-xs font-semibold">
                                    Zwróć produkt
                                </button>
                            </form>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="border p-4 text-center text-gray-500">Brak {{ $project->requires_authorization ? 'autoryzowanych' : 'pobranych' }} produktów</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    {{-- TABELA SUMARYCZNA PRODUKTÓW --}}
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">📊 Podsumowanie produktów w projekcie</h3>
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
        
        <table class="w-full border border-collapse text-sm bg-white">
            <thead class="bg-blue-100">
                <tr>
                    <th class="border p-3 text-left">Nazwa produktu</th>
                    <th class="border p-3 text-left">Opis</th>
                    <th class="border p-3 text-center">Łączna ilość w projekcie</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="border p-3">{{ isset($item['part']) && $item['part'] ? $item['part']->name : '-' }}</td>
                        <td class="border p-3 text-gray-600">{{ isset($item['part']) && $item['part'] ? ($item['part']->description ?? '-') : '-' }}</td>
                        <td class="border p-3 text-center font-bold text-blue-600">{{ $item['total_quantity'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="border p-4 text-center text-gray-500">Brak produktów w projekcie</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
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

{{-- MODAL ZAPISZ JAKO LISTA --}}
<div id="save-list-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">💾 Zapisz produkty jako listę</h3>
        <form action="{{ route('magazyn.projects.saveAsList', $project->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Wybierz istniejącą listę lub utwórz nową:</label>
                <select name="list_id" id="existing-list-select" class="w-full border rounded p-2 mb-2">
                    <option value="">-- Nowa lista --</option>
                    @if(class_exists('\App\Models\ProductList'))
                        @foreach(\App\Models\ProductList::orderBy('name')->get() as $list)
                            <option value="{{ $list->id }}">{{ $list->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div id="new-list-fields">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Nazwa nowej listy:</label>
                    <input type="text" name="list_name" class="w-full border rounded p-2" placeholder="np. Instalacja elektryczna">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Opis (opcjonalnie):</label>
                    <textarea name="list_description" class="w-full border rounded p-2" rows="2" placeholder="Krótki opis listy..."></textarea>
                </div>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" id="cancel-save-list-btn" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Anuluj
                </button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Zapisz
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL ZAŁADUJ LISTĘ --}}
<div id="load-list-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">📥 Załaduj listę produktów do projektu</h3>
        <form action="{{ route('magazyn.projects.loadList', $project->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Wybierz listę:</label>
                <select name="list_id" id="load-list-select" class="w-full border rounded p-2" required>
                    <option value="">-- Wybierz listę --</option>
                    @foreach(\App\Models\ProductList::with('items.part')->orderBy('name')->get() as $list)
                        <option value="{{ $list->id }}" data-items='@json($list->items->map(fn($item) => ["name" => $item->part->name ?? "Usunięty produkt", "quantity" => $item->quantity]))'>{{ $list->name }} ({{ $list->items->count() }} produktów)</option>
                    @endforeach
                </select>
            </div>
            
            {{-- PODGLĄD LISTY --}}
            <div id="list-preview" class="hidden mb-4">
                <label class="block text-sm font-medium mb-2">Podgląd produktów na liście:</label>
                <div class="bg-gray-50 border rounded p-3 max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="text-left p-2 border-b">Nazwa produktu</th>
                                <th class="text-center p-2 border-b w-24">Ilość</th>
                            </tr>
                        </thead>
                        <tbody id="list-preview-body">
                        </tbody>
                    </table>
                </div>
            </div>
            
            <p class="text-sm text-gray-600 mb-4">
                @if($project->requires_authorization)
                    ⚠️ Produkty zostaną dodane jako oczekujące na autoryzację.
                @else
                    ℹ️ Produkty zostaną pobrane bezpośrednio z magazynu.
                @endif
            </p>
            <div class="flex gap-2 justify-end">
                <button type="button" id="cancel-load-list-btn" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Anuluj
                </button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Załaduj
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

    // Modal zapisz jako lista
    const saveListBtn = document.getElementById('save-list-btn');
    const saveListModal = document.getElementById('save-list-modal');
    const cancelSaveListBtn = document.getElementById('cancel-save-list-btn');
    const existingListSelect = document.getElementById('existing-list-select');
    const newListFields = document.getElementById('new-list-fields');

    if (saveListBtn) {
        saveListBtn.addEventListener('click', function() {
            saveListModal.classList.remove('hidden');
        });
    }

    if (cancelSaveListBtn) {
        cancelSaveListBtn.addEventListener('click', function() {
            saveListModal.classList.add('hidden');
        });
    }

    if (saveListModal) {
        saveListModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    }

    // Pokaż/ukryj pola nowej listy w zależności od wyboru
    if (existingListSelect) {
        existingListSelect.addEventListener('change', function() {
            if (this.value) {
                newListFields.classList.add('hidden');
            } else {
                newListFields.classList.remove('hidden');
            }
        });
    }

    // Modal załaduj listę
    const loadListBtn = document.getElementById('load-list-btn');
    const loadListModal = document.getElementById('load-list-modal');
    const cancelLoadListBtn = document.getElementById('cancel-load-list-btn');

    if (loadListBtn) {
        loadListBtn.addEventListener('click', function() {
            loadListModal.classList.remove('hidden');
        });
    }

    if (cancelLoadListBtn) {
        cancelLoadListBtn.addEventListener('click', function() {
            loadListModal.classList.add('hidden');
        });
    }

    if (loadListModal) {
        loadListModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    }

    // Podgląd listy po wybraniu
    const loadListSelect = document.getElementById('load-list-select');
    const listPreview = document.getElementById('list-preview');
    const listPreviewBody = document.getElementById('list-preview-body');

    if (loadListSelect) {
        loadListSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const itemsData = selectedOption.getAttribute('data-items');
            
            if (itemsData && this.value) {
                const items = JSON.parse(itemsData);
                listPreviewBody.innerHTML = '';
                
                items.forEach(function(item) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="p-2 border-b">${item.name}</td>
                        <td class="p-2 border-b text-center font-semibold text-blue-600">${item.quantity}</td>
                    `;
                    listPreviewBody.appendChild(row);
                });
                
                listPreview.classList.remove('hidden');
            } else {
                listPreview.classList.add('hidden');
                listPreviewBody.innerHTML = '';
            }
        });
    }
</script>

{{-- Gantt Frappe (na dole strony) --}}
<div class="max-w-6xl mx-auto bg-white p-4 rounded shadow mt-8 mb-8">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-blue-800">📊 Gantt Frappe - Interaktywny harmonogram</h3>
        <div class="flex gap-2">
            <button id="frappe-add-task" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm font-semibold">
                ➕ Dodaj zadanie
            </button>
            <button id="frappe-export-excel" class="bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-700 text-sm font-semibold">
                📊 Eksport Excel
            </button>
            <button id="frappe-save-tasks" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm font-semibold">
                💾 Zapisz zmiany
            </button>
            <button id="frappe-clear-all" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm font-semibold">
                🗑️ Wyczyść wszystko
            </button>
        </div>
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
    
    <div id="frappe-gantt"></div>

    <div id="frappe-task-list" class="mt-8">
        <!-- Lista zadań pojawi się tutaj -->
    </div>
</div>

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
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Gantt === 'undefined') {
        console.error('❌ Frappe Gantt nie został załadowany z CDN!');
        document.getElementById('frappe-gantt').innerHTML = '<div class="text-red-500 p-4">Błąd: Biblioteka Frappe Gantt nie została załadowana.</div>';
        return;
    }
    
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
        const tasksToSave = frappeTasks.map(t => ({
            id: t.id,
            name: t.name,
            start: t.start instanceof Date ? t.start.toISOString().split('T')[0] : t.start,
            end: t.end instanceof Date ? t.end.toISOString().split('T')[0] : t.end,
            progress: t.progress || 0,
            dependencies: t.dependencies || ''
        }));
        localStorage.setItem('frappeTasks', JSON.stringify(tasksToSave));
        console.log('💾 Zapisano zadania:', tasksToSave);
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
                document.getElementById('task-progress-input').value = task.progress || 0;
                deleteBtn.style.display = 'inline-block';
            }
        } else {
            title.textContent = 'Dodaj nowe zadanie';
            form.reset();
            const today = new Date();
            const nextWeek = new Date(today);
            nextWeek.setDate(today.getDate() + 7);
            document.getElementById('task-start-input').value = formatDateForInput(today);
            document.getElementById('task-end-input').value = formatDateForInput(nextWeek);
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
    }
    
    function hideTaskModal() {
        document.getElementById('frappe-task-modal').classList.add('hidden');
        editingTaskId = null;
    }
    
    function renderGantt() {
        if (frappeTasks.length === 0) {
            document.getElementById('frappe-gantt').innerHTML = '<div class="text-gray-500 p-4 text-center">Brak zadań. Kliknij "➕ Dodaj zadanie", aby rozpocząć.</div>';
            document.getElementById('frappe-task-list').innerHTML = '';
            return;
        }
        try {
            document.getElementById('frappe-gantt').innerHTML = '';
            frappeGanttInstance = new Gantt("#frappe-gantt", frappeTasks, {
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
                    const start = task._start.toLocaleDateString('pl-PL');
                    const end = task._end.toLocaleDateString('pl-PL');
                    const duration = Math.ceil((task._end - task._start) / (1000 * 60 * 60 * 24));
                    const depText = task.dependencies ? frappeTasks.find(t => t.id === task.dependencies)?.name || 'Nieznane' : 'Brak';
                    return '<div style="padding: 10px;"><h5 style="margin: 0 0 10px 0; font-weight: bold;">' + task.name + '</h5><p style="margin: 5px 0;"><strong>Start:</strong> ' + start + '</p><p style="margin: 5px 0;"><strong>Koniec:</strong> ' + end + '</p><p style="margin: 5px 0;"><strong>Czas trwania:</strong> ' + duration + ' dni</p><p style="margin: 5px 0;"><strong>Postęp:</strong> ' + task.progress + '%</p><p style="margin: 5px 0;"><strong>Zależność:</strong> ' + depText + '</p><p style="margin: 10px 0 0 0; font-size: 11px; color: #666;">💡 Kliknij dwukrotnie, aby edytować</p></div>';
                },
                on_date_change: function(task, start, end) {
                    const taskIndex = frappeTasks.findIndex(t => t.id === task.id);
                    if (taskIndex !== -1) {
                        frappeTasks[taskIndex].start = start;
                        frappeTasks[taskIndex].end = end;
                        saveTasks();
                        renderTaskList();
                    }
                },
                on_progress_change: function(task, progress) {
                    const taskIndex = frappeTasks.findIndex(t => t.id === task.id);
                    if (taskIndex !== -1) {
                        frappeTasks[taskIndex].progress = progress;
                        saveTasks();
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
            });
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
        // Sortuj po dacie zakończenia
        const sorted = [...frappeTasks].sort((a, b) => {
            const aEnd = a.end instanceof Date ? a.end : parseDate(a.end);
            const bEnd = b.end instanceof Date ? b.end : parseDate(b.end);
            return aEnd - bEnd;
        });
        let html = '<h4 class="text-lg font-bold mb-2">Lista zadań wg dat zakończenia</h4>';
        html += '<ul class="divide-y divide-gray-200">';
        sorted.forEach((task, idx) => {
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
        // Dodaj obsługę przesuwania
        container.querySelectorAll('.move-task-up').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.idx);
                if (idx > 0) {
                    // Zamień miejscami w sorted
                    const taskId = sorted[idx].id;
                    const prevId = sorted[idx-1].id;
                    const origIdx = frappeTasks.findIndex(t => t.id === taskId);
                    const prevOrigIdx = frappeTasks.findIndex(t => t.id === prevId);
                    const temp = frappeTasks[origIdx];
                    frappeTasks[origIdx] = frappeTasks[prevOrigIdx];
                    frappeTasks[prevOrigIdx] = temp;
                    saveTasks();
                    renderGantt();
                }
            });
        });
        container.querySelectorAll('.move-task-down').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.idx);
                if (idx < sorted.length - 1) {
                    const taskId = sorted[idx].id;
                    const nextId = sorted[idx+1].id;
                    const origIdx = frappeTasks.findIndex(t => t.id === taskId);
                    const nextOrigIdx = frappeTasks.findIndex(t => t.id === nextId);
                    const temp = frappeTasks[origIdx];
                    frappeTasks[origIdx] = frappeTasks[nextOrigIdx];
                    frappeTasks[nextOrigIdx] = temp;
                    saveTasks();
                    renderGantt();
                }
            });
        });
    }
    
    try { 
        const savedTasks = JSON.parse(localStorage.getItem('frappeTasks')||'[]'); 
        if (savedTasks && savedTasks.length > 0) {
            frappeTasks = savedTasks.map(t => ({
                id: t.id,
                name: t.name,
                start: parseDate(t.start),
                end: parseDate(t.end),
                progress: t.progress || 0,
                dependencies: t.dependencies || ''
            }));
        } else {
            const today = new Date();
            const nextWeek = new Date(today);
            nextWeek.setDate(today.getDate() + 7);
            const twoWeeks = new Date(today);
            twoWeeks.setDate(today.getDate() + 14);
            const threeWeeks = new Date(today);
            threeWeeks.setDate(today.getDate() + 21);
            frappeTasks = [
                {id: 'task_1', name: 'Planowanie projektu', start: today, end: nextWeek, progress: 100, dependencies: ''},
                {id: 'task_2', name: 'Implementacja funkcji', start: nextWeek, end: twoWeeks, progress: 50, dependencies: 'task_1'},
                {id: 'task_3', name: 'Testowanie', start: twoWeeks, end: threeWeeks, progress: 0, dependencies: 'task_2'}
            ];
            saveTasks();
        }
    } catch(e) {
        console.error('❌ Błąd localStorage:', e);
        frappeTasks = [];
    }
    
    renderGantt();
    
    document.querySelectorAll('.frappe-view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (frappeGanttInstance) frappeGanttInstance.change_view_mode(this.dataset.mode);
        });
    });
    
    document.getElementById('frappe-today').addEventListener('click', function() {
        renderGantt();
    });
    
    document.getElementById('frappe-add-task').addEventListener('click', function() {
        showTaskModal();
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
                const depTask = task.dependencies ? frappeTasks.find(t => t.id === task.dependencies) : null;
                const startDate = task.start instanceof Date ? task.start : parseDate(task.start);
                const endDate = task.end instanceof Date ? task.end : parseDate(task.end);
                const duration = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
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
            frappeTasks = frappeTasks.filter(t => t.id !== editingTaskId);
            // Usuń zależności do tego zadania
            frappeTasks.forEach(t => {
                if (t.dependencies === editingTaskId) t.dependencies = '';
            });
            saveTasks();
            renderGantt();
            hideTaskModal();
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
            const taskIndex = frappeTasks.findIndex(t => t.id === editingTaskId);
            if (taskIndex !== -1) {
                frappeTasks[taskIndex].name = name;
                frappeTasks[taskIndex].start = start;
                frappeTasks[taskIndex].end = end;
                frappeTasks[taskIndex].progress = progress;
                frappeTasks[taskIndex].dependencies = dependency;
            }
        } else {
            frappeTasks.push({
                id: 'task_' + Date.now(),
                name: name,
                start: start,
                end: end,
                progress: progress,
                dependencies: dependency
            });
        }
        
        saveTasks();
        renderGantt();
        hideTaskModal();
    });
    
    document.getElementById('frappe-save-tasks').addEventListener('click', function() {
        saveTasks();
        alert('✅ Wszystkie zmiany zostały zapisane!');
    });
    
    document.getElementById('frappe-clear-all').addEventListener('click', function() {
        if (confirm('Czy na pewno chcesz usunąć wszystkie zadania?')) {
            frappeTasks = [];
            saveTasks();
            renderGantt();
        }
    });
    
    document.addEventListener('dblclick', function(e) {
        const barWrapper = e.target.closest('.bar-wrapper');
        if (barWrapper) {
            const taskId = barWrapper.getAttribute('data-id');
            showTaskModal(taskId);
        }
    });
});
</script>

</body>
</html>
</div>
