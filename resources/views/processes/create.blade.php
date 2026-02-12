<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nowy Proces</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    @include('parts.menu')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">➕ Nowy Proces Produkcyjny</h1>
                <a href="{{ route('processes.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
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

            <form action="{{ route('processes.store') }}" method="POST" id="processForm" class="bg-white rounded-lg shadow-md p-6">
                @csrf

                <div class="mb-3 flex items-center gap-3">
                    <label for="name" class="text-sm font-medium text-gray-700 w-32">Nazwa procesu:</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="np. Produkcja chleba">
                </div>

                <div class="mb-3 flex items-center gap-3">
                    <label for="quantity" class="text-sm font-medium text-gray-700 w-40 whitespace-nowrap">Ilość do wyprod. (szt):</label>
                    <input type="number" name="quantity" id="quantity" required min="1" value="{{ old('quantity', 1) }}"
                           oninput="calculateScale()"
                           class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-3 flex items-center gap-3">
                    <label for="recipe_id" class="text-sm font-medium text-gray-700 w-32">Receptura:</label>
                    <select name="recipe_id" id="recipe_id" required onchange="updateRecipeInfo()"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Wybierz recepturę --</option>
                        @foreach($recipes as $recipe)
                            <option value="{{ $recipe->id }}"
                                    data-output="{{ $recipe->output_quantity }}"
                                    data-name="{{ $recipe->name }}"
                                    {{ old('recipe_id') == $recipe->id ? 'selected' : '' }}>
                                {{ $recipe->name }} ({{ $recipe->output_quantity }} szt.)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6 flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 w-32">Bazowa ilość:</label>
                    <div id="recipeInfo" class="px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg hidden">
                        <span class="text-sm text-blue-800 font-bold"><span id="baseQuantity"></span> szt.</span>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notatki (opcjonalnie)</label>
                    <textarea name="notes" id="notes" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Dodatkowe informacje o procesie...">{{ old('notes') }}</textarea>
                </div>

                <hr class="my-6">

                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-700">Kroki Realizacji</h2>
                    <button type="button" onclick="addStep()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        ➕ Dodaj Krok
                    </button>
                </div>

                <div id="stepsContainer" class="space-y-4 mb-6">
                    <!-- Kroki będą dodawane dynamicznie -->
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                        ✅ Utwórz Proces
                    </button>
                    <a href="{{ route('processes.index') }}" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Anuluj
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let stepCounter = 0;
        let recipeIngredients = [];

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
                        <select name="steps[${stepCounter}][action_name]" required class="w-full px-3 py-2 border rounded" onchange="toggleIngredientsList(${stepCounter}, this.value)">
                            <option value="">-- Wybierz --</option>
                            <option value="Mieszanie">Mieszanie</option>
                            <option value="Podgrzewanie">Podgrzewanie</option>
                            <option value="Chłodzenie">Chłodzenie</option>
                            <option value="Wyczekiwanie">Wyczekiwanie</option>
                            <option value="Dodawanie">Dodawanie</option>
                            <option value="Inne">Inne</option>
                        </select>
                    </div>
                    
                    <div id="ingredients-list-${stepCounter}" class="mb-3 hidden">
                        <label class="block text-sm font-medium mb-2 text-green-700">🥄 Wybierz składniki do dodania:</label>
                        <div class="bg-white border border-green-300 rounded p-3 max-h-60 overflow-y-auto" id="ingredients-checkboxes-${stepCounter}">
                            <p class="text-sm text-gray-500">Najpierw wybierz recepturę...</p>
                        </div>
                    </div>
                    
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

        function toggleIngredientsList(stepId, actionName) {
            const ingredientsList = document.getElementById(`ingredients-list-${stepId}`);
            if (actionName === 'Dodawanie') {
                ingredientsList.classList.remove('hidden');
                loadIngredientsForStep(stepId);
            } else {
                ingredientsList.classList.add('hidden');
            }
        }

        function loadIngredientsForStep(stepId) {
            const checkboxContainer = document.getElementById(`ingredients-checkboxes-${stepId}`);
            
            if (recipeIngredients.length === 0) {
                checkboxContainer.innerHTML = '<p class="text-sm text-gray-500">Brak składników w wybranej recepturze</p>';
                return;
            }
            
            let html = '';
            recipeIngredients.forEach((ingredient, index) => {
                html += `
                    <div class="flex items-center mb-2 p-2 hover:bg-gray-50 rounded">
                        <input type="checkbox" 
                               id="ingredient-${stepId}-${ingredient.id}" 
                               name="steps[${stepId}][ingredients][]" 
                               value="${ingredient.id}"
                               class="mr-2">
                        <label for="ingredient-${stepId}-${ingredient.id}" class="text-sm cursor-pointer flex-1">
                            <span class="font-semibold">${ingredient.name}</span>
                            <span class="text-gray-600 ml-2">${ingredient.quantity} ${ingredient.unit}</span>
                            ${ingredient.is_flour ? '<span class="ml-2 text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded">🌾 Mąka</span>' : ''}
                        </label>
                    </div>
                `;
            });
            
            checkboxContainer.innerHTML = html;
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
                
                // Załaduj składniki dla wybranej receptury
                fetch(`/api/recipes/${selected.value}/ingredients`)
                    .then(response => response.json())
                    .then(data => {
                        recipeIngredients = data;
                    })
                    .catch(error => {
                        console.error('Błąd przy ładowaniu składników:', error);
                        recipeIngredients = [];
                    });
            } else {
                recipeInfo.classList.add('hidden');
                document.getElementById('scaleInfo').classList.add('hidden');
                recipeIngredients = [];
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

        // Initialize on page load if there's old input
        document.addEventListener('DOMContentLoaded', function() {
            const recipeSelect = document.getElementById('recipe_id');
            if (recipeSelect.value) {
                updateRecipeInfo();
            }
        });
    </script>
</body>
</html>
