@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-purple-700 mb-6">🔄 Realizacja procesu: {{ $process->name }}</h1>
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between mb-2">
                <span class="font-medium">Postęp realizacji</span>
                <span class="font-bold" id="stepCounter">Krok <span id="currentStep">1</span> / {{ $process->steps->count() }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div id="progressBar" class="bg-purple-600 h-4 rounded-full transition-all" style="width: 0%"></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <div id="stepBox">
                <!-- Kroki będą ładowane przez JS -->
            </div>
            <div class="mt-8 flex flex-col items-center">
                <label class="block text-lg font-semibold mb-2">Waga (imitacja)</label>
                <div class="flex items-center gap-4">
                    <button id="weightMinus" class="px-4 py-2 bg-gray-300 text-2xl rounded hover:bg-gray-400">-</button>
                    <span id="weightValue" class="text-2xl font-bold w-24 text-center">0.0</span>
                    <button id="weightPlus" class="px-4 py-2 bg-gray-300 text-2xl rounded hover:bg-gray-400">+</button>
                </div>
            </div>
            <div class="mt-8 flex justify-between">
                <button id="prevStep" class="px-6 py-3 bg-gray-400 text-white rounded-lg hover:bg-gray-500" disabled>Poprzedni krok</button>
                <button id="nextStep" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">Następny krok</button>
            </div>
        </div>
        <a href="{{ route('processes.show', $process) }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">← Powrót</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const steps = @json($process->steps->values());
    const flourIngredientsOriginal = @json($scaledIngredients['flour']);
    const otherIngredientsOriginal = @json($scaledIngredients['ingredients']);
    
    // Kopiuj składniki do modyfikowalnych tablic
    let flourIngredients = JSON.parse(JSON.stringify(flourIngredientsOriginal));
    let otherIngredients = JSON.parse(JSON.stringify(otherIngredientsOriginal));
    
    let currentStep = 0;
    let currentIngredientIndex = 0;
    let weight = 0.0;

    function renderStep() {
        const step = steps[currentStep];
        let html = `
            <h2 class="text-xl font-bold text-blue-700 mb-4">Krok ${currentStep + 1}: ${step.action_name}</h2>
            <p class="mb-2 text-gray-700">${step.action_description ?? ''}</p>
            <p class="text-sm text-gray-500 mb-4">Czas trwania: ${step.duration ? step.duration + ' sek.' : '—'}</p>
        `;
        
        // Reset wagi na początku każdego kroku
        weight = 0.0;
        document.getElementById('weightValue').textContent = weight.toFixed(1);
        
        // Wyświetl składniki jeśli są przypisane do kroku
        if (step.ingredients_data && step.ingredients_data.length > 0) {
            currentIngredientIndex = 0;
            html += `<div class="mt-4 bg-green-50 border border-green-300 rounded-lg p-4">`;
            html += `<h3 class="font-semibold text-green-800 mb-3">📦 Składniki do dodania w tym kroku:</h3>`;
            html += `<div id="ingredientsList">`;
            html += renderCurrentIngredient(step);
            html += `</div></div>`;
        } else {
            html += `<p class="text-gray-500 italic">Brak składników do dodania w tym kroku.</p>`;
        }
        
        document.getElementById('stepBox').innerHTML = html;
        document.getElementById('currentStep').textContent = currentStep + 1;
        document.getElementById('progressBar').style.width = `${((currentStep + 1) / steps.length) * 100}%`;
        document.getElementById('prevStep').disabled = currentStep === 0;
        document.getElementById('nextStep').textContent = currentStep === steps.length - 1 ? 'Zakończ' : 'Następny krok';
        
        // Jeśli są składniki, ukryj przycisk następny krok dopóki nie zostaną dodane wszystkie
        if (step.ingredients_data && step.ingredients_data.length > 0) {
            document.getElementById('nextStep').style.display = 'none';
        } else {
            document.getElementById('nextStep').style.display = 'block';
        }
    }
    
    function renderCurrentIngredient(step) {
        if (!step.ingredients_data || currentIngredientIndex >= step.ingredients_data.length) {
            // Wszystkie składniki dodane, pokaż przycisk dalej
            document.getElementById('nextStep').style.display = 'block';
            return `<p class="text-green-700 font-semibold">✅ Wszystkie składniki dodane!</p>`;
        }
        
        const ing = step.ingredients_data[currentIngredientIndex];
        const targetWeight = parseFloat(ing.target_weight || ing.quantity_added);
        const tolerance = 0.01; // Tolerancja 0.01 kg
        const isCorrectWeight = Math.abs(weight - targetWeight) < tolerance;
        const isOverWeight = weight > targetWeight + tolerance;
        
        let html = `
            <div class="mb-4 p-4 bg-white rounded border-2 ${isCorrectWeight ? 'border-green-500' : isOverWeight ? 'border-red-500' : 'border-orange-500'}">
                <p class="font-bold text-lg mb-2">${ing.name}</p>
                <p class="text-2xl font-bold mb-3 ${isCorrectWeight ? 'text-green-600' : isOverWeight ? 'text-red-600' : 'text-orange-600'}">
                    Ustaw wagę: ${targetWeight.toFixed(3)} ${ing.unit}
                </p>
                <p class="text-sm text-gray-600 mb-2">Aktualna waga na wadze: <span class="font-bold">${weight.toFixed(3)}</span> ${ing.unit}</p>
        `;
        
        if (isCorrectWeight) {
            html += `
                <p class="text-green-600 font-semibold mb-3">✅ Waga poprawna!</p>
                <button onclick="nextIngredient()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    ${currentIngredientIndex < step.ingredients_data.length - 1 ? 'Następny składnik →' : 'Zakończ dodawanie ✓'}
                </button>
            `;
        } else if (isOverWeight) {
            html += `
                <p class="text-red-600 font-semibold mb-3">⚠️ Waga przekroczona!</p>
                <button onclick="nextIngredient()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    Akceptuję i zakończ krok
                </button>
            `;
        } else {
            const diff = targetWeight - weight;
            html += `<p class="text-orange-600 font-semibold">Dostosuj wagę: ${diff > 0 ? '+' : ''}${diff.toFixed(3)} ${ing.unit}</p>`;
        }
        
        html += `</div>`;
        return html;
    }
    
    window.nextIngredient = function() {
        currentIngredientIndex++;
        weight = 0.0;
        document.getElementById('weightValue').textContent = weight.toFixed(1);
        
        const step = steps[currentStep];
        document.getElementById('ingredientsList').innerHTML = renderCurrentIngredient(step);
    }

    document.getElementById('weightMinus').onclick = function() {
        weight = Math.max(0, weight - 0.5);
        document.getElementById('weightValue').textContent = weight.toFixed(1);
        const step = steps[currentStep];
        if (step.ingredients_data && step.ingredients_data.length > 0 && currentIngredientIndex < step.ingredients_data.length) {
            document.getElementById('ingredientsList').innerHTML = renderCurrentIngredient(step);
        }
    };
    document.getElementById('weightPlus').onclick = function() {
        weight += 0.5;
        document.getElementById('weightValue').textContent = weight.toFixed(1);
        const step = steps[currentStep];
        if (step.ingredients_data && step.ingredients_data.length > 0 && currentIngredientIndex < step.ingredients_data.length) {
            document.getElementById('ingredientsList').innerHTML = renderCurrentIngredient(step);
        }
    };

    // Przytrzymanie przycisków -/+ dla szybszego zliczania
    let weightInterval = null;
    let weightSpeed = 300; // ms
    let weightStep = 0.5;
    function startWeightChange(delta) {
        if (weightInterval) return;
        weightInterval = setInterval(() => {
            weight = Math.max(0, weight + delta);
            document.getElementById('weightValue').textContent = weight.toFixed(1);
            const step = steps[currentStep];
            if (step.ingredients_data && step.ingredients_data.length > 0 && currentIngredientIndex < step.ingredients_data.length) {
                document.getElementById('ingredientsList').innerHTML = renderCurrentIngredient(step);
            }
            // Po 1s przyspiesz
            if (weightSpeed > 50) weightSpeed -= 30;
            clearInterval(weightInterval);
            weightInterval = setInterval(() => startWeightChange(delta), weightSpeed);
        }, weightSpeed);
    }
    function stopWeightChange() {
        clearInterval(weightInterval);
        weightInterval = null;
        weightSpeed = 300;
    }
    document.getElementById('weightPlus').addEventListener('mousedown', function() { startWeightChange(weightStep); });
    document.getElementById('weightPlus').addEventListener('mouseup', stopWeightChange);
    document.getElementById('weightPlus').addEventListener('mouseleave', stopWeightChange);
    document.getElementById('weightMinus').addEventListener('mousedown', function() { startWeightChange(-weightStep); });
    document.getElementById('weightMinus').addEventListener('mouseup', stopWeightChange);
    document.getElementById('weightMinus').addEventListener('mouseleave', stopWeightChange);
    document.getElementById('prevStep').onclick = function() {
        if (currentStep > 0) {
            currentStep--;
            renderStep();
        }
    };
    document.getElementById('nextStep').onclick = function() {
        if (currentStep < steps.length - 1) {
            currentStep++;
            renderStep();
        } else {
            alert('Proces zakończony!');
            window.location.href = "{{ route('processes.show', $process) }}";
        }
    };

    renderStep();
</script>
@endpush
