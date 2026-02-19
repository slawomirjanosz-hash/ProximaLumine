<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edycja listy projektowej - {{ $projectList->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

@include('parts.menu')

<script>
// --- Ochrona przed utratą zmian ---
let initialListProducts = JSON.stringify(@json($listProductsData));
let initialListName = @json($projectList->name);
let isSaved = true;

function isListChanged() {
    const currentProducts = JSON.stringify(listProducts);
    const currentName = document.querySelector('input[name="name"]').value;
    return currentProducts !== initialListProducts || currentName !== initialListName;
}

window.addEventListener('DOMContentLoaded', function() {
    // Oznacz jako zapisane po kliknięciu "Zapisz listę"
    const saveBtn = document.getElementById('save-list-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            isSaved = true;
        });
    }
    // Oznacz jako zmienione przy zmianie nazwy
    const nameInput = document.querySelector('input[name="name"]');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            isSaved = false;
        });
    }
    // Oznacz jako zmienione przy zmianie produktów
    window.renderProductsList = (function(orig) {
        return function() {
            isSaved = false;
            return orig.apply(this, arguments);
        };
    })(window.renderProductsList);
});

window.addEventListener('beforeunload', function(e) {
    if (!isSaved && isListChanged()) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});
</script>

<div class="max-w-6xl mx-auto mt-6">
    <a href="{{ route('magazyn.projects.settings') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow transition-all text-gray-700 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Powrót do ustawień
    </a>
</div>

{{-- KOMUNIKATY --}}
@if(session('success'))
    <div class="max-w-6xl mx-auto mt-4 bg-green-100 text-green-800 p-2 rounded">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="max-w-6xl mx-auto mt-4 bg-red-100 text-red-800 p-2 rounded">
        {{ session('error') }}
    </div>
@endif

<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow mt-6">
    
    <h2 class="text-xl font-bold mb-6">Edycja listy projektowej: {{ $projectList->name }}</h2>
    
    {{-- Informacje podstawowe --}}
    <div class="bg-gray-50 p-4 rounded mb-6">
        <form method="POST" action="{{ route('magazyn.projects.lists.update', $projectList) }}" class="flex items-end gap-4">
            @csrf
            @method('PUT')
            <div class="flex-1">
                <label class="block text-sm font-medium mb-2">Nazwa listy *</label>
                <input type="text" name="name" value="{{ $projectList->name }}" required class="w-full px-3 py-2 border rounded">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded">
                Zapisz nazwę
            </button>
        </form>
    </div>

    {{-- Przyciski akcji --}}
    <div class="mb-6 flex gap-3">
        <button type="button" id="pickup-products-btn" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded font-semibold flex items-center gap-2">
            ➖ Pobierz na listę
        </button>        <button type="button" id="import-excel-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold flex items-center gap-2">
            📄 Załaduj z Excela
        </button>        <button type="button" id="save-list-btn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-semibold">
            💾 Zapisz listę
        </button>
    </div>

    {{-- Tabela produktów na liście --}}
    <div class="mb-6">
        <h3 class="font-semibold mb-3">Produkty na liście</h3>
        <div id="products-list-container">
            <!-- Tabela zostanie wypełniona przez JavaScript -->
        </div>
    </div>

</div>

