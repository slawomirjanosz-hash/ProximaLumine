<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Magazyn – Pobierz</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

{{-- MENU --}}
@include('parts.menu')

<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow mt-6">
    <h2 class="text-xl font-bold mb-4">Pobierz Produkt</h2>

    {{-- KOMUNIKAT BŁĘDU --}}
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-2 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- SEKCJA: POBIERZ PRODUKT (ROZWIJALNA) --}}
    <div class="bg-white rounded shadow mb-6 border">
        <button type="button" class="collapsible-btn w-full flex items-center gap-2 p-6 cursor-pointer hover:bg-gray-50" data-target="remove-form-content">
            <span class="toggle-arrow text-lg">▶</span>
            <h3 class="text-lg font-semibold">Pobierz Produkt <span class="text-sm font-normal text-gray-600">(wpisz ręcznie)</span></h3>
        </button>
        <div id="remove-form-content" class="collapsible-content hidden p-6 border-t">
            {{-- FORMULARZ --}}
            <form method="POST" action="{{ route('parts.remove') }}" class="grid grid-cols-4 gap-2 mb-4">
        @csrf

        {{-- NAZWA --}}
        <input
            id="part-name"
            name="name"
            placeholder="Nazwa Produktu"
            class="border p-2 rounded"
            required
        >

        {{-- OPIS --}}
        <input
            id="part-description"
            name="description"
            placeholder="Opis"
            class="border p-2 rounded"
            readonly
        >

        {{-- ILOŚĆ --}}
        <input
            name="quantity"
            type="number"
            min="1"
            value="1"
            class="border p-2 rounded"
            required
        >

        {{-- POBIERZ DO PROJEKTU --}}
        <select
            name="project_id"
            class="border p-2 rounded text-sm"
        >
            <option value="">- Standardowe pobranie -</option>
            @foreach($projects ?? [] as $proj)
                <option value="{{ $proj->id }}">{{ $proj->project_number }} - {{ $proj->name }}</option>
            @endforeach
        </select>

            {{-- PRZYCISK --}}
            <button
                type="submit"
                class="bg-amber-400 hover:bg-amber-500 text-white rounded px-4"
            >
                ➖ Pobierz
            </button>
        </form>

            {{-- PODGLĄD STANU --}}
            <div class="mb-4 text-sm text-gray-600">
                Aktualny stan: <span id="current-quantity" class="font-bold">0</span>
            </div>
        </div>
    </div>


    {{-- SEKCJA: POBIERZ Z KATALOGU (TRYB SZYBKI – przycisk per wiersz) --}}
    <div class="bg-white rounded shadow mb-6 border">
        <button type="button" class="collapsible-btn w-full flex items-center gap-2 p-6 cursor-pointer hover:bg-gray-50" data-target="quick-remove-catalog-content">
            <span class="toggle-arrow text-lg">▶</span>
            <h3 class="text-lg font-semibold">Pobierz z magazynu – katalog <span class="text-sm font-normal text-gray-600">(przycisk ➖ przy każdym produkcie)</span></h3>
        </button>
        <div id="quick-remove-catalog-content" class="collapsible-content hidden p-6 border-t">
            {{-- LISTA POBRANYCH PRODUKTÓW W TEJ SESJI --}}
            <div id="dispensed-products-container" class="mb-6 hidden">
                <h3 class="text-lg font-bold mb-3 text-red-700">➖ Pobrane produkty w tej sesji:</h3>
                <div id="dispensed-products-list" class="space-y-2 mb-4"></div>
            </div>

            @include('parts.check', ['bulkActions' => false, 'showExport' => false, 'isPartial' => true, 'isRemoveContext' => true])
        </div>
    </div>

    {{-- MODAL DO POBIERANIA Z MAGAZYNU --}}
    <div id="dispense-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Pobierz z magazynu</h3>
            <p class="mb-2 text-gray-700">Produkt: <strong id="dispense-modal-part-name"></strong></p>
            <p class="mb-3 text-sm text-gray-500">Dostępne: <strong id="dispense-modal-part-qty"></strong> szt.</p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ilość do pobrania:</label>
                <input
                    type="number"
                    id="dispense-modal-quantity-input"
                    class="w-full px-3 py-2 border border-gray-300 rounded"
                    min="1"
                    value="1"
                    autofocus
                >
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Projekt (opcjonalnie):</label>
                <select id="dispense-modal-project-select" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                    <option value="">— Bez projektu —</option>
                    @foreach($projects ?? [] as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_number }} – {{ $proj->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button id="dispense-modal-confirm-btn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    ➖ Pobierz
                </button>
                <button id="dispense-modal-cancel-btn" class="flex-1 px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                    ✕ Anuluj
                </button>
            </div>
        </div>
    </div>

    {{-- SEKCJA: POBIERZ Z KATALOGU PRODUKTÓW (ROZWIJALNA) --}}
    <div class="bg-white rounded shadow mb-6 border">
        <button type="button" class="collapsible-btn w-full flex items-center gap-2 p-6 cursor-pointer hover:bg-gray-50" data-target="catalog-content">
            <span class="toggle-arrow text-lg">▶</span>
            <h3 class="text-lg font-semibold">Pobierz z Katalogu Produktów</h3>
        </button>
        <div id="catalog-content" class="collapsible-content hidden p-6 border-t">
            {{-- PODSEKCJA: PRODUKTY DO POBRANIA (COLLAPSIBLE) --}}
            <div class="mb-6 pb-6 border-b">
                <button type="button" id="selected-products-btn" class="collapsible-btn w-full flex items-center gap-2 px-0 py-2 cursor-pointer hover:bg-gray-50" data-target="selected-products-inner">
                    <span class="toggle-arrow text-xs">▶</span>
                    <h4 class="font-semibold text-xs">Produkty do pobrania</h4>
                </button>
                <div id="selected-products-inner" class="collapsible-content hidden mt-4 p-4 bg-gray-50 rounded border border-gray-300">
                    <table id="selected-products-table-inner" class="w-full border border-collapse text-xs mb-4">
                        <thead class="bg-blue-100">
                            <tr>
                                <th class="border p-1 text-center" style="width: 30px;">
                                    <input type="checkbox" id="select-all-remove-products" class="w-4 h-4 cursor-pointer" title="Zaznacz wszystkie">
                                </th>
                                <th class="border p-1 text-left" style="white-space: nowrap;">Produkt</th>
                                <th class="border p-1 text-left" style="width: 60px;">Dostawca</th>
                                <th class="border p-1 text-center" style="width: 85px;">Cena netto</th>
                                <th class="border p-1 text-left" style="width: 100px;">Kategoria</th>
                                <th class="border p-1 text-center" style="width: 45px;">Stan</th>
                                <th class="border p-1 text-center" style="width: 50px;">Il. do pobr.</th>
                                <th class="border p-1 text-left" style="width: 120px;">Projekt</th>
                                <th class="border p-1 text-center" style="width: 60px;">Akcja</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div class="flex items-center gap-2 mt-4">
                        <button type="button" id="remove-all-selected-btn-inner" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">🗑️ Wyczyść listę</button>
                        <select id="project-for-all" class="border p-1 rounded text-xs" style="width: 200px;">
                            <option value="">Projekt dla wszystkich</option>
                            @foreach($projects ?? [] as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->project_number }} - {{ $proj->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="apply-project-to-all" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">Zastosuj</button>
                        <button type="button" id="fetch-all-btn-inner" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs ml-auto">✅ Pobierz wszystkie</button>
                    </div>
                </div>
            </div>

            {{-- POBIERZ Z KATALOGU PRODUKTÓW --}}
            <table class="w-full border border-collapse text-sm">
                <table class="w-full border border-collapse text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-center text-xs" style="width: 40px;">
                            <input type="checkbox" id="select-all-catalog-remove" class="w-4 h-4 cursor-pointer" title="Zaznacz wszystkie">
                        </th>
                        <th class="border p-2 text-left text-xs whitespace-nowrap min-w-[16rem] max-w-[24rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('name')">Produkty <span class="align-middle ml-1 text-gray-400">↕</span></th>
                        <th class="border p-2 text-left text-xs whitespace-nowrap min-w-[16rem] max-w-[28rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('description')">Opis <span class="align-middle ml-1 text-gray-400">↕</span></th>
                        <th class="border p-2 text-xs whitespace-nowrap min-w-[3.5rem] max-w-[6rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('supplier')">Dost. <span class="align-middle ml-1 text-gray-400">↕</span></th>
                        <th class="border p-2 text-left text-xs whitespace-nowrap min-w-[6.5rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('category')">Kategoria <span class="align-middle ml-1 text-gray-400">↕</span></th>
                        <th class="border p-2 text-center text-xs whitespace-nowrap min-w-[2.5rem] max-w-[4rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('quantity')">Stan <span class="align-middle ml-1 text-gray-400">↕</span></th>
                        <th class="border p-1 text-center text-xs whitespace-nowrap min-w-[4.5rem]" style="width: 6ch;">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parts ?? [] as $p)
                        @php
                            $supplierShort = '';
                            if ($p->supplier) {
                                $sup = $suppliers->firstWhere('name', $p->supplier);
                                $supplierShort = $sup ? ($sup->short_name ?? $sup->name) : $p->supplier;
                            }
                        @endphp
                        <tr>
                            <td class="border p-2 text-center">
                                <input type="checkbox" class="catalog-checkbox w-4 h-4 cursor-pointer" 
                                       data-part-name="{{ $p->name }}" 
                                       data-part-desc="{{ $p->description ?? '' }}" 
                                       data-part-supplier="{{ $p->supplier ?? '' }}" 
                                       data-part-supplier-short="{{ $supplierShort }}"
                                       data-part-qty="{{ $p->quantity }}"
                                       data-part-price="{{ $p->net_price ?? '' }}"
                                       data-part-currency="{{ $p->currency ?? 'PLN' }}"
                                       data-part-cat-name="{{ $p->category->name ?? '' }}">
                            </td>
                            <td class="border p-2">{{ $p->name }}</td>
                            <td class="border p-2 text-xs text-gray-700">{{ $p->description ?? '-' }}</td>
                            <td class="border p-2 text-center text-xs text-gray-700">{{ $supplierShort ?: '-' }}</td>
                            <td class="border p-2">{{ $p->category->name ?? '-' }}</td>
                            <td class="border p-2 text-center font-bold text-xs {{ $p->quantity == 0 ? 'text-red-600 bg-red-50' : '' }}">{{ $p->quantity }}</td>
                            <td class="border p-2 text-center text-xs text-gray-600">{{ $p->lastModifiedBy ? $p->lastModifiedBy->short_name : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="border p-2 text-center text-gray-400 italic" colspan="7">Brak produktów w katalogu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SEKCJA: HISTORIA POBRAŃ (ROZWIJALNA) --}}
    @if(!empty($sessionRemoves) && count($sessionRemoves))
        <div class="bg-white rounded shadow mb-6 border">
            <button type="button" class="collapsible-btn w-full flex items-center gap-2 p-6 cursor-pointer hover:bg-gray-50" data-target="history-content">
                <span class="toggle-arrow text-lg">▶</span>
                <h3 class="text-lg font-semibold">Historia pobrań</h3>
            </button>
            <div id="history-content" class="collapsible-content hidden p-6 border-t">
                <div class="flex items-center justify-between mb-2">
                    <button type="button" id="delete-selected-history" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">🗑️ Usuń zaznaczone</button>
                </div>

                <table class="w-full border border-collapse text-xs">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-center">
                        <input type="checkbox" id="select-all-history" class="w-4 h-4 cursor-pointer">
                    </th>
                    <th class="border p-2 text-left">Produkt</th>
                    <th class="border p-2 text-left">Opis</th>
                    <th class="border p-2 text-left" style="width: 80px;">Dostawca</th>
                    <th class="border p-2 text-center">Pobrano</th>
                    <th class="border p-2 text-center">Stan po</th>
                    <th class="border p-2 text-left">Data</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sessionRemoves as $index => $r)
                    <tr>
                        <td class="border p-2 text-center">
                            <input type="checkbox" class="history-checkbox w-4 h-4 cursor-pointer" data-index="{{ $index }}">
                        </td>
                        <td class="border p-2">
                            {{ $r['name'] ?? '-' }}
                        </td>

                        <td class="border p-2">
                            {{ $r['description'] ?? '-' }}
                        </td>

                        <td class="border p-2 text-xs text-gray-700">
                            {{ $r['supplier'] ?? '-' }}
                        </td>

                        <td class="border p-2 text-center text-red-600 font-bold">
                            -{{ $r['changed'] ?? 0 }}
                        </td>

                        <td class="border p-2 text-center font-bold">
                            {{ $r['after'] ?? '-' }}
                        </td>

                        <td class="border p-2">
                            {{ $r['date'] ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            </table>
            </div>
        </div>
    @endif
</div>

{{-- JAVASCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.getElementById('part-name');
    const descInput = document.getElementById('part-description');
    const qtyInfo   = document.getElementById('current-quantity');

    // Obsługa collapsible sekcji
    document.querySelectorAll('.collapsible-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            const content = document.getElementById(target);
            const arrow = btn.querySelector('.toggle-arrow');
            
            if (!content || !arrow) return;
            
            const isVisible = content.classList.contains('hidden') === false;
            
            if (isVisible) {
                content.classList.add('hidden');
                arrow.textContent = '▶';
            } else {
                content.classList.remove('hidden');
                arrow.textContent = '▼';
            }
        });
    });

    // ❌ ENTER NIE WYSYŁA FORMULARZA
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            }
        });
    });

    // Historia pobrań - obsługa checkboxów i usuwania
    const selectAllHistoryCheckbox = document.getElementById('select-all-history');
    const deleteSelectedHistoryBtn = document.getElementById('delete-selected-history');
    
    if (selectAllHistoryCheckbox) {
        selectAllHistoryCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.history-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }
    
    if (deleteSelectedHistoryBtn) {
        deleteSelectedHistoryBtn.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.history-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Nie zaznaczono żadnych pozycji');
                return;
            }
            
            if (!confirm(`Czy na pewno usunąć ${checkboxes.length} zaznaczonych pozycji z historii?`)) {
                return;
            }
            
            const indices = Array.from(checkboxes).map(cb => cb.dataset.index);
            
            fetch('{{ route('parts.deleteSelectedHistory') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ indices: indices })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Usuń zaznaczone wiersze z tabeli bez przeładowania strony
                    checkboxes.forEach(cb => {
                        const row = cb.closest('tr');
                        if (row) row.remove();
                    });
                    // Odznacz checkbox "Zaznacz wszystkie"
                    if (selectAllHistoryCheckbox) {
                        selectAllHistoryCheckbox.checked = false;
                    }
                } else {
                    alert('Błąd podczas usuwania pozycji');
                }
            })
            .catch(err => {
                console.error('Błąd:', err);
                alert('Błąd podczas usuwania pozycji');
            });
        });
    }

    // ❌ OTWÓRZ KATALOG JEŚLI BYŁ ZAPAMIĘTANY
    if (localStorage.getItem('katalogOtwarty') === 'true') {
        const catalogBtn = document.querySelector('[data-target="catalog-content"]');
        const catalogContent = document.getElementById('catalog-content');
        catalogContent.style.display = 'block';
        catalogBtn.querySelector('.toggle-arrow').textContent = '▼';
        localStorage.removeItem('katalogOtwarty');
    }

    // �🔎 PODGLĄD CZĘŚCI
    nameInput.addEventListener('blur', () => {
        if (nameInput.value.length < 2) return;

        fetch('{{ route('parts.preview') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: nameInput.value })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                qtyInfo.innerText = data.quantity ?? 0;
                descInput.value  = data.description ?? '';
            } else {
                qtyInfo.innerText = '0';
                descInput.value  = '';
            }
        });
    });

    // Obsługa zaznaczania produktów w katalogu
    const catalogCheckboxes = document.querySelectorAll('.catalog-checkbox');
    const selectedProductsBtn = document.querySelector('[data-target="selected-products-inner"]');
    const selectedProductsContent = document.getElementById('selected-products-inner');
    const selectedProductsTable = document.getElementById('selected-products-table-inner').querySelector('tbody');
    const removeAllBtn = document.getElementById('remove-all-selected-btn-inner');
    const fetchAllBtn = document.getElementById('fetch-all-btn-inner');
    const selectAllRemoveCheckbox = document.getElementById('select-all-remove-products');
    const selectAllCatalogRemoveCheckbox = document.getElementById('select-all-catalog-remove');
    const applyProjectToAllBtn = document.getElementById('apply-project-to-all');
    const projectForAllSelect = document.getElementById('project-for-all');
    let selectedProducts = {};

    // Event listener dla przycisku "Zastosuj" - projekt dla wszystkich produktów
    if (applyProjectToAllBtn && projectForAllSelect) {
        applyProjectToAllBtn.addEventListener('click', function() {
            const projectId = projectForAllSelect.value;
            if (!projectId) {
                alert('Wybierz projekt');
                return;
            }

            // Ustaw projekt dla wszystkich produktów w tabeli
            const projectSelects = selectedProductsTable.querySelectorAll('.product-project');
            projectSelects.forEach(select => {
                select.value = projectId;
                
                // Zaktualizuj również selectedProducts
                const row = select.closest('tr');
                const productNameCell = row.querySelector('td:nth-child(2)');
                const productName = productNameCell ? productNameCell.textContent.trim() : null;
                if (productName && selectedProducts[productName]) {
                    selectedProducts[productName].projectId = projectId;
                }
            });
        });
    }

    // Globalny checkbox "Zaznacz wszystkie" w katalogu produktów
    if (selectAllCatalogRemoveCheckbox) {
        selectAllCatalogRemoveCheckbox.addEventListener('change', function() {
            catalogCheckboxes.forEach(cb => {
                cb.checked = this.checked;
                const event = new Event('change', { bubbles: true });
                cb.dispatchEvent(event);
            });
        });
    }

    function updateSelectedProductsDisplay() {
        selectedProductsTable.innerHTML = '';
        
        Object.entries(selectedProducts).forEach(([name, data]) => {
            const row = document.createElement('tr');
            const stockClass = data.stockQuantity === 0 ? 'text-red-600 bg-red-50 font-bold' : 'text-blue-600 font-bold';
            row.innerHTML = `
                <td class="border p-1 text-center">
                    <input type="checkbox" checked class="w-4 h-4 cursor-pointer selected-product-checkbox" data-product-name="${name}">
                </td>
                <td class="border p-1">${name}</td>
                <td class="border p-1 text-xs">${data.supplierShort || '-'}</td>
                <td class="border p-1 text-center text-xs">${data.price ? data.price + ' ' + data.currency : '-'}</td>
                <td class="border p-1 text-xs">${data.categoryName || '-'}</td>
                <td class="border p-1 text-center ${stockClass}">${data.stockQuantity}</td>
                <td class="border p-1 text-center">
                    <input type="number" min="1" max="${data.stockQuantity}" value="${data.quantity}" size="3" class="px-1 py-0.5 border rounded text-center text-xs product-qty" data-product-name="${name}">
                </td>
                <td class="border p-1">
                    <select class="w-full px-1 py-0.5 border rounded text-xs product-project" data-product-name="${name}">
                        <option value="">Brak projektu</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_number }} - {{ $project->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="border p-1 text-center">
                    <div class="flex justify-center gap-1">
                        <button type="button" class="bg-green-500 hover:bg-green-600 text-white px-1 py-0 rounded text-xs whitespace-nowrap fetch-product-btn" data-product-name="${name}">➖</button>
                        <button type="button" class="bg-red-500 hover:bg-red-600 text-white px-1 py-0 rounded text-xs remove-product-btn" data-product-name="${name}">🗑️</button>
                    </div>
                </td>
            `;
            selectedProductsTable.appendChild(row);
        });

        // Obsługa checkboxów w tabeli wybranych produktów
        document.querySelectorAll('.selected-product-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const productName = e.target.dataset.productName;
                if (!e.target.checked) {
                    // Usuń produkt z listy
                    delete selectedProducts[productName];
                    
                    // Odznacz checkbox w katalogu
                    catalogCheckboxes.forEach(cb => {
                        if (cb.dataset.partName === productName) {
                            cb.checked = false;
                        }
                    });
                    
                    updateSelectedProductsDisplay();
                }
            });
        });

        // Obsługa przycisków usuwania
        document.querySelectorAll('.remove-product-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productName = e.target.dataset.productName;
                
                // Usuń produkt z listy
                delete selectedProducts[productName];
                
                // Odznacz checkbox w katalogu
                catalogCheckboxes.forEach(cb => {
                    if (cb.dataset.partName === productName) {
                        cb.checked = false;
                    }
                });
                
                updateSelectedProductsDisplay();
            });
        });

        // Obsługa indywidualnego pobierania produktu
        document.querySelectorAll('.fetch-product-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productName = e.target.dataset.productName;
                const qty = parseInt(selectedProducts[productName].quantity);
                
                // Pobierz wybrany projekt dla tego produktu
                const projectSelect = document.querySelector(`.product-project[data-product-name="${productName}"]`);
                const projectId = projectSelect ? projectSelect.value : '';
                
                const formData = new FormData();
                formData.append('name', productName);
                formData.append('quantity', qty);
                if (projectId) {
                    formData.append('project_id', projectId);
                }

                console.log('Wysyłam żądanie pobrania:', {name: productName, qty, project_id: projectId});

                fetch('{{ route('parts.remove') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Status odpowiedzi:', response.status);
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Błąd podczas pobierania');
                        });
                    }
                    return response.json();
                })
                .then((data) => {
                    console.log('Odpowiedź serwera:', data);
                    
                    // Zaktualizuj stan magazynu w obiekcie selectedProducts
                    if (selectedProducts[productName] && data.quantity !== undefined) {
                        selectedProducts[productName].stockQuantity = data.quantity;
                    }
                    
                    // Zaktualizuj stan w tabeli katalogu
                    catalogCheckboxes.forEach(cb => {
                        if (cb.dataset.partName === productName) {
                            cb.dataset.partQty = data.quantity;
                            const row = cb.closest('tr');
                            const stateCell = row.querySelector('td:last-child');
                            if (stateCell) {
                                stateCell.textContent = data.quantity;
                                stateCell.className = 'border p-2 text-center font-bold' + (data.quantity == 0 ? ' text-red-600 bg-red-50' : '');
                            }
                        }
                    });
                    
                    // Odśwież wyświetlanie tabeli z nowym stanem
                    updateSelectedProductsDisplay();
                    
                    alert('✅ Pobrano ' + qty + ' szt. produktu: ' + productName);
                })
                .catch(err => {
                    alert('❌ Błąd podczas pobierania produktu');
                });
            });
        });

        // Podświetl przycisk na zielono
        const selectedProductsBtnElement = document.getElementById('selected-products-btn');
        if (Object.keys(selectedProducts).length > 0) {
            // Rozwiń sekcję i zaświeć napis na zielono
            selectedProductsContent.classList.remove('hidden');
            selectedProductsBtnElement.classList.add('bg-green-100');
        } else {
            // Zwiń sekcję i usuń zielone podświetlenie
            selectedProductsContent.classList.add('hidden');
            selectedProductsBtnElement.classList.remove('bg-green-100');
        }

        // Obsługa zmian ilości
        document.querySelectorAll('.product-qty').forEach(input => {
            input.addEventListener('change', (e) => {
                const productName = e.target.dataset.productName;
                selectedProducts[productName].quantity = parseInt(e.target.value) || 1;
            });
        });

        // Obsługa usuwania produktu
        document.querySelectorAll('.remove-product-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productName = e.target.dataset.productName;
                delete selectedProducts[productName];
                
                // Odznacz checkbox w katalogu
                catalogCheckboxes.forEach(cb => {
                    if (cb.dataset.partName === productName) {
                        cb.checked = false;
                    }
                });
                
                updateSelectedProductsDisplay();
            });
        });
    }

    catalogCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const productName = checkbox.dataset.partName;
            const productDesc = checkbox.dataset.partDesc;
            const productQty = parseInt(checkbox.dataset.partQty) || 0;
            const productSupplier = checkbox.dataset.partSupplier || '';
            const productSupplierShort = checkbox.dataset.partSupplierShort || '';
            const productPrice = checkbox.dataset.partPrice || '';
            const productCurrency = checkbox.dataset.partCurrency || 'PLN';
            const productCategoryName = checkbox.dataset.partCatName || '';
            
            if (checkbox.checked) {
                selectedProducts[productName] = {
                    description: productDesc,
                    quantity: 1,
                    stockQuantity: productQty,
                    supplier: productSupplier,
                    supplierShort: productSupplierShort,
                    price: productPrice,
                    currency: productCurrency,
                    categoryName: productCategoryName
                };
            } else {
                delete selectedProducts[productName];
            }
            
            updateSelectedProductsDisplay();
        });
    });

    // Globalny checkbox "Zaznacz wszystkie" w tabeli produktów do pobrania
    if (selectAllRemoveCheckbox) {
        selectAllRemoveCheckbox.addEventListener('change', function() {
            const checkboxes = selectedProductsTable.querySelectorAll('.selected-product-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    removeAllBtn.addEventListener('click', () => {
        selectedProducts = {};
        catalogCheckboxes.forEach(cb => cb.checked = false);
        updateSelectedProductsDisplay();
    });

    fetchAllBtn.addEventListener('click', () => {
        if (Object.keys(selectedProducts).length === 0) {
            alert('Zaznacz przynajmniej jeden produkt');
            return;
        }

        // Pobierz wszystkie produkty
        let delay = 0;
        Object.entries(selectedProducts).forEach(([name, data]) => {
            setTimeout(() => {
                // Pobierz wybrany projekt dla tego produktu
                const projectSelect = document.querySelector(`.product-project[data-product-name="${name}"]`);
                const projectId = projectSelect ? projectSelect.value : '';
                
                const formData = new FormData();
                formData.append('name', name);
                formData.append('quantity', data.quantity);
                if (projectId) {
                    formData.append('project_id', projectId);
                }

                fetch('{{ route('parts.remove') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Błąd podczas pobierania');
                    return response.json();
                })
                .then(() => {
                    // Zaznaczony produkt został pobrany
                })
                .catch(err => {
                    console.error('Błąd:', err);
                });
            }, delay);
            delay += 300; // 300ms między żądaniami
        });

        // Wyczyść listę po chwili
        setTimeout(() => {
            selectedProducts = {};
            catalogCheckboxes.forEach(cb => cb.checked = false);
            updateSelectedProductsDisplay();
            
            // Zapamiętaj że katalog ma być otwarty
            localStorage.setItem('katalogOtwarty', 'true');
            window.location.reload();
        }, delay + 500);
    });
});
</script>

