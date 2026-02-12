<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Nowa Receptura</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @include('parts.menu')

<main class="max-w-4xl mx-auto mt-8 px-6 pb-12">
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <h1 class="text-3xl font-bold mb-6">Utwórz Nową Recepturę</h1>

    <form method="POST" action="{{ route('recipes.store') }}" class="bg-white rounded-lg shadow p-6">
        @csrf
        
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Nazwa receptury *</label>
            <input type="text" name="name" required class="w-full px-4 py-2 border rounded">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Opis</label>
            <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded"></textarea>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Ilość sztuk z receptury *</label>
            <input type="number" name="output_quantity" min="1" value="1" required class="w-full px-4 py-2 border rounded">
            <small class="text-gray-500">Ile sztuk produktu finalnego wychodzi z tej receptury (np. 100 sztuk chleba)</small>
        </div>
        
        <hr class="my-6">
        
        <!-- TABELA SKŁADNIKÓW -->
        <h2 class="text-xl font-bold mb-4">Składniki Receptury</h2>
        
        <!-- Sekcja Mąki -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold text-amber-700">🌾 Mąka (suma = 100%)</h3>
                <button type="button" onclick="addFlourRow()" class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm">
                    ➕ Dodaj Mąkę
                </button>
            </div>
            <div class="bg-amber-50 border-2 border-amber-300 rounded-lg p-4">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-amber-400">
                            <th class="text-left py-2 px-2 text-sm">Składnik</th>
                            <th class="text-left py-2 px-2 text-sm">Waga (kg)</th>
                            <th class="text-left py-2 px-2 text-sm">Procent (%)</th>
                            <th class="w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="flourTable">
                        <!-- Wiersze mąki dodawane dynamicznie -->
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-amber-500 font-bold">
                            <td class="py-2 px-2">SUMA</td>
                            <td class="py-2 px-2"><span id="flourTotalWeight">0</span> kg</td>
                            <td class="py-2 px-2"><span id="flourTotalPercent" class="text-amber-800">0</span>%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Sekcja Pozostałych Składników -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold text-green-700">📦 Pozostałe Składniki</h3>
                <button type="button" onclick="addIngredientRow()" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    ➕ Dodaj Składnik
                </button>
            </div>
            <div class="bg-green-50 border-2 border-green-300 rounded-lg p-4">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-green-400">
                            <th class="text-left py-2 px-2 text-sm">Składnik</th>
                            <th class="text-left py-2 px-2 text-sm">Ilość</th>
                            <th class="text-left py-2 px-2 text-sm">Procent (% od mąki)</th>
                            <th class="w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="ingredientTable">
                        <!-- Wiersze składników dodawane dynamicznie -->
                    </tbody>
                </table>
                <p class="text-xs text-gray-600 mt-2">💡 Procent odnosi się do całkowitej wagi mąki</p>
            </div>
        </div>
        
        <button type="submit" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold">
            💾 Zapisz Recepturę
        </button>
    </form>
</main>

<script>
let flourCounter = 0;
let ingredientCounter = 0;
const ingredients = @json($ingredients);

// ===== MĄKA =====
function addFlourRow() {
    flourCounter++;
    let ingredientOptions = '<option value="">-- Wybierz mąkę --</option>';
    ingredients.forEach(ing => {
        ingredientOptions += `<option value="${ing.id}" data-unit="${ing.unit}">${ing.name} (${ing.quantity} ${ing.unit})</option>`;
    });
    
    const html = `
        <tr id="flour-${flourCounter}">
            <td class="py-2 px-2">
                <select name="flour[${flourCounter}][ingredient_id]" required class="w-full px-2 py-1 border rounded text-sm flour-select">
                    ${ingredientOptions}
                </select>
            </td>
            <td class="py-2 px-2">
                <input type="number" name="flour[${flourCounter}][weight]" step="0.01" min="0.01" required 
                       oninput="updateFlourFromWeight(${flourCounter})" 
                       class="w-full px-2 py-1 border rounded text-sm flour-weight" id="flour-weight-${flourCounter}">
            </td>
            <td class="py-2 px-2">
                <input type="number" name="flour[${flourCounter}][percentage]" step="0.01" min="0.01" max="100" required 
                       oninput="updateFlourFromPercent(${flourCounter})" 
                       class="w-full px-2 py-1 border rounded text-sm flour-percentage" id="flour-percent-${flourCounter}">
            </td>
            <td class="py-2 px-2 text-center">
                <button type="button" onclick="removeFlourRow(${flourCounter})" class="text-red-600 hover:text-red-800">🗑️</button>
            </td>
        </tr>
    `;
    document.getElementById('flourTable').insertAdjacentHTML('beforeend', html);
}

function removeFlourRow(id) {
    document.getElementById(`flour-${id}`).remove();
    calculateFlourTotals();
}

function updateFlourFromWeight(id) {
    const weights = Array.from(document.querySelectorAll('.flour-weight')).map(input => parseFloat(input.value) || 0);
    const total = weights.reduce((a, b) => a + b, 0);
    if (total > 0) {
        // Przelicz procenty dla wszystkich mąk tak, by suma = 100%
        const newPercents = weights.map(w => w / total * 100);
        document.querySelectorAll('.flour-percentage').forEach((input, idx) => {
            input.value = newPercents[idx].toFixed(2);
        });
    }
    calculateFlourTotals();
}