{{-- MODAL: Pobierz produkty --}}
<div id="pickup-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-7xl w-full max-h-[90vh] overflow-y-auto mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Katalog produktów - wybierz produkty do dodania</h3>
            <button type="button" id="close-pickup-modal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        
        {{-- Filtry --}}
        <div class="grid grid-cols-2 gap-2 mb-4">
            <input type="text" id="filter-name" placeholder="Filtruj po nazwie..." class="border p-2 rounded">
            <input type="text" id="filter-supplier" placeholder="Filtruj po dostawcy..." class="border p-2 rounded">
        </div>

        {{-- Przyciski akcji --}}
        <div class="flex items-center gap-2 mb-4">
            <button type="button" id="select-all-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm">☑️ Zaznacz wszystkie</button>
            <button type="button" id="deselect-all-btn" class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-2 rounded text-sm">☐ Odznacz wszystkie</button>
            <button type="button" id="add-selected-btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold ml-auto">➕ Dodaj zaznaczone</button>
        </div>

        {{-- Tabela --}}
        <div class="overflow-auto max-h-[500px] border rounded">
            <table id="parts-table" class="w-full border-collapse text-sm">
                <thead class="bg-gray-100 sticky top-0">
                    <tr>
                        <th class="border p-2 text-center">☐</th>
                        <th class="border p-2 text-left">Produkty</th>
                        <th class="border p-2 text-left">Opis</th>
                        <th class="border p-2 text-left">Dostawca</th>
                        <th class="border p-2 text-left">Kategoria</th>
                        <th class="border p-2 text-center">Stan</th>
                        <th class="border p-2 text-center">Ilość</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $part)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2 text-center">
                                <input type="checkbox" class="part-checkbox w-4 h-4 cursor-pointer" 
                                       data-part-id="{{ $part->id }}"
                                       data-part-name="{{ $part->name }}"
                                       data-part-code="{{ $part->qr_code }}"
                                       data-part-description="{{ $part->description }}"
                                       data-part-qty="{{ $part->quantity }}">
                            </td>
                            <td class="border p-2">{{ $part->name }}</td>
                            <td class="border p-2 text-xs text-gray-700">{{ $part->description ?? '-' }}</td>
                            <td class="border p-2 text-xs">{{ $part->supplier ?? '-' }}</td>
                            <td class="border p-2">{{ $part->category->name ?? '-' }}</td>
                            <td class="border p-2 text-center {{ $part->quantity == 0 ? 'text-red-600 bg-red-50' : '' }}">{{ $part->quantity }}</td>
                            <td class="border p-2 text-center">
                                <input type="number" class="qty-input border rounded px-2 py-1 w-16 text-sm" 
                                       data-part-id="{{ $part->id }}" 
                                       value="1" min="1">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL: Import z Excela --}}
<div id="import-excel-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Załaduj listę z pliku Excel</h3>
            <button type="button" id="close-import-modal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">
                <strong>Format pliku Excel:</strong> Plik musi zawierać kolumny z nazwami produktów oraz ilościami.
            </p>
            <p class="text-sm text-gray-600 mb-2">
                <strong>Wymagane kolumny:</strong>
            </p>
            <ul class="text-xs text-gray-600 list-disc list-inside mb-4">
                <li><strong>Nazwa produktu</strong>: użyj <strong>jednej</strong> z nazw kolumn: <code>Produkty</code>, <code>Nazwa</code> lub <code>Name</code> (wystarczy jedna z nich, nie wszystkie). Nazwa produktu musi istnieć w magazynie.</li>
                <li><strong>Ilość</strong>: użyj <strong>jednej</strong> z nazw kolumn: <code>Ilość</code>, <code>Quantity</code> lub <code>Qty</code> (wystarczy jedna z nich, nie wszystkie).</li>
            </ul>
            <p class="text-xs text-yellow-600">
                ⚠️ Produkty, które nie istnieją w magazynie, zostaną pominięte. W pliku Excel wystarczy jedna kolumna z nazwą produktu i jedna z ilością (nie wpisuj wszystkich wariantów naraz).
            </p>
        </div>
        
        <form id="import-excel-form" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Wybierz plik Excel (.xlsx, .xls)</label>
                <input type="file" id="excel-file-input" name="excel_file" accept=".xlsx,.xls" required 
                       class="w-full px-3 py-2 border rounded">
            </div>
            
            <div id="import-progress" class="hidden mb-4">
                <div class="bg-blue-100 rounded-full h-2">
                    <div id="import-progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="import-status" class="text-sm text-gray-600 mt-2 text-center"></p>
            </div>
            
            <div class="flex gap-2 justify-end">
                <button type="button" id="cancel-import-btn" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                    Anuluj
                </button>
                <button type="submit" id="submit-import-btn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">
                    📤 Importuj
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Stan listy produktów (przechowuje produkty dodane do listy)
let listProducts = @json($listProductsData);

