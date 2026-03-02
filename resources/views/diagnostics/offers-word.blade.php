<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Diagnostyka Word ofert</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen">
    @include('parts.menu')

    <main class="max-w-6xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-2">Diagnostyka generowania Word dla ofert</h1>
            <p class="text-sm text-gray-600 mb-6">Ta strona diagnozuje błąd 500 dla endpointu /wyceny/{id}/generate-word (szczególnie na Railway).</p>

            <div class="flex flex-wrap gap-3 items-end mb-4">
                <div>
                    <label for="offer-id" class="block text-sm font-medium text-gray-700 mb-1">ID oferty</label>
                    <input id="offer-id" type="number" min="1" value="{{ $defaultOfferId }}" class="border border-gray-300 rounded px-3 py-2 w-40">
                </div>
                <button id="run-diagnostics" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Uruchom diagnostykę</button>
                <button id="run-full-test" class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Test pełnej generacji (pokaż błąd)</button>
                <a id="open-generate-link" href="#" target="_blank" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Otwórz generate-word</a>
            </div>

            <div class="mb-2 text-sm text-gray-500" id="status">Status: gotowe</div>
            <pre id="output" class="bg-gray-900 text-green-200 p-4 rounded text-xs overflow-auto" style="max-height: 70vh;">Kliknij "Uruchom diagnostykę".</pre>
        </div>
    </main>

    <script>
        const offerIdInput = document.getElementById('offer-id');
        const statusEl = document.getElementById('status');
        const outputEl = document.getElementById('output');
        const runButton = document.getElementById('run-diagnostics');
        const generateLink = document.getElementById('open-generate-link');

        function getOfferId() {
            return Number(offerIdInput.value || 0);
        }

        function updateGenerateLink() {
            const offerId = getOfferId();
            generateLink.href = offerId > 0 ? `/wyceny/${offerId}/generate-word` : '#';
        }

        async function runDiagnostics() {
            const offerId = getOfferId();
            if (!offerId || offerId < 1) {
                statusEl.textContent = 'Status: podaj poprawne ID oferty';
                return;
            }

            updateGenerateLink();
            statusEl.textContent = 'Status: trwa diagnostyka...';
            outputEl.textContent = 'Ładowanie...';

            try {
                const response = await fetch(`/api/diagnostics/offers-word/${offerId}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const text = await response.text();
                let parsed = text;
                try {
                    parsed = JSON.parse(text);
                } catch (_) {}

                outputEl.textContent = typeof parsed === 'string'
                    ? parsed
                    : JSON.stringify(parsed, null, 2);

                statusEl.textContent = `Status: zakończono (${response.status})`;
            } catch (error) {
                statusEl.textContent = 'Status: błąd żądania';
                outputEl.textContent = String(error);
            }
        }

        async function runFullGenerationTest() {
            const offerId = getOfferId();
            if (!offerId || offerId < 1) {
                statusEl.textContent = 'Status: podaj poprawne ID oferty';
                return;
            }

            statusEl.textContent = 'Status: trwa pełny test generacji...';
            outputEl.textContent = 'Ładowanie...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch(`/api/diagnostics/offers-word/${offerId}/full-test`, {
                    headers: {
                        'Accept': 'application/json, */*',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin'
                });

                const contentType = response.headers.get('Content-Type') || '';

                if (contentType.includes('application/json')) {
                    // Błąd zwrócony jako JSON
                    const data = await response.json();
                    outputEl.textContent = `HTTP ${response.status}\n\n` + JSON.stringify(data, null, 2);
                    statusEl.textContent = `Status: BŁĄD ${response.status} - sprawdź output`;
                } else if (contentType.includes('application/vnd') || contentType.includes('octet-stream')) {
                    outputEl.textContent = `Sukces! Dokument Word został wygenerowany (HTTP ${response.status}).\nContent-Type: ${contentType}`;
                    statusEl.textContent = 'Status: generacja zakończona sukcesem!';
                } else {
                    const text = await response.text();
                    outputEl.textContent = `HTTP ${response.status}\nContent-Type: ${contentType}\n\n${text.substring(0, 3000)}`;
                    statusEl.textContent = `Status: odpowiedź HTTP ${response.status}`;
                }
            } catch (error) {
                statusEl.textContent = 'Status: błąd żądania fetch';
                outputEl.textContent = String(error);
            }
        }

        offerIdInput.addEventListener('input', updateGenerateLink);
        runButton.addEventListener('click', runDiagnostics);
        document.getElementById('run-full-test').addEventListener('click', runFullGenerationTest);
        updateGenerateLink();
    </script>
</body>
</html>
