<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj Recepturę</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('recipes.index') }}" class="text-2xl font-bold text-blue-600">← Edytuj Recepturę</a>
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

<main class="max-w-4xl mx-auto mt-8 px-6 pb-12">
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <h1 class="text-3xl font-bold mb-6">Edytuj Recepturę: {{ $recipe->name }}</h1>

    <form method="POST" action="{{ route('recipes.update', $recipe) }}" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')
        
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Nazwa receptury *</label>
            <input type="text" name="name" value="{{ $recipe->name }}" required class="w-full px-4 py-2 border rounded">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Opis</label>
            <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded">{{ $recipe->description }}</textarea>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Ilość sztuk z receptury *</label>
            <input type="number" name="output_quantity" min="1" value="{{ $recipe->output_quantity }}" required class="w-full px-4 py-2 border rounded">
            <small class="text-gray-500">Ile sztuk produktu finalnego wychodzi z tej receptury</small>
        </div>
        
        <hr class="my-6">
        
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Kroki Receptury</h2>
            <div class="flex gap-2">
                <button type="button" onclick="addActionStep()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    ➕ Dodaj Czynność
                </button>
                <button type="button" onclick="addIngredientStep()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    ➕ Dodaj Składnik
                </button>
            </div>
        </div>
        
        <div id="stepsContainer" class="space-y-4 mb-6">
            @foreach($recipe->steps as $index => $step)
                @if($step->type === 'action')
                    <div class="border border-blue-300 rounded-lg p-4 bg-blue-50" id="step-{{ $index + 1 }}">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-blue-800">🔧 Czynność #{{ $index + 1 }}</h3>
                            <button type="button" onclick="removeStep({{ $index + 1 }})" class="text-red-600 hover:text-red-800">🗑️ Usuń</button>
                        </div>
                        
                        <input type="hidden" name="steps[{{ $index + 1 }}][type]" value="action">
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Nazwa czynności *</label>
                            <select name="steps[{{ $index + 1 }}][action_name]" required class="w-full px-3 py-2 border rounded">
                                <option value="">-- Wybierz --</option>
                                <option value="Mieszanie" {{ $step->action_name === 'Mieszanie' ? 'selected' : '' }}>Mieszanie</option>
                                <option value="Podgrzewanie" {{ $step->action_name === 'Podgrzewanie' ? 'selected' : '' }}>Podgrzewanie</option>
                                <option value="Chłodzenie" {{ $step->action_name === 'Chłodzenie' ? 'selected' : '' }}>Chłodzenie</option>
                                <option value="Wyczekiwanie" {{ $step->action_name === 'Wyczekiwanie' ? 'selected' : '' }}>Wyczekiwanie</option>
                                <option value="Dodawanie" {{ $step->action_name === 'Dodawanie' ? 'selected' : '' }}>Dodawanie</option>
                                <option value="Inne" {{ $step->action_name === 'Inne' ? 'selected' : '' }}>Inne</option>
                            </select>
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
                @else
                    <div class="border border-green-300 rounded-lg p-4 bg-green-50" id="step-{{ $index + 1 }}">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-green-800">📦 Składnik #{{ $index + 1 }}</h3>
                            <button type="button" onclick="removeStep({{ $index + 1 }})" class="text-red-600 hover:text-red-800">🗑️ Usuń</button>
                        </div>
                        
                        <input type="hidden" name="steps[{{ $index + 1 }}][type]" value="ingredient">
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Składnik *</label>
                            <select name="steps[{{ $index + 1 }}][ingredient_id]" required class="w-full px-3 py-2 border rounded">
                                <option value="">-- Wybierz składnik --</option>
                                @foreach($ingredients as $ing)
                                    <option value="{{ $ing->id }}" {{ $step->ingredient_id == $ing->id ? 'selected' : '' }}>
                                        {{ $ing->name }} (dostępne: {{ $ing->quantity }} {{ $ing->unit }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Ilość *</label>
                            <input type="number" name="steps[{{ $index + 1 }}][quantity]" value="{{ $step->quantity }}" step="0.01" min="0.01" required class="w-full px-3 py-2 border rounded">
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        
        <button type="submit" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold">
            💾 Zaktualizuj Recepturę
        </button>
    </form>
</main>

<script>
let stepCounter = {{ count($recipe->steps) }};
const ingredients = @json($ingredients);

function addActionStep() {
    stepCounter++;
    const html = `
        <div class="border border-blue-300 rounded-lg p-4 bg-blue-50" id="step-${stepCounter}">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-blue-800">🔧 Czynność #${stepCounter}</h3>
                <button type="button" onclick="removeStep(${stepCounter})" class="text-red-600 hover:text-red-800">🗑️ Usuń</button>
            </div>
            
            <input type="hidden" name="steps[${stepCounter}][type]" value="action">
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Nazwa czynności *</label>
                <select name="steps[${stepCounter}][action_name]" required class="w-full px-3 py-2 border rounded">
                    <option value="">-- Wybierz --</option>
                    <option value="Mieszanie">Mieszanie</option>
                    <option value="Podgrzewanie">Podgrzewanie</option>
                    <option value="Chłodzenie">Chłodzenie</option>
                    <option value="Wyczekiwanie">Wyczekiwanie</option>
                    <option value="Dodawanie">Dodawanie</option>
                    <option value="Inne">Inne</option>
                </select>
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

function addIngredientStep() {
    stepCounter++;
    let ingredientOptions = '<option value="">-- Wybierz składnik --</option>';
    ingredients.forEach(ing => {
        ingredientOptions += `<option value="${ing.id}">${ing.name} (dostępne: ${ing.quantity} ${ing.unit})</option>`;
    });
    
    const html = `
        <div class="border border-green-300 rounded-lg p-4 bg-green-50" id="step-${stepCounter}">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-green-800">📦 Składnik #${stepCounter}</h3>
                <button type="button" onclick="removeStep(${stepCounter})" class="text-red-600 hover:text-red-800">🗑️ Usuń</button>
            </div>
            
            <input type="hidden" name="steps[${stepCounter}][type]" value="ingredient">
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Składnik *</label>
                <select name="steps[${stepCounter}][ingredient_id]" required class="w-full px-3 py-2 border rounded">
                    ${ingredientOptions}
                </select>
            </div>
            
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Ilość *</label>
                <input type="number" name="steps[${stepCounter}][quantity]" step="0.01" min="0.01" required class="w-full px-3 py-2 border rounded">
            </div>
        </div>
    `;
    document.getElementById('stepsContainer').insertAdjacentHTML('beforeend', html);
}

function removeStep(id) {
    document.getElementById(`step-${id}`).remove();
}
</script>

</body>
</html>
