<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Katalog Składników</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('receptury') }}" class="text-2xl font-bold text-blue-600">← Katalog Składników</a>
        </div>
        <nav class="flex gap-2 items-center">
            <span class="text-gray-700 text-sm">{{ Auth::user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-3 py-2 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded">Wyloguj</button>
            </form>
        </nav>
    </div>
</header>

<main class="max-w-7xl mx-auto mt-8 px-6">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Katalog Składników</h1>
        <button onclick="showAddModal()" class="px-6 py-3 bg-green-600 text-white rounded hover:bg-green-700">
            ➕ Dodaj Składnik
        </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nazwa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ilość</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jednostka</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cena</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Akcje</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($ingredients as $ingredient)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $ingredient->name }}</td>
                        <td class="px-6 py-4">{{ $ingredient->description ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $ingredient->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $ingredient->unit }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $ingredient->price ? number_format($ingredient->price, 2) . ' zł' : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="editIngredient({{ $ingredient->id }}, '{{ $ingredient->name }}', '{{ $ingredient->description }}', {{ $ingredient->quantity }}, '{{ $ingredient->unit }}', {{ $ingredient->price ?? 0 }})" 
                                    class="text-blue-600 hover:text-blue-800 mr-3">✏️ Edytuj</button>
                            <form action="{{ route('recipes.ingredients.destroy', $ingredient) }}" method="POST" class="inline" onsubmit="return confirm('Czy na pewno usunąć ten składnik?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">🗑️ Usuń</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Brak składników w katalogu</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>

<!-- Modal dodawania/edycji składnika -->
<div id="ingredientModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 id="modalTitle" class="text-lg font-bold mb-4">Dodaj Składnik</h3>
        <form id="ingredientForm" method="POST" action="{{ route('recipes.ingredients.store') }}">
            @csrf
            <input type="hidden" id="methodField" name="_method" value="POST">
            <input type="hidden" id="ingredientId" name="ingredient_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nazwa *</label>
                <input type="text" name="name" id="ingredientName" required class="w-full px-3 py-2 border rounded">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Opis</label>
                <textarea name="description" id="ingredientDescription" rows="2" class="w-full px-3 py-2 border rounded"></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Ilość *</label>
                <input type="number" name="quantity" id="ingredientQuantity" step="0.01" min="0" required class="w-full px-3 py-2 border rounded">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Jednostka *</label>
                <select name="unit" id="ingredientUnit" required class="w-full px-3 py-2 border rounded">
                    <option value="kg">kg</option>
                    <option value="g">g</option>
                    <option value="l">l</option>
                    <option value="ml">ml</option>
                    <option value="szt">szt</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Cena za jednostkę (zł)</label>
                <input type="number" name="price" id="ingredientPrice" step="0.01" min="0" class="w-full px-3 py-2 border rounded">
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Zapisz</button>
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Dodaj Składnik';
    document.getElementById('ingredientForm').action = '{{ route('recipes.ingredients.store') }}';
    document.getElementById('methodField').value = 'POST';
    document.getElementById('ingredientForm').reset();
    document.getElementById('ingredientModal').classList.remove('hidden');
}

function editIngredient(id, name, description, quantity, unit, price) {
    document.getElementById('modalTitle').textContent = 'Edytuj Składnik';
    document.getElementById('ingredientForm').action = '/receptury/skladniki/' + id;
    document.getElementById('methodField').value = 'PUT';
    document.getElementById('ingredientName').value = name;
    document.getElementById('ingredientDescription').value = description;
    document.getElementById('ingredientQuantity').value = quantity;
    document.getElementById('ingredientUnit').value = unit;
    document.getElementById('ingredientPrice').value = price;
    document.getElementById('ingredientModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('ingredientModal').classList.add('hidden');
}
</script>

</body>
</html>
