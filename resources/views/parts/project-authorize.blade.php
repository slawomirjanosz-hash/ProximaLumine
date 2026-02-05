<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <li>Kliknij w pole poniżej lub skieruj skaner na pole</li>
            <li>Zeskanuj kod QR produktu</li>
            <li>System automatycznie autoryzuje produkt i odejmie go ze stanu magazynowego</li>
            <li>Każde skanowanie autoryzuje 1 sztukę produktu</li>
        </ol>
    </div>

    {{-- POLE DO SKANOWANIA --}}
    <div class="mb-6">
        <label class="block text-lg font-semibold mb-2">Zeskanuj kod QR:</label>
        <input 
            type="text" 
            id="qr-input" 
            class="w-full px-4 py-3 border-4 border-orange-400 rounded text-2xl font-mono text-center"
            placeholder="Kliknij tutaj i zeskanuj kod..."
            autofocus
        >
        <p class="text-sm text-gray-500 mt-2">💡 Po zeskanowaniu kodu pole wyświetli zeskanowany kod i automatycznie przetworzy autoryzację</p>
        <div id="last-scanned" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded hidden">
            <span class="text-sm font-semibold">Ostatnio zeskanowany kod:</span>
            <span id="last-scanned-code" class="ml-2 font-mono text-lg text-blue-800"></span>
        </div>
    </div>

    {{-- KOMUNIKATY --}}
    <div id="message-container" class="mb-6 hidden">
        <div id="message" class="p-4 rounded"></div>
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
    const lastScannedDiv = document.getElementById('last-scanned');
    const lastScannedCode = document.getElementById('last-scanned-code');
    
    let scansCount = 0;
    let authorizedCount = 0;
    let unauthorizedCount = {{ $removals->sum('quantity') }};

    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const qrCode = input.value.trim();
            
            if (!qrCode) {
                return;
            }

            // Pokaż zeskanowany kod
            lastScannedDiv.classList.remove('hidden');
            lastScannedCode.textContent = qrCode;

            processQRCode(qrCode);
            
            // Wyczyść pole po 2 sekundach
            setTimeout(() => {
                input.value = '';
                input.focus();
            }, 2000);
        }
    });

    async function processQRCode(qrCode) {
        scansCount++;
        statScans.textContent = scansCount;

        try {
            const response = await fetch('{{ route("magazyn.projects.authorize.process", $project->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ qr_code: qrCode })
            });

            if (!response.ok) {
                const errorData = await response.json();
                showMessage(errorData.message || 'Błąd serwera', 'error');
                return;
            }

            const data = await response.json();

            if (data.success) {
                showMessage(data.message, 'success');
                
                authorizedCount++;
                unauthorizedCount--;
                
                statAuthorized.textContent = authorizedCount;
                statUnauthorized.textContent = unauthorizedCount;

                // Zaktualizuj tabelę
                updateProductTable(qrCode);

                // Jeśli wszystko autoryzowane, pokaż komunikat
                if (unauthorizedCount === 0) {
                    setTimeout(() => {
                        if (confirm('Wszystkie produkty zostały autoryzowane! Czy chcesz wrócić do projektu?')) {
                            window.location.href = '{{ route("magazyn.projects.show", $project->id) }}';
                        }
                    }, 1000);
                }
            } else {
                showMessage(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Błąd połączenia z serwerem: ' + error.message, 'error');
        }
    }

    function showMessage(text, type) {
        messageContainer.classList.remove('hidden');
        message.textContent = text;
        
        if (type === 'success') {
            message.className = 'p-4 rounded bg-green-100 border border-green-400 text-green-800';
        } else {
            message.className = 'p-4 rounded bg-red-100 border border-red-400 text-red-800';
        }

        setTimeout(() => {
            messageContainer.classList.add('hidden');
        }, 3000);
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
            }
        }
    }

    // Focus na input przy załadowaniu strony
    input.focus();
    
    // Przywróć focus po kliknięciu w dowolne miejsce
    document.addEventListener('click', function() {
        input.focus();
    });
});
</script>

</body>
</html>
