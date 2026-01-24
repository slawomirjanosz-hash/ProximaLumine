<!-- ZARZĄDZANIE TYPAMI KLIENTÓW -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-2xl font-bold mb-4">Typy Klientów</h2>
    <p class="text-gray-600 mb-4">Zarządzaj typami klientów, kolorami i możliwością dodawania nowych typów.</p>
    <table class="w-full border-collapse mb-4">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2 text-left">Nazwa typu</th>
                <th class="border p-2 text-left">Slug</th>
                <th class="border p-2 text-left">Kolor</th>
                <th class="border p-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customerTypes as $type)
                <tr class="hover:bg-gray-50">
                    <td class="border p-2 font-semibold">{{ $type->name }}</td>
                    <td class="border p-2"><code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $type->slug }}</code></td>
                    <td class="border p-2">
                        <span class="inline-block px-3 py-1 rounded text-white" style="background-color: {{ $type->color }};">{{ $type->color }}</span>
                    </td>
                    <td class="border p-2 text-center">
                        <button onclick="editCustomerType({{ $type->id }})" class="text-blue-600 hover:underline">✏️ Edytuj</button>
                        <form action="{{ route('crm.customer-types.destroy', $type->id) }}" method="POST" class="inline" onsubmit="return confirm('Usunąć typ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-2">🗑️ Usuń</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button onclick="showAddCustomerTypeModal()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">➕ Dodaj Nowy Typ</button>
</div>

<!-- MODAL DODAWANIA/EDYCJI TYPU KLIENTA -->
<div id="customer-type-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-xl font-bold mb-4" id="customer-type-modal-title">Dodaj Typ Klienta</h3>
        <form id="customer-type-form" method="POST" action="{{ route('crm.customer-types.store') }}">
            @csrf
            <input type="hidden" id="customer-type-method" name="_method" value="">
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Nazwa typu *</label>
                <input type="text" name="name" id="customer-type-name" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Slug (unikalny identyfikator) *</label>
                <input type="text" name="slug" id="customer-type-slug" required class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Np: klient, partner, konkurencja (bez spacji, małe litery)</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Kolor (hex) *</label>
                <input type="color" name="color" id="customer-type-color" required class="w-full border rounded px-3 py-2 h-10">
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeCustomerTypeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Zapisz</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddCustomerTypeModal() {
    document.getElementById('customer-type-modal-title').textContent = 'Dodaj Typ Klienta';
    document.getElementById('customer-type-form').action = '{{ route("crm.customer-types.store") }}';
    document.getElementById('customer-type-method').value = '';
    document.getElementById('customer-type-name').value = '';
    document.getElementById('customer-type-slug').value = '';
    document.getElementById('customer-type-slug').readOnly = false;
    document.getElementById('customer-type-color').value = '#3b82f6';
    document.getElementById('customer-type-modal').classList.remove('hidden');
}
function editCustomerType(id) {
    fetch(`/crm/customer-types/${id}`)
        .then(res => res.json())
        .then(type => {
            document.getElementById('customer-type-modal-title').textContent = 'Edytuj Typ Klienta';
            document.getElementById('customer-type-form').action = `/crm/customer-types/${id}`;
            document.getElementById('customer-type-method').value = 'PUT';
            document.getElementById('customer-type-name').value = type.name;
            document.getElementById('customer-type-slug').value = type.slug;
            document.getElementById('customer-type-slug').readOnly = true;
            document.getElementById('customer-type-color').value = type.color;
            document.getElementById('customer-type-modal').classList.remove('hidden');
        });
}
function closeCustomerTypeModal() {
    document.getElementById('customer-type-modal').classList.add('hidden');
}
</script>
