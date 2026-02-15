<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="session-id" content="{{ session()->getId() }}">
    <meta name="app-env" content="{{ app()->environment() }}">
    <meta name="session-driver" content="{{ config('session.driver') }}">
    <title>Autoryzacja produktów - {{ $project->project_number }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">

@include('parts.menu')

<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow mt-6">
    <div class="mb-6">
        <a href="{{ route('magazyn.projects.show', $project->id) }}" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">← Powrót do projektu</a>
        <h2 class="text-2xl font-bold">🔍 Autoryzacja produktów przez skanowanie</h2>
        <div class="mt-2 text-sm text-gray-600">
            <p><strong>Projekt:</strong> {{ $project->project_number }} - {{ $project->name }}</p>
        </div>
    </div>

    {{-- INSTRUKCJA --}}
    <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
        <h3 class="font-semibold text-blue-900 mb-2">📋 Instrukcja:</h3>
        <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
            <li>Kliknij przycisk "Zacznij autoryzację" poniżej</li>
            <li>Zeskanuj kod QR produktu (system automatycznie rozpozna kod)</li>
            <li>Prawidłowy kod zostanie automatycznie zautoryzowany</li>
            <li>Błędny kod wyświetli ostrzeżenie - potwierdź aby skanować dalej</li>
            <li>Każde skanowanie autoryzuje 1 sztukę produktu</li>
        </ol>
    </div>

    {{-- PRZYCISK START --}}
    <div class="mb-6 text-center">
        <button 
            id="start-scan-btn"
            class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white text-xl font-bold rounded-lg shadow-lg transition-all"
        >
            🔍 Zacznij autoryzację
        </button>
        <button 
            id="stop-scan-btn"
            class="px-8 py-4 bg-red-600 hover:bg-red-700 text-white text-xl font-bold rounded-lg shadow-lg transition-all hidden"
        >
            ⏸️ Zatrzymaj skanowanie
        </button>
    </div>

    {{-- POLE DO SKANOWANIA (ukryte, tylko do przechwytywania) --}}
    <input 
        type="text" 
        id="qr-input" 
        class="absolute opacity-0 pointer-events-none"
        style="left: -9999px;"
    >
    
    {{-- STATUS SKANOWANIA --}}
    <div id="scan-status" class="mb-6 p-4 rounded hidden">
        <p class="text-center text-lg font-semibold">
            <span class="animate-pulse">🔍</span> Oczekiwanie na skan kodu QR...
        </p>
    </div>

    {{-- KOMUNIKATY --}}
    <div id="message-container" class="mb-6 hidden">
        <div id="message" class="p-4 rounded text-center text-xl font-bold"></div>
    </div>
    
    {{-- MODAL BŁĘDNEGO KODU --}}
    <div id="error-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4 text-center">
            <div class="text-6xl mb-4">❌</div>
            <h3 class="text-2xl font-bold text-red-600 mb-4">BŁĘDNY KOD!</h3>
            <p class="text-lg mb-2" id="error-code-display"></p>
            <p class="text-gray-600 mb-6">Ten kod nie należy do żadnego produktu w tym projekcie</p>
            <button 
                id="error-confirm-btn"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg"
            >
                OK - Kontynuuj skanowanie
            </button>
        </div>
    </div>

    {{-- STATYSTYKI --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-red-50 border border-red-200 rounded p-4 text-center">
            <p class="text-3xl font-bold text-red-600" id="stat-unauthorized">{{ $removals->sum('quantity') }}</p>
            <p class="text-sm text-gray-600">Oczekuje na autoryzację</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded p-4 text-center">
            <p class="text-3xl font-bold text-green-600" id="stat-authorized">0</p>
            <p class="text-sm text-gray-600">Zautoryzowano w tej sesji</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded p-4 text-center">
            <p class="text-3xl font-bold text-blue-600" id="stat-scans">0</p>
            <p class="text-sm text-gray-600">Liczba skanów</p>
        </div>
    </div>

    {{-- LISTA PRODUKTÓW DO AUTORYZACJI --}}
    <h3 class="text-lg font-semibold mb-4">Produkty oczekujące na autoryzację:</h3>
    <div class="overflow-auto max-h-96">
        <table class="w-full border border-collapse text-sm">
            <thead class="bg-gray-100 sticky top-0">
                <tr>
                    <th class="border p-2">Nazwa produktu</th>
                    <th class="border p-2 text-center">Kod QR</th>
                    <th class="border p-2 text-center bg-red-50">Pobranie nieautoryzowane</th>
                    <th class="border p-2 text-center bg-green-50">Pobranie autoryzowane</th>
                </tr>
            </thead>
            <tbody id="products-table">
                @php
                    $groupedProducts = [];
                    foreach($removals as $removal) {
                        $partId = $removal->part_id;
                        if (!isset($groupedProducts[$partId])) {
                            $groupedProducts[$partId] = [
                                'part' => $removal->part,
                                'unauthorized' => 0,
                                'authorized' => 0,
                            ];
                        }
                        if ($removal->authorized) {
                            $groupedProducts[$partId]['authorized'] += $removal->quantity;
                        } else {
                            $groupedProducts[$partId]['unauthorized'] += $removal->quantity;
                        }
                    }
                    
                    // Dodaj również autoryzowane produkty z tego projektu
                    $authorizedRemovals = \App\Models\ProjectRemoval::where('project_id', $project->id)
                        ->where('authorized', true)
                        ->with('part')
                        ->get();
                    
                    foreach($authorizedRemovals as $removal) {
                        $partId = $removal->part_id;
                        if (!isset($groupedProducts[$partId])) {
                            $groupedProducts[$partId] = [
                                'part' => $removal->part,
                                'unauthorized' => 0,
                                'authorized' => $removal->quantity,
                            ];
                        } else {
                            $groupedProducts[$partId]['authorized'] += $removal->quantity;
                        }
                    }
                @endphp
                
                @foreach($groupedProducts as $partId => $data)
                    <tr data-part-id="{{ $partId }}" data-qr="{{ $data['part']->qr_code }}">
                        <td class="border p-2">{{ $data['part']->name }}</td>
                        <td class="border p-2 text-center font-mono text-xs">{{ $data['part']->qr_code ?? '-' }}</td>
                        <td class="border p-2 text-center font-bold text-red-600 unauthorized-cell">{{ $data['unauthorized'] }}</td>
                        <td class="border p-2 text-center font-bold text-green-600 authorized-cell">{{ $data['authorized'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($removals->count() == 0)
        <div class="text-center py-8 text-gray-500">
            <p class="text-xl mb-2">✅ Wszystkie produkty zostały autoryzowane!</p>
            <a href="{{ route('magazyn.projects.show', $project->id) }}" class="text-blue-600 hover:underline">Powrót do projektu</a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('qr-input');
    const messageContainer = document.getElementById('message-container');
    const message = document.getElementById('message');
    const statUnauthorized = document.getElementById('stat-unauthorized');
    const statAuthorized = document.getElementById('stat-authorized');
    const statScans = document.getElementById('stat-scans');
    const scanStatus = document.getElementById('scan-status');
    const startBtn = document.getElementById('start-scan-btn');
    const stopBtn = document.getElementById('stop-scan-btn');
    const errorModal = document.getElementById('error-modal');
    const errorCodeDisplay = document.getElementById('error-code-display');
    const errorConfirmBtn = document.getElementById('error-confirm-btn');
    
    let scansCount = 0;
    let authorizedCount = 0;
    let unauthorizedCount = {{ $removals->sum('quantity') }};
    let isScanning = false;
    let scanBuffer = '';
    let scanTimeout = null;
    let isProcessing = false;

    // Przycisk START
    startBtn.addEventListener('click', function() {
        startScanning();
    });

    // Przycisk STOP
    stopBtn.addEventListener('click', function() {
        stopScanning();
    });

    // Obsługa przycisku OK w modalu błędu
    errorConfirmBtn.addEventListener('click', function() {
        errorModal.classList.add('hidden');
        if (isScanning) {
            input.focus();
            showScanStatus();
        }
    });

    function startScanning() {
        isScanning = true;
        startBtn.classList.add('hidden');
        stopBtn.classList.remove('hidden');
        scanStatus.classList.remove('hidden');
        scanStatus.className = 'mb-6 p-4 rounded bg-blue-100 border-2 border-blue-400';
        scanStatus.querySelector('p').innerHTML = '<span class="animate-pulse">🔍</span> Oczekiwanie na skan kodu QR...';
        input.focus();
        
        // Włącz nasłuchiwanie klawiatury
        document.addEventListener('keypress', handleKeyPress);
    }

    function stopScanning() {
        isScanning = false;
        startBtn.classList.remove('hidden');
        stopBtn.classList.add('hidden');
        scanStatus.classList.add('hidden');
        
        // Wyłącz nasłuchiwanie klawiatury
        document.removeEventListener('keypress', handleKeyPress);
    }

    function handleKeyPress(e) {
        if (!isScanning || isProcessing) return;

        // Jeśli Enter - przetwórz kod
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            if (scanBuffer.length > 0) {
                processQRCode(scanBuffer.trim());
                scanBuffer = '';
            }
            return;
        }

        // Dodaj znak do bufora
        scanBuffer += e.key;

        // Zresetuj timeout - skaner zazwyczaj wysyła wszystkie znaki bardzo szybko
        clearTimeout(scanTimeout);
        scanTimeout = setTimeout(() => {
            // Jeśli timeout minie i mamy dane, przetwórz je
            if (scanBuffer.length > 0) {
                processQRCode(scanBuffer.trim());
                scanBuffer = '';
            }
        }, 100); // 100ms powinno wystarczyć dla skanera
    }

    async function processQRCode(qrCode) {
        if (!qrCode || isProcessing) return;
        
        isProcessing = true;
        scansCount++;
        statScans.textContent = scansCount;

        // Pokaż status skanowania
        scanStatus.className = 'mb-6 p-4 rounded bg-yellow-100 border-2 border-yellow-400';
        scanStatus.querySelector('p').innerHTML = `🔍 Przetwarzanie kodu: <strong class="font-mono">${qrCode}</strong>`;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
            
            if (!csrfToken) {
                console.error('CSRF token not found - Railway session issue?');
                showErrorModal(qrCode, 'Błąd sesji - odśwież stronę i spróbuj ponownie');
                isProcessing = false;
                return;
            }
            
            const response = await fetch('{{ route("magazyn.projects.authorize.process", $project->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' // For Railway compatibility
                },
                body: JSON.stringify({ qr_code: qrCode }),
                credentials: 'same-origin' // Ensure cookies are sent on Railway
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Błąd połączenia' }));
                
                // Special handling for CSRF/session issues on Railway
                if (response.status === 419 || response.status === 403) {
                    console.warn('CSRF/Session error on Railway - attempting token refresh');
                    
                    // Try to refresh CSRF token
                    try {
                        const refreshResponse = await fetch('/up', { 
                            method: 'GET',
                            credentials: 'same-origin'
                        });
                        
                        if (refreshResponse.ok) {
                            // Retry the authorization request
                            setTimeout(() => {
                                processQRCode(qrCode);
                            }, 500);
                            return;
                        }
                    } catch (e) {
                        console.error('Failed to refresh session:', e);
                    }
                    
                    showErrorModal(qrCode, 'Sesja wygasła - odśwież stronę i spróbuj ponownie');
                } else {
                    showErrorModal(qrCode, errorData.message || 'Błąd serwera');
                }
                
                isProcessing = false;
                return;
            }

            const data = await response.json();

            if (data.success) {
                showSuccessMessage(data.message);
                
                authorizedCount++;
                unauthorizedCount--;
                
                statAuthorized.textContent = authorizedCount;
                statUnauthorized.textContent = unauthorizedCount;

                // Zaktualizuj tabelę
                updateProductTable(qrCode);

                // Jeśli wszystko autoryzowane, pokaż komunikat
                if (unauthorizedCount === 0) {
                    stopScanning();
                    setTimeout(() => {
                        if (confirm('Wszystkie produkty zostały autoryzowane! Czy chcesz wrócić do projektu?')) {
                            window.location.href = '{{ route("magazyn.projects.show", $project->id) }}';
                        }
                    }, 1000);
                } else {
                    // Kontynuuj skanowanie
                    setTimeout(() => {
                        showScanStatus();
                        input.focus();
                        isProcessing = false;
                    }, 1500);
                }
            } else {
                showErrorModal(qrCode, data.message);
                isProcessing = false;
            }
        } catch (error) {
            console.error('Error:', error);
            showErrorModal(qrCode, 'Błąd połączenia z serwerem: ' + error.message);
            isProcessing = false;
        }
    }

    function showSuccessMessage(text) {
        scanStatus.className = 'mb-6 p-4 rounded bg-green-100 border-2 border-green-400';
        scanStatus.querySelector('p').innerHTML = `✅ <strong>${text}</strong>`;
    }

    function showScanStatus() {
        scanStatus.className = 'mb-6 p-4 rounded bg-blue-100 border-2 border-blue-400';
        scanStatus.querySelector('p').innerHTML = '<span class="animate-pulse">🔍</span> Oczekiwanie na skan kodu QR...';
    }

    function showErrorModal(qrCode, errorMessage) {
        errorCodeDisplay.textContent = `Kod: ${qrCode}`;
        errorModal.classList.remove('hidden');
        
        // Dodaj efekt dźwiękowy jeśli dostępny
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZTA0PVLHn7KpYFApFnt/yuW8gBjGB0PPYgy8GHW++7uSaSwwPU7Ho66pYFApGnt/yuW4fBjGB0PTYgS8GHW++7uSbSgwPU7Ho66pYFApGnt/yuW4fBjGB0PTYgS8GHW++7uSbSgwOUbHo7KpZEwpFnt/yuW4fBjCB0PTYgS8GHW++7uSbSgwOUbHo66pZEwpFnt/yuW4fBjCB0PTYgi8FHW++7uSbSgwOUbHo66pZFApFnt/zuG4fBjCB0PTYgi8GHW/A7uSaSgwPULHo66pZFApFnt/zuG4fBjCB0PTYgi8GHW/A7uSaSgwOULHo66pZFApFnt/zuG4fBjCB0PTYgi8FHW++7uSbSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSbSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEwpFnt/zuG4fBjCB0PTYgi8FHW++7uSaSgwOUbHo66pZEw==');
            audio.play().catch(() => {});
        } catch (e) {}
    }

    function updateProductTable(qrCode) {
        const row = document.querySelector(`tr[data-qr="${qrCode}"]`);
        if (row) {
            const unauthorizedCell = row.querySelector('.unauthorized-cell');
            const authorizedCell = row.querySelector('.authorized-cell');
            
            if (unauthorizedCell && authorizedCell) {
                // Zmniejsz nieautoryzowane
                const currentUnauthorized = parseInt(unauthorizedCell.textContent) || 0;
                const newUnauthorized = Math.max(0, currentUnauthorized - 1);
                unauthorizedCell.textContent = newUnauthorized;
                
                // Zwiększ autoryzowane
                const currentAuthorized = parseInt(authorizedCell.textContent) || 0;
                const newAuthorized = currentAuthorized + 1;
                authorizedCell.textContent = newAuthorized;
                
                // Jeśli wszystko autoryzowane dla tego produktu, podświetl na zielono
                if (newUnauthorized === 0) {
                    row.classList.add('bg-green-50');
                    unauthorizedCell.classList.remove('text-red-600');
                    unauthorizedCell.classList.add('text-gray-400');
                }
                
                // Animacja migania wiersza
                row.classList.add('bg-green-200');
                setTimeout(() => {
                    row.classList.remove('bg-green-200');
                    if (newUnauthorized === 0) {
                        row.classList.add('bg-green-50');
                    }
                }, 500);
            }
        }
    }
});
</script>

</body>
</html>
