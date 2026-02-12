<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Proces</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    @include('parts.menu')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">✏️ Edytuj Proces</h1>
                <a href="{{ route('processes.show', $process) }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    ← Powrót
                </a>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('processes.update', $process) }}" method="POST" id="processForm" class="bg-white rounded-lg shadow-md p-6">
                @csrf
                @method('PUT')

                <div class="mb-3 flex items-center gap-3">
                          <label for="name" class="text-sm font-medium text-gray-700 w-40">Nazwa procesu:</label>
                          <input type="text" name="name" id="name" required value="{{ old('name', $process->name) }}"
                              class="md:w-1/2 px-3 py-2 border-2 border-green-500 bg-green-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="np. Produkcja chleba">
                </div>

                <div class="mb-3 flex items-center gap-3">
                          <label for="quantity" class="text-sm font-medium text-gray-700 w-40 whitespace-nowrap">Ilość do wyprod. (szt):</label>
                          <input type="number" name="quantity" id="quantity" required min="1" value="{{ old('quantity', $process->quantity) }}"
                              oninput="calculateScale()"
                              class="md:w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-3 flex items-center gap-3">
                        <label for="recipe_id" class="text-sm font-medium text-gray-700 w-40">Receptura:</label>
                        <select name="recipe_id" id="recipe_id" required onchange="updateRecipeInfo()"
                            class="md:w-1/2 px-3 py-2 border-2 border-blue-500 bg-blue-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Wybierz recepturę --</option>
                        @foreach($recipes as $recipe)
                            <option value="{{ $recipe->id }}"
                                    data-output="{{ $recipe->output_quantity }}"
                                    data-name="{{ $recipe->name }}"
                                    {{ old('recipe_id', $process->recipe_id) == $recipe->id ? 'selected' : '' }}>
                                {{ $recipe->name }} ({{ $recipe->output_quantity }} szt.)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6 flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 w-40">Bazowa ilość:</label>
                    <div id="recipeInfo" class="md:w-1/2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                        <span class="text-sm text-blue-800 font-bold"><span id="baseQuantity">{{ $process->recipe->output_quantity }}</span> szt.</span>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notatki (opcjonalnie)</label>
                    <textarea name="notes" id="notes" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Dodatkowe informacje o procesie...">{{ old('notes', $process->notes) }}</textarea>
                </div>

                <hr class="my-6">

                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-700">Kroki Realizacji</h2>
                    <button type="button" onclick="addStep()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        ➕ Dodaj Krok
                    </button>
                </div>

                <div id="stepsContainer" class="space-y-4 mb-6">
                    @foreach($process->steps as $index => $step)
                        <div class="border border-blue-300 rounded-lg p-4 bg-blue-50" id="step-{{ $index + 1 }}">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-bold text-blue-800">🔧 Krok #{{ $index + 1 }}</h3>
                                <button type="button" onclick="removeStep({{ $index + 1 }})" class="text-red-600 hover:text-red-800">🗑️ Usuń</button>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium mb-1">Nazwa czynności *</label>
                                <select name="steps[{{ $index + 1 }}][action_name]" required class="w-full px-3 py-2 border rounded" onchange="toggleIngredientsTable({{ $index + 1 }}, this.value)">
                                    <option value="">-- Wybierz --</option>
                                    <option value="Mieszanie" {{ $step->action_name === 'Mieszanie' ? 'selected' : '' }}>Mieszanie</option>
                                    <option value="Podgrzewanie" {{ $step->action_name === 'Podgrzewanie' ? 'selected' : '' }}>Podgrzewanie</option>
                                    <option value="Chłodzenie" {{ $step->action_name === 'Chłodzenie' ? 'selected' : '' }}>Chłodzenie</option>
                                    <option value="Wyczekiwanie" {{ $step->action_name === 'Wyczekiwanie' ? 'selected' : '' }}>Wyczekiwanie</option>
                                    <option value="Dodawanie" {{ $step->action_name === 'Dodawanie' ? 'selected' : '' }}>Dodawanie</option>
                                    <option value="Inne" {{ $step->action_name === 'Inne' ? 'selected' : '' }}>Inne</option>
                                </select>
                            </div>
                            <div id="ingredients-table-{{ $index + 1 }}" class="mb-3 {{ $step->action_name === 'Dodawanie' ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium mb-2 text-green-700">🥄 Składniki do dodania:</label>
                                <div class="bg-white border border-green-300 rounded overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-green-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Składnik</th>
                                                <th class="px-3 py-2 text-right">Potrzebne</th>
                                                <th class="px-3 py-2 text-right">Zostało</th>
                                                <th class="px-3 py-2 text-right">Dodaję</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ingredients-tbody-{{ $index + 1 }}">
                                            @if($step->ingredients_data)
                                                @foreach($step->ingredients_data as $ingredient)
                                                    <tr class="border-t hover:bg-gray-50">
                                                        <td class="px-3 py-2">
                                                            <span class="font-semibold">{{ $ingredient['name'] }}</span>
                                                        </td>
                                                        <td class="px-3 py-2 text-right text-gray-600">{{ $ingredient['quantity_added'] }} {{ $ingredient['unit'] }}</td>
                                                        <td class="px-3 py-2 text-right font-semibold text-green-600">{{ $ingredient['quantity_added'] }} {{ $ingredient['unit'] }}</td>
                                                        <td class="px-3 py-2 text-right flex gap-2 items-center">
                                                            <input type="number"
                                                                   class="w-24 px-2 py-1 border rounded text-right ingredient-input"
                                                                   name="steps[{{ $index + 1 }}][ingredients_data][{{ $loop->index }}][quantity_added]"
                                                                   value="{{ $ingredient['quantity_added'] }}"
                                                                   min="0"
                                                                   step="0.001">
                                                            <input type="hidden" name="steps[{{ $index + 1 }}][ingredients_data][{{ $loop->index }}][ingredient_id]" value="{{ $ingredient['ingredient_id'] }}">
                                                            <input type="hidden" name="steps[{{ $index + 1 }}][ingredients_data][{{ $loop->index }}][name]" value="{{ $ingredient['name'] }}">
                                                            <input type="hidden" name="steps[{{ $index + 1 }}][ingredients_data][{{ $loop->index }}][unit]" value="{{ $ingredient['unit'] }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium mb-1">Opis czynności</label>
                                <textarea name="steps[{{ $index + 1 }}][action_description]" rows="2" class="w-full px-3 py-2 border rounded">{{ $step->action_description }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium mb-1">Czas trwania (sekundy)</label>
                                <input type="number" name="steps[{{ $index + 1 }}][duration]" value="{{ $step->duration }}" min="0" class="w-full px-3 py-2 border rounded">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        💾 Zaktualizuj Proces
                    </button>
                    <a href="{{ route('processes.show', $process) }}" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Anuluj
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let stepCounter = {{ $process->steps->count() }};
        let scaledIngredients = @json($scaledIngredients);

        function addStep() {
            stepCounter++;
            const html = `
                <div class="border border-blue-300 rounded-lg p-4 bg-blue-50" id="step-${stepCounter}">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-blue-800">🔧 Krok #${stepCounter}</h3>
                        <button type="button" onclick="removeStep(${stepCounter})" class="text-red-600 hover:text-red-800">🗑️ Usuń</button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Nazwa czynności *</label>
                        <select name="steps[${stepCounter}][action_name]" required class="w-full px-3 py-2 border rounded" onchange="toggleIngredientsTable(${stepCounter}, this.value)">
                            <option value="">-- Wybierz --</option>
                            <option value="Mieszanie">Mieszanie</option>
                            <option value="Podgrzewanie">Podgrzewanie</option>
                            <option value="Chłodzenie">Chłodzenie</option>
                            <option value="Wyczekiwanie">Wyczekiwanie</option>
                            <option value="Dodawanie">Dodawanie</option>
                            <option value="Inne">Inne</option>
                        </select>
                    </div>
                    
                    <div id="ingredients-table-${stepCounter}" class="mb-3 hidden">
                        <label class="block text-sm font-medium mb-2 text-green-700">🥄 Składniki do dodania:</label>
                        <div class="bg-white border border-green-300 rounded overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-green-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Składnik</th>
                                        <th class="px-3 py-2 text-right">Potrzebne</th>
                                        <th class="px-3 py-2 text-right">Zostało</th>
                                        <th class="px-3 py-2 text-right">Dodaję</th>
                                        <th class="px-3 py-2 text-center">Akcja</th>
                                    </tr>
                                </thead>
                                <tbody id="ingredients-tbody-${stepCounter}">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <input type="hidden" name="steps[${stepCounter}][ingredients_data]" id="ingredients-data-${stepCounter}" value="">
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Opis czynności</label>
                        <textarea name="steps[${stepCounter}][action_description]" rows="2" class="w-full px-3 py-2 border rounded"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Czas trwania (sekundy)</label>
                        <input type="number" name="steps[${stepCounter}][duration]" min="0" placeholder="np. 120" class="w-full px-3 py-2 border rounded">
                    </div>
                </div>
            `;
            document.getElementById('stepsContainer').insertAdjacentHTML('beforeend', html);
        }

        function removeStep(id) {
            document.getElementById(`step-${id}`).remove();
        }

        function addAllIngredient(inputId, remaining, stepId) {
            const input = document.getElementById(inputId);
            if (input) {
                input.value = remaining.toFixed(3);
                updateIngredientsData(stepId);
            }
        }

        function toggleIngredientsTable(stepId, actionName) {
            const table = document.getElementById(`ingredients-table-${stepId}`);
            if (actionName === 'Dodawanie') {
                table.classList.remove('hidden');
                loadIngredientsTable(stepId);
            } else {
                table.classList.add('hidden');
            }
        }

        function loadIngredientsTable(stepId) {
            const tbody = document.getElementById(`ingredients-tbody-${stepId}`);
            const allIngredients = [...scaledIngredients.flour, ...scaledIngredients.ingredients];
            
            if (allIngredients.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-3 py-2 text-gray-500 text-center">Brak składników</td></tr>';
                return;
            }
            
            let html = '';
            allIngredients.forEach((ingredient, index) => {
                if (ingredient.remaining > 0) {
                    const inputId = `ingredient-input-${stepId}-${index}`;
                    html += `
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <span class="font-semibold">${ingredient.name}</span>
                                ${ingredient.is_flour ? '<span class="ml-2 text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded">🌾</span>' : ''}
                            </td>
                            <td class="px-3 py-2 text-right text-gray-600">${ingredient.scaled_weight || ingredient.scaled_quantity} ${ingredient.unit}</td>
                            <td class="px-3 py-2 text-right font-semibold text-green-600">${ingredient.remaining.toFixed(3)} ${ingredient.unit}</td>
                            <td class="px-3 py-2 text-right">
                                <div class="flex gap-2 items-center justify-end">
                                    <input type="number" 
                                           id="${inputId}"
                                           class="w-24 px-2 py-1 border rounded text-right ingredient-input" 
                                           data-ingredient-id="${ingredient.ingredient_id}"
                                           data-name="${ingredient.name}"
                                           data-unit="${ingredient.unit}"
                                           data-remaining="${ingredient.remaining}"
                                           data-step-id="${stepId}"
                                           data-index="${index}"
                                           min="0" 
                                           max="${ingredient.remaining}"
                                           step="0.001"
                                           placeholder="0"
                                           onchange="updateIngredientsData(${stepId})">
                                    <button type="button" class="px-2 py-1 bg-blue-500 text-white rounded text-xs whitespace-nowrap" onclick="addAllIngredient('${inputId}', ${ingredient.remaining}, ${stepId})">Dodaj wszystko</button>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" class="px-3 py-1 bg-orange-500 text-white rounded text-xs whitespace-nowrap" onclick="subtractIngredient('${inputId}', ${stepId}, ${index})">🔄 Przelicz</button>
                            </td>
                        </tr>
                    `;
                }
            });

            if (html === '') {
                tbody.innerHTML = '<tr><td colspan="5" class="px-3 py-2 text-gray-500 text-center">Wszystkie składniki zostały już dodane</td></tr>';
            } else {
                tbody.innerHTML = html;
            }
        }

        function subtractIngredient(inputId, stepId, ingredientIndex) {
            const input = document.getElementById(inputId);
            const addedAmount = parseFloat(input.value) || 0;
            
            if (addedAmount <= 0) {
                alert('Wprowadź ilość składnika w polu "Dodaję" przed przeliczeniem!');
                return;
            }
            
            // Znajdź składnik w globalnej tablicy
            const allIngredients = [...scaledIngredients.flour, ...scaledIngredients.ingredients];
            const ingredient = allIngredients[ingredientIndex];
            
            if (!ingredient) {
                alert('Nie znaleziono składnika!');
                return;
            }
            
            // Odejmij od remaining
            const newRemaining = Math.max(0, ingredient.remaining - addedAmount);
            ingredient.remaining = newRemaining;
            ingredient.used = (ingredient.used || 0) + addedAmount;
            
            // Zapisz dane składników
            updateIngredientsData(stepId);
            
            // Przeładuj tabelę
            loadIngredientsTable(stepId);
            
            alert(`Dodano ${addedAmount.toFixed(3)} ${ingredient.unit} ${ingredient.name}. Pozostało: ${newRemaining.toFixed(3)} ${ingredient.unit}`);
        }

        function updateIngredientsData(stepId) {
            const inputs = document.querySelectorAll(`#ingredients-tbody-${stepId} .ingredient-input`);
            const ingredientsData = [];
            
            inputs.forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                if (quantity > 0) {
                    ingredientsData.push({
                        ingredient_id: parseInt(input.dataset.ingredientId),
                        name: input.dataset.name,
                        quantity_added: quantity,
                        target_weight: quantity,
                        unit: input.dataset.unit,
                    });
                }
            });
            
            document.getElementById(`ingredients-data-${stepId}`).value = JSON.stringify(ingredientsData);
        }

        function updateRecipeInfo() {
            const select = document.getElementById('recipe_id');
            const selected = select.options[select.selectedIndex];
            const recipeInfo = document.getElementById('recipeInfo');
            const baseQuantity = document.getElementById('baseQuantity');
            
            if (selected.value) {
                const output = selected.dataset.output;
                baseQuantity.textContent = output;
                recipeInfo.classList.remove('hidden');
                calculateScale();
            } else {
                recipeInfo.classList.add('hidden');
                document.getElementById('scaleInfo').classList.add('hidden');
            }
        }

        function calculateScale() {
            const select = document.getElementById('recipe_id');
            const selected = select.options[select.selectedIndex];
            const quantity = document.getElementById('quantity').value;
            const scaleInfo = document.getElementById('scaleInfo');
            const scaleFactor = document.getElementById('scaleFactor');
            
            if (selected.value && quantity) {
                const baseOutput = parseFloat(selected.dataset.output);
                const factor = (quantity / baseOutput).toFixed(2);
                scaleFactor.textContent = factor;
                scaleInfo.classList.remove('hidden');
            } else {
                scaleInfo.classList.add('hidden');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateScale();
        });
    </script>
</body>
</html>
