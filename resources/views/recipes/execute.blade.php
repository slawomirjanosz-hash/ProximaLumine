<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Realizacja Receptury - {{ $execution->recipe->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @include('parts.menu')

<main class="max-w-4xl mx-auto mt-8 px-6 pb-12">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <!-- Pasek postępu -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between mb-2">
            <span class="font-medium">Postęp realizacji</span>
            <span class="font-bold" id="progressText">Krok {{ $execution->current_step }} / {{ $execution->recipe->total_steps }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div id="progressBar" class="bg-purple-600 h-4 rounded-full transition-all" style="width: {{ ($execution->current_step - 1) / $execution->recipe->total_steps * 100 }}%"></div>
        </div>
    </div>

    <!-- Kontener kroku -->
    <div id="stepContainer" class="bg-white rounded-lg shadow-lg p-8">
        <!-- Tutaj będzie wyświetlany aktualny krok -->
    </div>

    <!-- Modal zakończenia -->
    <div id="completionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-3xl font-bold mb-4 text-green-600">Receptura Zakończona!</h2>
            <p class="text-gray-600 mb-6">Gratulacje! Pomyślnie ukończyłeś wszystkie kroki receptury.</p>
            <a href="{{ route('recipes.index') }}" class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Powrót do listy receptur
            </a>
        </div>
    </div>
</main>

<!-- Audio element for timer alarm -->
<audio id="timerAlarm" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjGH0fPTgjMGHmSy6+mjUBELTqXh8rJmIAc3k9nw0IQ1BRxisfDnpmMhC0qj4O+vZyQGNJHW8dKINQYabr3u6KZQLww=" type="audio/wav">
</audio>

<script>
const executionId = {{ $execution->id }};
let currentStepNumber = {{ $execution->current_step }};
const totalSteps = {{ $execution->recipe->total_steps }};
const steps = @json($execution->recipe->steps);
let timerInterval = null;

// Inicjalizacja
document.addEventListener('DOMContentLoaded', function() {
    displayCurrentStep();
});

function displayCurrentStep() {
    if (currentStepNumber > totalSteps) {
        showCompletionModal();
        return;
    }

    const step = steps[currentStepNumber - 1];
    const container = document.getElementById('stepContainer');
    
    let html = `<div class="text-center mb-6">
        <h2 class="text-3xl font-bold mb-2">Krok ${currentStepNumber} / ${totalSteps}</h2>
    </div>`;
    
    if (step.type === 'action') {
        html += `
            <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-6 mb-6">
                <div class="text-5xl mb-4 text-center">🔧</div>
                <h3 class="text-2xl font-bold text-blue-800 mb-3 text-center">${step.action_name}</h3>
                ${step.action_description ? `<p class="text-gray-700 mb-4 text-lg">${step.action_description}</p>` : ''}
                
                ${step.duration ? `
                    <div class="bg-white rounded-lg p-6 mb-4 text-center">
                        <div class="text-sm text-gray-600 mb-2">Czas do odczekania:</div>
                        <div id="timer" class="text-6xl font-bold text-blue-600 mb-4"></div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="timerBar" class="bg-blue-600 h-2 rounded-full transition-all" style="width: 100%"></div>
                        </div>
                    </div>
                    <button id="nextButton" class="hidden w-full px-8 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xl font-bold" onclick="confirmStep()">
                        ✅ Przejdź do następnego kroku
                    </button>
                ` : `
                    <button class="w-full px-8 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xl font-bold" onclick="confirmStep()">
                        ✅ Zakończ i przejdź dalej
                    </button>
                `}
            </div>
        `;
        
        container.innerHTML = html;
        
        if (step.duration) {
            startTimer(step.duration);
        }
    } else if (step.type === 'ingredient') {
        const ingredient = step.ingredient;
        html += `
            <div class="bg-green-50 border-2 border-green-300 rounded-lg p-6 mb-6">
                <div class="text-5xl mb-4 text-center">📦</div>
                <h3 class="text-2xl font-bold text-green-800 mb-3 text-center">Dodaj składnik</h3>
                <div class="bg-white rounded-lg p-6 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xl font-bold">${ingredient.name}</div>
                            <div class="text-gray-600">${ingredient.description || ''}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-green-600">${step.quantity}</div>
                            <div class="text-gray-600">${ingredient.unit}</div>
                        </div>
                    </div>
                </div>
                <button class="w-full px-8 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xl font-bold" onclick="confirmStep()">
                    ✅ Składnik dodany - przejdź dalej
                </button>
            </div>
        `;
        
        container.innerHTML = html;
    }
}

function startTimer(duration) {
    let remainingTime = duration;
    const totalDuration = duration;
    const timerDisplay = document.getElementById('timer');
    const timerBar = document.getElementById('timerBar');
    const nextButton = document.getElementById('nextButton');
    
    function updateTimer() {
        const minutes = Math.floor(remainingTime / 60);
        const seconds = remainingTime % 60;
        timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        const percentage = (remainingTime / totalDuration) * 100;
        timerBar.style.width = percentage + '%';
        
        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            timerDisplay.textContent = '00:00';
            timerDisplay.classList.add('text-green-600');
            playAlarm();
            nextButton.classList.remove('hidden');
        }
        
        remainingTime--;
    }
    
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
}

function playAlarm() {
    const audio = document.getElementById('timerAlarm');
    audio.play().catch(e => console.log('Nie można odtworzyć dźwięku:', e));
    
    // Powtórz dźwięk 3 razy
    let count = 0;
    const alarmInterval = setInterval(() => {
        count++;
        if (count < 3) {
            audio.play().catch(e => console.log('Nie można odtworzyć dźwięku:', e));
        } else {
            clearInterval(alarmInterval);
        }
    }, 1000);
}

function confirmStep() {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
    
    // Wyślij potwierdzenie do serwera
    fetch(`/receptury/realizacja/${executionId}/potwierdz-krok`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            step_number: currentStepNumber
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'completed') {
            showCompletionModal();
        } else {
            currentStepNumber++;
            updateProgress();
            displayCurrentStep();
        }
    })
    .catch(error => {
        console.error('Błąd:', error);
        alert('Wystąpił błąd podczas potwierdzania kroku');
    });
}

function updateProgress() {
    const percentage = ((currentStepNumber - 1) / totalSteps) * 100;
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressText').textContent = `Krok ${currentStepNumber} / ${totalSteps}`;
}

function showCompletionModal() {
    document.getElementById('completionModal').classList.remove('hidden');
    playAlarm();
}
</script>

</body>
</html>
