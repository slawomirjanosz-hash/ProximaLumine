<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Magazyn – Pobierz produkty do projektu</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

{{-- MENU --}}
@include('parts.menu')

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow mt-6">
    <div class="mb-6">
        <a href="{{ route('magazyn.projects.show', $project->id) }}" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">← Powrót do projektu</a>
        <h2 class="text-2xl font-bold">Pobierz produkty do projektu</h2>
        <div class="mt-2 text-sm text-gray-600">
            <p><strong>Projekt:</strong> {{ $project->project_number }} - {{ $project->name }}</p>
            <p><strong>Odpowiedzialny:</strong> {{ $project->responsibleUser->name ?? '-' }}</p>
        </div>
    </div>

    {{-- KOMUNIKAT BŁĘDU --}}
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-2 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- SEKCJA: WYBIERZ PRODUKTY Z KATALOGU --}}
    <div class="bg-white rounded shadow mb-6 border">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Katalog produktów</h3>

            {{-- Filtry --}}
            <div class="grid grid-cols-2 gap-2 mb-4">
                <input type="text" id="filter-name" placeholder="Filtruj po nazwie..." class="border p-2 rounded">
                <input type="text" id="filter-supplier" placeholder="Filtruj po dostawcy..." class="border p-2 rounded">
            </div>

            {{-- Przyciski akcji --}}
            <div class="flex items-center gap-2 mb-4">
                <button type="button" id="select-all-catalog" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm">☑️ Zaznacz wszystkie</button>
                <button type="button" id="deselect-all-catalog" class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-2 rounded text-sm">☐ Odznacz wszystkie</button>
                <button type="button" id="pickup-selected" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold ml-auto">➖ Pobierz zaznaczone</button>
            </div>

            {{-- Tabela --}}
            <div class="overflow-auto max-h-[600px]">
                <table id="parts-table" class="w-full border border-collapse text-sm">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="border p-2 text-center text-xs whitespace-nowrap min-w-[3rem]">☐</th>
                            <th class="border p-2 text-left text-xs whitespace-nowrap min-w-[8rem] max-w-[12rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('name')">Produkty <span class="align-middle ml-1 text-gray-400">↕</span></th>
                            <th class="border p-2 text-left text-xs whitespace-nowrap min-w-[8rem] max-w-[14rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('description')">Opis <span class="align-middle ml-1 text-gray-400">↕</span></th>
                            <th class="border p-2 text-left text-xs whitespace-nowrap min-w-[4rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('supplier')">Dost. <span class="align-middle ml-1 text-gray-400">↕</span></th>
                            <th class="border p-2 text-left text-xs whitespace-nowrap min-w-[6rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('category')">Kategoria <span class="align-middle ml-1 text-gray-400">↕</span></th>
                            <th class="border p-2 text-center text-xs whitespace-nowrap min-w-[2.5rem] max-w-[4rem] cursor-pointer hover:bg-gray-200" onclick="sortTable('quantity')">Stan <span class="align-middle ml-1 text-gray-400">↕</span></th>
                            <th class="border p-1 text-center text-xs whitespace-nowrap min-w-[4.5rem]" style="width: 6ch;">User</th>
                            <th class="border p-2 text-center text-xs whitespace-nowrap min-w-[5rem]">Ilość</th>
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
                                           data-part-id="{{ $p->id }}"
                                           data-part-name="{{ $p->name }}" 
                                           data-part-desc="{{ $p->description ?? '' }}" 
                                           data-part-supplier="{{ $p->supplier ?? '' }}" 
                                           data-part-supplier-short="{{ $supplierShort }}"
                                           data-part-qty="{{ $p->quantity }}"
                                           data-part-cat-name="{{ $p->category->name ?? '' }}">
                                </td>
                                <td class="border p-2">{{ $p->name }}</td>
                                <td class="border p-2 text-xs text-gray-700">{{ $p->description ?? '-' }}</td>
                                <td class="border p-2 text-center text-xs text-gray-700" style="width: 5.5rem;">{{ $supplierShort ?: '-' }}</td>
                                <td class="border p-2">{{ $p->category->name ?? '-' }}</td>
                                <td class="border p-2 text-center font-bold text-xs {{ $p->quantity == 0 ? 'text-red-600 bg-red-50' : '' }}">{{ $p->quantity }}</td>
                                <td class="border p-2 text-center text-xs text-gray-600">{{ $p->lastModifiedBy ? $p->lastModifiedBy->short_name : '-' }}</td>
                                <td class="border p-2 text-center">
                                    <input type="number" class="quantity-input border rounded px-2 py-1 w-16 text-sm" 
                                           data-part-id="{{ $p->id }}" 
                                           value="1" min="1" max="{{ $p->quantity }}">
                                </td>
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterName = document.getElementById('filter-name');
    const filterSupplier = document.getElementById('filter-supplier');
    const table = document.getElementById('parts-table');
    const tbody = table.querySelector('tbody');

    // Filtrowanie
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
    document.getElementById('select-all-catalog').addEventListener('click', () => {
        document.querySelectorAll('.catalog-checkbox').forEach(cb => {
            if (cb.closest('tr').style.display !== 'none') {
                cb.checked = true;
            }
        });
    });

    document.getElementById('deselect-all-catalog').addEventListener('click', () => {
        document.querySelectorAll('.catalog-checkbox').forEach(cb => cb.checked = false);
    });

    // Pobierz zaznaczone
    document.getElementById('pickup-selected').addEventListener('click', async () => {
        const selected = Array.from(document.querySelectorAll('.catalog-checkbox:checked'));
        
        if (selected.length === 0) {
            alert('Nie zaznaczono żadnych produktów');
            return;
        }

        const products = selected.map(cb => {
            const partId = cb.dataset.partId;
            const quantityInput = document.querySelector(`.quantity-input[data-part-id="${partId}"]`);
            const quantity = parseInt(quantityInput.value) || 1;
            const maxQty = parseInt(cb.dataset.partQty);
            
            if (quantity > maxQty) {
                alert(`${cb.dataset.partName}: Ilość (${quantity}) przekracza stan magazynowy (${maxQty})`);
                return null;
            }
            
            return {
                id: partId,
                quantity: quantity
            };
        }).filter(p => p !== null);

        if (products.length === 0) {
            return;
        }

        // Wyślij formularz
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("magazyn.projects.pickup.store", $project->id) }}';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        products.forEach((product, index) => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = `products[${index}][id]`;
            idInput.value = product.id;
            form.appendChild(idInput);
            
            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = `products[${index}][quantity]`;
            qtyInput.value = product.quantity;
            form.appendChild(qtyInput);
        });
        
        document.body.appendChild(form);
        form.submit();
    });
});

// Sortowanie tabeli
let sortColumn = null;
let sortAscending = true;

function sortTable(column) {
    const table = document.getElementById('parts-table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    if (sortColumn === column) {
        sortAscending = !sortAscending;
    } else {
        sortColumn = column;
        sortAscending = true;
    }

    const columnIndex = {
        'name': 1,
        'description': 2,
        'supplier': 3,
        'category': 4,
        'quantity': 5
    }[column];

    rows.sort((a, b) => {
        let aVal = a.cells[columnIndex]?.textContent.trim() || '';
        let bVal = b.cells[columnIndex]?.textContent.trim() || '';

        if (column === 'quantity') {
            aVal = parseInt(aVal) || 0;
            bVal = parseInt(bVal) || 0;
        }

        if (aVal < bVal) return sortAscending ? -1 : 1;
        if (aVal > bVal) return sortAscending ? 1 : -1;
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}
</script>

</body>
</html>
