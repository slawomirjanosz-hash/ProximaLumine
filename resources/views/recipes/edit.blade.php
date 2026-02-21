<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj Recepturę</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
    @include('parts.menu')

<main class="max-w-4xl mx-auto mt-8 px-6 pb-12">
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif


    <form method="POST" action="{{ route('recipes.update', $recipe) }}" class="bg-white rounded-lg shadow p-4">
        @csrf
        @method('PUT')
        <div class="flex flex-col sm:flex-row sm:items-end gap-2 mb-3">
            <div class="flex-1">
                <label class="block text-xs font-medium mb-1">Nazwa receptury *</label>
                <input type="text" name="name" value="{{ $recipe->name }}" required class="w-full px-2 py-1 border rounded text-sm">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium mb-1">Ilość sztuk *</label>
                <input type="number" name="output_quantity" min="1" value="{{ $recipe->output_quantity }}" required class="w-full px-2 py-1 border rounded text-sm">
            </div>
        </div>
        <div class="mb-3">
            <label class="block text-xs font-medium mb-1">Opis</label>
            <textarea name="description" rows="2" class="w-full px-2 py-1 border rounded text-sm">{{ $recipe->description }}</textarea>
        </div>
        
        <hr class="my-6">
        
        <!-- TABELA SKŁADNIKÓW -->
        <h2 class="text-xl font-bold mb-4">Składniki Receptury</h2>
        
        <!-- Sekcja Mąki -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-base font-semibold text-amber-700">🌾 Mąka (suma = 100%)</h3>
                <button type="button" onclick="addFlourRow()" class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm">
                    ➕ Dodaj Mąkę
                </button>
            </div>
            <div class="bg-amber-50 border-2 border-amber-300 rounded-lg p-3">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-amber-400">
                            <th class="text-left py-1 px-1 text-xs">Składnik</th>
                            <th class="text-left py-1 px-1 text-xs" style="width:110px;">Waga (kg)</th>
                            <th class="text-left py-1 px-1 text-xs" style="width:90px;">Procent (%)</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="flourTable">
                        @foreach($recipe->steps->where('is_flour', true) as $step)
                        <tr id="flour-{{ $loop->iteration }}">
                            <td class="py-1 px-1">
                                <select name="flour[{{ $loop->iteration }}][ingredient_id]" required class="w-full px-1 py-1 border rounded text-xs flour-select">
                                    <option value="">-- Wybierz mąkę --</option>
                                    @foreach($ingredients as $ing)
                                        <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" {{ $step->ingredient_id == $ing->id ? 'selected' : '' }}>
                                            {{ $ing->name }} ({{ $ing->quantity }} {{ $ing->unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-1 px-1">
                                <input type="number" name="flour[{{ $loop->iteration }}][weight]" step="0.01" min="0.01" required 
                                       value="{{ $step->quantity }}"
                                       oninput="updateFlourFromWeight({{ $loop->iteration }})" 
                                       class="w-20 px-1 py-1 border rounded text-xs flour-weight" id="flour-weight-{{ $loop->iteration }}">
                            </td>
                            <td class="py-1 px-1">
                                <input type="number" name="flour[{{ $loop->iteration }}][percentage]" step="0.01" min="0.01" max="100" required 
                                       value="{{ $step->percentage }}"
                                       oninput="updateFlourFromPercent({{ $loop->iteration }})" 
                                       class="w-16 px-1 py-1 border rounded text-xs flour-percentage" id="flour-percent-{{ $loop->iteration }}">
                            </td>
                            <td class="py-1 px-1 text-center">
                                <button type="button" onclick="removeFlourRow({{ $loop->iteration }})" class="text-red-600 hover:text-red-800">🗑️</button>
                            </td>
                        </tr>
                        @endforeach
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
            <div class="bg-green-50 border-2 border-green-300 rounded-lg p-3">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-green-400">
                            <th class="text-left py-1 px-1 text-xs">Składnik</th>
                            <th class="text-left py-1 px-1 text-xs" style="width:110px;">Ilość / jednostka</th>
                            <th class="text-left py-1 px-1 text-xs" style="width:90px;">Procent (%)</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="ingredientTable">
                        @foreach($recipe->steps->where('is_flour', false)->where('type', 'ingredient') as $step)
                        <tr id="ingredient-{{ $loop->iteration }}">
                            <td class="py-1 px-1">
                                <select name="ingredient[{{ $loop->iteration }}][ingredient_id]" required onchange="updateIngredientUnit({{ $loop->iteration }})" class="w-full px-1 py-1 border rounded text-xs ingredient-select">
                                    <option value="">-- Wybierz składnik --</option>
                                    @foreach($ingredients as $ing)
                                        <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" {{ $step->ingredient_id == $ing->id ? 'selected' : '' }}>
                                            {{ $ing->name }} ({{ $ing->quantity }} {{ $ing->unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-1 px-1 flex items-center gap-1">
                                <input type="number" name="ingredient[{{ $loop->iteration }}][quantity]" step="0.01" min="0.01" required 
                                       value="{{ $step->quantity }}"
                                       oninput="updateIngredientPercentage({{ $loop->iteration }})" 
                                       class="w-20 px-1 py-1 border rounded text-xs ingredient-quantity" id="ingredient-quantity-{{ $loop->iteration }}">
                                <span class="text-xs text-gray-500 ml-1" id="ingredient-unit-{{ $loop->iteration }}">{{ $step->ingredient->unit ?? '' }}</span>
                            </td>
                            <td class="py-1 px-1">
                                <input type="number" name="ingredient[{{ $loop->iteration }}][percentage]" step="0.01" min="0.01" required 
                                       value="{{ $step->percentage }}"
                                       oninput="updateIngredientQuantity({{ $loop->iteration }})" 
                                       class="w-16 px-1 py-1 border rounded text-xs ingredient-percentage" id="ingredient-percent-{{ $loop->iteration }}">
                            </td>
                            <td class="py-1 px-1 text-center">
                                <button type="button" onclick="removeIngredientRow({{ $loop->iteration }})" class="text-red-600 hover:text-red-800">🗑️</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="text-xs text-gray-600 mt-2">💡 Procent odnosi się do całkowitej wagi mąki</p>
            </div>
        </div>
        
        <button type="submit" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold">
            💾 Zaktualizuj Recepturę
        </button>
    </form>
</main>

<script>
let flourCounter = {{ $recipe->steps->where('is_flour', true)->count() }};
let ingredientCounter = {{ $recipe->steps->where('is_flour', false)->where('type', 'ingredient')->count() }};
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
            <td class="py-1 px-1">
                <select name="flour[${flourCounter}][ingredient_id]" required class="w-full px-1 py-1 border rounded text-xs flour-select">
                    ${ingredientOptions}
                </select>
            </td>
            <td class="py-1 px-1">
                <input type="number" name="flour[${flourCounter}][weight]" step="0.01" min="0.01" required 
                       oninput="updateFlourFromWeight(${flourCounter})" 
                       class="w-20 px-1 py-1 border rounded text-xs flour-weight" id="flour-weight-${flourCounter}">
            </td>
            <td class="py-1 px-1">
                <input type="number" name="flour[${flourCounter}][percentage]" step="0.01" min="0.01" max="100" required 
                       oninput="updateFlourFromPercent(${flourCounter})" 
                       class="w-16 px-1 py-1 border rounded text-xs flour-percentage" id="flour-percent-${flourCounter}">
            </td>
            <td class="py-1 px-1 text-center">
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
        // Jeśli suma wag przekracza 0, przelicz procenty tak, by suma = 100%
        const newPercents = weights.map(w => w / total * 100);
        document.querySelectorAll('.flour-percentage').forEach((input, idx) => {
            input.value = newPercents[idx].toFixed(2);
        });
    }
    calculateFlourTotals();
}

function updateFlourFromPercent(id) {
    // Jeśli użytkownik wpisuje procenty, przelicz wagę tej mąki, a jeśli suma > 100%, przeskaluj wszystkie
    const percInputs = Array.from(document.querySelectorAll('.flour-percentage'));
    let percents = percInputs.map(input => parseFloat(input.value) || 0);
    let sum = percents.reduce((a, b) => a + b, 0);
    if (sum > 100.01) {
        // Przeskaluj wszystkie do sumy 100%
        percents = percents.map(p => p / sum * 100);
        percInputs.forEach((input, idx) => {
            input.value = percents[idx].toFixed(2);
        });
        sum = 100;
    }
    // Przelicz wagę dla tej mąki
    const totalWeight = getTotalFlourWeight();
    const percent = parseFloat(document.getElementById(`flour-percent-${id}`).value) || 0;
    const weight = (totalWeight * percent / 100).toFixed(3);
    document.getElementById(`flour-weight-${id}`).value = weight;
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
            <td class="py-1 px-1">
                <select name="ingredient[${ingredientCounter}][ingredient_id]" required onchange="updateIngredientUnit(${ingredientCounter})" class="w-full px-1 py-1 border rounded text-xs ingredient-select">
                    ${ingredientOptions}
                </select>
            </td>
            <td class="py-1 px-1 flex items-center gap-1">
                <input type="number" name="ingredient[${ingredientCounter}][quantity]" step="0.01" min="0.01" required 
                       oninput="updateIngredientPercentage(${ingredientCounter})" 
                       class="w-20 px-1 py-1 border rounded text-xs ingredient-quantity" id="ingredient-quantity-${ingredientCounter}">
                <span class="text-xs text-gray-500 ml-1" id="ingredient-unit-${ingredientCounter}"></span>
            </td>
            <td class="py-1 px-1">
                <input type="number" name="ingredient[${ingredientCounter}][percentage]" step="0.01" min="0.01" required 
                       oninput="updateIngredientQuantity(${ingredientCounter})" 
                       class="w-16 px-1 py-1 border rounded text-xs ingredient-percentage" id="ingredient-percent-${ingredientCounter}">
            </td>
            <td class="py-1 px-1 text-center">
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
document.getElementById('recipeForm').addEventListener('submit', function(e) {
    let totalPercent = 0;
    document.querySelectorAll('.flour-percentage').forEach(input => {
        totalPercent += parseFloat(input.value) || 0;
    });
    
    if (Math.abs(totalPercent - 100) > 0.01) {
        e.preventDefault();
        alert(`Suma procentów mąki musi wynosić 100%!\nAktualna suma: ${totalPercent.toFixed(2)}%`);
        return false;
    }
});

// ===== INICJALIZACJA PO ZAŁADOWANIU STRONY =====
document.addEventListener('DOMContentLoaded', function() {
    // Ustaw jednostki dla istniejących składników
    document.querySelectorAll('[id^="ingredient-"]').forEach(row => {
        const id = row.id.replace('ingredient-', '');
        const select = document.querySelector(`#ingredient-${id} .ingredient-select`);
        if (select && select.value) {
            const unit = select.options[select.selectedIndex]?.dataset?.unit || '';
            const unitSpan = document.getElementById(`ingredient-unit-${id}`);
            if (unitSpan) {
                unitSpan.textContent = unit;
            }
        }
    });
    
    // Oblicz początkowe sumy
    calculateFlourTotals();
});

</script>

</body>
</html>