<script>
// ===== OBSŁUGA POBIERANIA Z MAGAZYNU (TRYB SZYBKI) =====
document.addEventListener('DOMContentLoaded', function() {
    const dispenseModal     = document.getElementById('dispense-modal');
    const dispensePartName  = document.getElementById('dispense-modal-part-name');
    const dispensePartQty   = document.getElementById('dispense-modal-part-qty');
    const dispenseQtyInput  = document.getElementById('dispense-modal-quantity-input');
    const dispenseProject   = document.getElementById('dispense-modal-project-select');
    const dispenseConfirm   = document.getElementById('dispense-modal-confirm-btn');
    const dispenseCancel    = document.getElementById('dispense-modal-cancel-btn');

    let currentDispensePartName = null;
    let currentDispensePartStock = 0;
    let dispensedChanges = [];

    function updateDispensedList() {
        const container = document.getElementById('dispensed-products-container');
        const list      = document.getElementById('dispensed-products-list');
        if (!container || !list) return;
        if (dispensedChanges.length === 0) { container.classList.add('hidden'); return; }
        container.classList.remove('hidden');
        list.innerHTML = '';
        dispensedChanges.forEach(function(c) {
            const item = document.createElement('div');
            item.className = 'flex items-center justify-between p-2 bg-red-50 rounded border border-red-200';
            item.innerHTML = '<div class="flex-1"><span class="font-medium">' + c.name + '</span>'
                + ' <span class="text-gray-600 ml-2">-' + c.quantity + ' szt.</span>'
                + (c.projectLabel ? ' <span class="text-indigo-600 ml-2 text-xs">' + c.projectLabel + '</span>' : '')
                + '</div>';
            list.appendChild(item);
        });
    }

    // Otwórz modal po kliknięciu ➖
    document.addEventListener('click', function(e) {
        const btn = e.target.classList.contains('remove-dispense-btn')
            ? e.target : e.target.closest('.remove-dispense-btn');
        if (!btn) return;
        currentDispensePartName  = btn.dataset.partName;
        currentDispensePartStock = parseInt(btn.dataset.partQuantity) || 0;
        dispensePartName.textContent = currentDispensePartName;
        dispensePartQty.textContent  = currentDispensePartStock;
        dispenseQtyInput.value = 1;
        dispenseQtyInput.max   = currentDispensePartStock;
        dispenseProject.value  = '';
        dispenseModal.classList.remove('hidden');
        setTimeout(function() { dispenseQtyInput.focus(); }, 80);
    });

    dispenseQtyInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') dispenseConfirm.click();
    });

    dispenseConfirm.addEventListener('click', function() {
        const qty = parseInt(dispenseQtyInput.value);
        if (!qty || qty < 1) { alert('Podaj poprawną ilość (minimum 1)'); return; }

        const projectId    = dispenseProject.value;
        const projectLabel = dispenseProject.options[dispenseProject.selectedIndex].text;

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}');
        formData.append('name', currentDispensePartName);
        formData.append('quantity', qty);
        if (projectId) formData.append('project_id', projectId);

        dispenseModal.classList.add('hidden');

        fetch('{{ route("parts.remove") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        })
        .then(function(r) { if (!r.ok) throw new Error('Błąd'); return r.json(); })
        .then(function(data) {
            dispensedChanges.push({
                name: currentDispensePartName,
                quantity: qty,
                projectLabel: projectId ? projectLabel : ''
            });
            updateDispensedList();

            const msg = document.createElement('div');
            msg.className = 'fixed top-4 right-4 z-50 bg-green-100 text-green-800 p-3 rounded shadow border border-green-300';
            msg.textContent = '✓ Pobrano ' + qty + ' szt. produktu „' + currentDispensePartName + '"';
            document.body.appendChild(msg);
            setTimeout(function() { msg.remove(); }, 3000);

            // Zaktualizuj stan w przyciskach
            document.querySelectorAll('.remove-dispense-btn').forEach(function(btn) {
                if (btn.dataset.partName === currentDispensePartName) {
                    var newQty = (data.quantity !== undefined) ? data.quantity : (currentDispensePartStock - qty);
                    btn.dataset.partQuantity = newQty;
                    btn.closest('tr').querySelector('.remove-dispense-btn').title = 'Pobierz z magazynu (stan: ' + newQty + ')';
                }
            });
        })
        .catch(function() {
            alert('❌ Błąd podczas pobierania produktu');
        });
    });

    dispenseCancel.addEventListener('click', function() { dispenseModal.classList.add('hidden'); });
    dispenseModal.addEventListener('click', function(e) { if (e.target === dispenseModal) dispenseModal.classList.add('hidden'); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !dispenseModal.classList.contains('hidden')) dispenseModal.classList.add('hidden');
    });
});
</script>

</body>
</html>
