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
                <p class="text-lg">{{ $project->responsibleUser->name ?? '-' }}</p>
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
                            <td class="border p-2">{{ $removal->part->name }}</td>
                            <td class="border p-2 text-center font-mono text-xs">{{ $removal->part->qr_code ?? '-' }}</td>
                            <td class="border p-2 text-center font-bold text-red-600">{{ $removal->quantity }}</td>
                            <td class="border p-2 text-center">{{ $removal->created_at->format('d.m.Y H:i') }}</td>
                            <td class="border p-2 text-center">{{ $removal->user->short_name ?? $removal->user->name }}</td>
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
                    <td class="border p-2">{{ $removal->part->name }}</td>
                    <td class="border p-2 text-center">{{ $removal->quantity }}</td>
                    <td class="border p-2 text-center">
                        {{ $removal->created_at->format('d.m.Y H:i') }}
                    </td>
                    <td class="border p-2 text-center">{{ $removal->user->short_name ?? $removal->user->name }}</td>
                    <td class="border p-2 text-center">
                        @if($removal->status === 'added')
                            <span class="text-blue-600 font-semibold">Dodany</span>
                        @else
                            <span class="text-green-600 font-semibold">Zwrócony</span>
                            <br>
                            <span class="text-xs text-gray-500">{{ $removal->returned_at->format('d.m.Y H:i') }}</span>
                            <br>
                            <span class="text-xs text-gray-500">przez {{ $removal->returnedBy->short_name ?? $removal->returnedBy->name }}</span>
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
                return [
                    'part' => $group->first()->part,
                    'total_quantity' => $group->sum('quantity')
                ];
            })->sortBy(function($item) {
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
                        <td class="border p-3">{{ $item['part']->name }}</td>
                        <td class="border p-3 text-gray-600">{{ $item['part']->description ?? '-' }}</td>
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