function updateFlourFromPercent(id) {
    const totalWeight = getTotalFlourWeight();
    if (totalWeight > 0) {
        const percentage = parseFloat(document.getElementById(`flour-percent-${id}`).value) || 0;
        const weight = (totalWeight * percentage / 100).toFixed(3);
        document.getElementById(`flour-weight-${id}`).value = weight;
    }
    calculateFlourTotals();
}

function getTotalFlourWeight() {
    let total = 0;
    document.querySelectorAll('.flour-weight').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    return total;
}

function calculateFlourTotals() {
    const totalWeight = getTotalFlourWeight();
    let totalPercent = 0;
    
    document.querySelectorAll('.flour-percentage').forEach(input => {
        totalPercent += parseFloat(input.value) || 0;
    });
    
    document.getElementById('flourTotalWeight').textContent = totalWeight.toFixed(3);
    document.getElementById('flourTotalPercent').textContent = totalPercent.toFixed(2);
    
    const percentDisplay = document.getElementById('flourTotalPercent');
    if (Math.abs(totalPercent - 100) > 0.01 && totalPercent > 0) {
        percentDisplay.classList.add('text-red-600');
        percentDisplay.classList.remove('text-amber-800');
    } else {
        percentDisplay.classList.remove('text-red-600');
        percentDisplay.classList.add('text-amber-800');
    }
    
    recalculateIngredients();
}

// ===== POZOSTAŁE SKŁADNIKI =====
function addIngredientRow() {
    ingredientCounter++;
    let ingredientOptions = '<option value="">-- Wybierz składnik --</option>';
    ingredients.forEach(ing => {
        ingredientOptions += `<option value="${ing.id}" data-unit="${ing.unit}">${ing.name} (${ing.quantity} ${ing.unit})</option>`;
    });
    
    const html = `
        <tr id="ingredient-${ingredientCounter}">
            <td class="py-2 px-2">
                <select name="ingredient[${ingredientCounter}][ingredient_id]" required onchange="updateIngredientUnit(${ingredientCounter})" class="w-full px-2 py-1 border rounded text-sm ingredient-select">
                    ${ingredientOptions}
                </select>
            </td>
            <td class="py-2 px-2 flex items-center gap-1">
                <input type="number" name="ingredient[${ingredientCounter}][quantity]" step="0.01" min="0.01" required 
                       oninput="updateIngredientPercentage(${ingredientCounter})" 
                       class="w-24 px-2 py-1 border rounded text-sm ingredient-quantity" id="ingredient-quantity-${ingredientCounter}">
                <span class="text-xs text-gray-500 ml-1" id="ingredient-unit-${ingredientCounter}"></span>
            </td>
            <td class="py-2 px-2">
                <input type="number" name="ingredient[${ingredientCounter}][percentage]" step="0.01" min="0.01" required 
                       oninput="updateIngredientQuantity(${ingredientCounter})" 
                       class="w-full px-2 py-1 border rounded text-sm ingredient-percentage" id="ingredient-percent-${ingredientCounter}">
            </td>
            <td class="py-2 px-2 text-center">
                <button type="button" onclick="removeIngredientRow(${ingredientCounter})" class="text-red-600 hover:text-red-800">🗑️</button>
            </td>
        </tr>
    `;
    document.getElementById('ingredientTable').insertAdjacentHTML('beforeend', html);
}

function removeIngredientRow(id) {
    document.getElementById(`ingredient-${id}`).remove();
}

function updateIngredientUnit(id) {
    const select = document.querySelector(`#ingredient-${id} .ingredient-select`);
    const unit = select.options[select.selectedIndex]?.dataset?.unit || '';
    document.getElementById(`ingredient-unit-${id}`).textContent = unit;
}

function updateIngredientPercentage(id) {
    const flourTotal = getTotalFlourWeight();
    if (flourTotal > 0) {
        const quantity = parseFloat(document.getElementById(`ingredient-quantity-${id}`).value) || 0;
        const percentage = (quantity / flourTotal * 100).toFixed(2);
        document.getElementById(`ingredient-percent-${id}`).value = percentage;
    }
}

function updateIngredientQuantity(id) {
    const flourTotal = getTotalFlourWeight();
    if (flourTotal > 0) {
        const percentage = parseFloat(document.getElementById(`ingredient-percent-${id}`).value) || 0;
        const quantity = (flourTotal * percentage / 100).toFixed(3);
        document.getElementById(`ingredient-quantity-${id}`).value = quantity;
    }
}

function recalculateIngredients() {
    const flourTotal = getTotalFlourWeight();
    if (flourTotal > 0) {
        document.querySelectorAll('[id^="ingredient-percent-"]').forEach(input => {
            const id = input.id.replace('ingredient-percent-', '');
            updateIngredientQuantity(id);
        });
    }
}

// ===== WALIDACJA FORMULARZA =====
document.querySelector('form').addEventListener('submit', function(e) {
    const flourTotal = parseFloat(document.getElementById('flourTotalPercent').textContent);
    
    if (Math.abs(flourTotal - 100) > 0.01) {
        e.preventDefault();
        alert('BŁĄD: Suma procentów mąki musi wynosić 100%! Aktualnie: ' + flourTotal + '%');
        return false;
    }
    
    const flourRows = document.querySelectorAll('[id^="flour-"]').length;
    if (flourRows === 0) {
        e.preventDefault();
        alert('Musisz dodać przynajmniej jeden rodzaj mąki!');
        return false;
    }
});
</script>

</body>
</html>