document.addEventListener('DOMContentLoaded', function() {
    // Renderuj listę produktów
    renderProductsList();
    
    // Otwórz modal pobierania
    document.getElementById('pickup-products-btn').addEventListener('click', function() {
        document.getElementById('pickup-modal').classList.remove('hidden');
    });
    
    // Zamknij modal
    document.getElementById('close-pickup-modal').addEventListener('click', function() {
        document.getElementById('pickup-modal').classList.add('hidden');
    });
    
    // Zamknij po kliknięciu w tło
    document.getElementById('pickup-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
    
    // Modal importu Excel
    document.getElementById('import-excel-btn').addEventListener('click', function() {
        document.getElementById('import-excel-modal').classList.remove('hidden');
    });
    
    document.getElementById('close-import-modal').addEventListener('click', function() {
        document.getElementById('import-excel-modal').classList.add('hidden');
    });
    
    document.getElementById('cancel-import-btn').addEventListener('click', function() {
        document.getElementById('import-excel-modal').classList.add('hidden');
    });
    
    document.getElementById('import-excel-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
    
    // Obsługa formularza importu Excel
    document.getElementById('import-excel-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('excel-file-input');
        const file = fileInput.files[0];
        
        if (!file) {
            alert('Proszę wybrać plik');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', file);
        
        const progressContainer = document.getElementById('import-progress');
        const progressBar = document.getElementById('import-progress-bar');
        const submitBtn = document.getElementById('submit-import-btn');
        
        progressContainer.classList.remove('hidden');
        progressBar.style.width = '10%';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Importowanie...';
        
        try {
            const response = await fetch('{{ route("magazyn.projects.lists.importExcel", $projectList) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });
            
            progressBar.style.width = '70%';
            const data = await response.json();
            
            if (response.ok) {
                progressBar.style.width = '100%';
                
                // Sprawdź czy są produkty które nie zostały znalezione
                if (data.skippedProducts && data.skippedProducts.length > 0) {
                    const maxDisplay = 10; // Maksymalnie 10 produktów w komunikacie
                    const displayProducts = data.skippedProducts.slice(0, maxDisplay);
                    const remaining = data.skippedProducts.length - maxDisplay;
                    
                    let message = `⚠️ UWAGA!\n\nNastępujące produkty nie zostały znalezione w magazynie:\n\n`;
                    displayProducts.forEach(name => {
                        message += `• ${name}\n`;
                    });
                    
                    if (remaining > 0) {
                        message += `\n... i ${remaining} innych produktów\n`;
                    }
                    
                    message += `\n\nZnaleziono: ${data.products.length} produktów\n`;
                    message += `Nie znaleziono: ${data.skippedProducts.length} produktów\n\n`;
                    message += `Czy chcesz dodać do listy tylko te produkty, które zostały znalezione w magazynie?`;
                    
                    if (!confirm(message)) {
                        // Użytkownik anulował import
                        document.getElementById('import-excel-modal').classList.add('hidden');
                        fileInput.value = '';
                        return;
                    }
                }
                
                // Dodaj zaimportowane produkty do listProducts
                data.products.forEach(product => {
                    const existing = listProducts.find(p => p.id == product.id);
                    if (existing) {
                        existing.quantity += product.quantity;
                    } else {
                        listProducts.push(product);
                    }
                });
                
                renderProductsList();
                
                let successMessage = `Zaimportowano ${data.products.length} produktów`;
                if (data.skippedProducts && data.skippedProducts.length > 0) {
                    successMessage += `\n\nPominięto ${data.skippedProducts.length} produktów, które nie istnieją w magazynie.`;
                }
                alert(successMessage);
                
                document.getElementById('import-excel-modal').classList.add('hidden');
                fileInput.value = '';
            } else {
                alert('Błąd: ' + (data.message || 'Nie udało się zaimportować pliku'));
            }
        } catch (error) {
            alert('Błąd połączenia: ' + error.message);
        } finally {
            progressContainer.classList.add('hidden');
            progressBar.style.width = '0%';
            submitBtn.disabled = false;
            submitBtn.textContent = '📤 Importuj';
        }
    });
    
    // Filtry
    const filterName = document.getElementById('filter-name');
    const filterSupplier = document.getElementById('filter-supplier');
    const tbody = document.querySelector('#parts-table tbody');
    
    function applyFilters() {
        const nameVal = filterName.value.toLowerCase();
        const supplierVal = filterSupplier.value.toLowerCase();
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const name = row.cells[1]?.textContent.toLowerCase() || '';
            const supplier = row.cells[3]?.textContent.toLowerCase() || '';
            
            const nameMatch = name.includes(nameVal);
            const supplierMatch = supplier.includes(supplierVal);

            if (nameMatch && supplierMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    filterName.addEventListener('input', applyFilters);
    filterSupplier.addEventListener('input', applyFilters);
    
    // Zaznacz/Odznacz wszystkie
    document.getElementById('select-all-btn').addEventListener('click', () => {
        document.querySelectorAll('.part-checkbox').forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = true;
            }
        });
    });
    
    document.getElementById('deselect-all-btn').addEventListener('click', () => {
        document.querySelectorAll('.part-checkbox').forEach(cb => cb.checked = false);
    });
    
    // Dodaj zaznaczone
    document.getElementById('add-selected-btn').addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.part-checkbox:checked'));
        
        if (selected.length === 0) {
            alert('Nie zaznaczono żadnych produktów');
            return;
        }
        
        selected.forEach(cb => {
            const partId = cb.dataset.partId;
            const qtyInput = document.querySelector(`.qty-input[data-part-id="${partId}"]`);
            const quantity = parseInt(qtyInput.value) || 1;
            
            // Sprawdź czy produkt już jest na liście
            const existing = listProducts.find(p => p.id == partId);
            if (existing) {
                existing.quantity += quantity;
            } else {
                listProducts.push({
                    id: partId,
                    name: cb.dataset.partName,
                    code: cb.dataset.partCode,
                    code_description: cb.dataset.partDescription,
                    quantity: quantity
                });
            }
            
            cb.checked = false;
        });
        
        renderProductsList();
        document.getElementById('pickup-modal').classList.add('hidden');
    });
    
    // Zapisz listę
    document.getElementById('save-list-btn').addEventListener('click', async function() {
        if (listProducts.length === 0) {
            alert('Lista jest pusta');
            return;
        }
        
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Zapisywanie...';
        
        try {
            const response = await fetch('{{ route("magazyn.projects.lists.saveProducts", $projectList) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ products: listProducts })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert('Lista została zapisana');
                window.location.reload();
            } else {
                alert('Błąd: ' + (data.message || 'Nie udało się zapisać listy'));
            }
        } catch (error) {
            alert('Błąd połączenia: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.textContent = '💾 Zapisz listę';
        }
    });
});

function renderProductsList() {
    const container = document.getElementById('products-list-container');
    
    if (listProducts.length === 0) {
        container.innerHTML = '<p class="text-gray-600 p-4 bg-gray-50 rounded">Brak produktów na liście. Użyj przycisku "Pobierz na listę" aby dodać produkty.</p>';
        return;
    }
    
    let html = `
        <div class="border rounded overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left p-2 border-b">Produkt</th>
                        <th class="text-center p-2 border-b" style="width: 100px;">Kod</th>
                        <th class="text-left p-2 border-b">Opis kodu</th>
                        <th class="text-center p-2 border-b" style="width: 120px;">Ilość</th>
                        <th class="text-center p-2 border-b" style="width: 80px;">Akcje</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    listProducts.forEach((product, index) => {
        const qrCodeUrl = product.code ? `https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${encodeURIComponent(product.code)}` : '';
        html += `
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2">${product.name}</td>
                <td class="p-2 text-center">
                    ${product.code ? `<img src="${qrCodeUrl}" alt="QR Code" class="inline-block" style="width: 60px; height: 60px;">` : '-'}
                </td>
                <td class="p-2">${product.code || '-'}</td>
                <td class="p-2 text-center">
                    <input type="number" class="product-qty-input border rounded px-2 py-1 w-16 text-center text-sm" 
                           data-index="${index}" value="${product.quantity}" min="1">
                </td>
                <td class="p-2 text-center">
                    <button type="button" class="remove-product-btn text-red-600 hover:text-red-800 text-xs" data-index="${index}">
                        🗑️ Usuń
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Event listeners dla edycji ilości
    document.querySelectorAll('.product-qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const index = parseInt(this.dataset.index);
            const newQty = parseInt(this.value) || 1;
            listProducts[index].quantity = newQty;
        });
    });
    
    // Event listeners dla usuwania
    document.querySelectorAll('.remove-product-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            listProducts.splice(index, 1);
            renderProductsList();
        });
    });
}
</script>

</body>
</html>
