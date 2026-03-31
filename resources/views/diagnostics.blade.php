<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostyka Railway - ProximaLumine</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <style>
        .status-ok { color: #16a34a; }
        .status-error { color: #dc2626; }
        .status-warning { color: #ea580c; }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-3xl font-bold mb-4">🔧 Diagnostyka Środowiska Railway</h1>
            <p class="text-gray-600 mb-4">
                Ten widok pomaga zidentyfikować problemy z generowaniem dokumentów Word i XLSX na Railway.
            </p>
            
            @auth
            <div class="mb-4 flex flex-wrap gap-2">
                <button onclick="runDiagnostics()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    🚀 Uruchom Diagnostykę
                </button>
                <button onclick="testWordGeneration()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    📄 Test Generowania Word
                </button>
                <button onclick="testXlsxGeneration()" class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                    📊 Test Generowania XLSX
                </button>
                <button onclick="probeXlsx()" class="px-6 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                    🔍 Probe XLSX (szczegóły błędu)
                </button>
                <button onclick="probeWord()" class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                    🔍 Probe Word (szczegóły błędu)
                </button>
            </div>
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-300 rounded-lg">
                <p class="font-semibold text-yellow-800 mb-2">⬇️ Bezpośrednie testy pobierania (klik = plik pobierany przez przeglądarkę):</p>
                <div class="flex flex-wrap gap-2">
                    <a href="/magazyn/sprawdz/test-minimal-xlsx" target="_blank"
                       class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800 text-sm">
                        📥 Pobierz minimalny XLSX (1 wiersz)
                    </a>
                    <a href="/magazyn/sprawdz/test-minimal-word" target="_blank"
                       class="px-4 py-2 bg-purple-700 text-white rounded hover:bg-purple-800 text-sm">
                        📥 Pobierz minimalny Word (1 akapit)
                    </a>
                    <a href="/magazyn/sprawdz/eksport-xlsx" target="_blank"
                       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        📥 Pobierz pełny XLSX (katalog)
                    </a>
                    <a href="/magazyn/sprawdz/eksport-word" target="_blank"
                       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        📥 Pobierz pełny Word (katalog)
                    </a>
                </div>
                <p class="text-xs text-yellow-700 mt-2">Jeśli "minimalny" pobiera się poprawnie a "pełny" nie — problem leży w przetwarzaniu danych (nie w transporcie). Jeśli "minimalny" też nie działa — problem z rozszerzeniami PHP lub prawami do zapisu tmp.</p>
            </div>
            @else
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                ⚠️ Musisz być zalogowany aby uruchomić diagnostykę.
                <a href="/login" class="underline ml-2">Zaloguj się</a>
            </div>
            @endauth
        </div>
        
        <div id="loading" class="hidden bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <span class="ml-4 text-lg">Ładowanie diagnostyki...</span>
            </div>
        </div>
        
        <div id="results" class="hidden bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-4">Wyniki Diagnostyki</h2>
            <div id="results-content" class="space-y-4"></div>
        </div>
        
        <div id="error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>Błąd:</strong> <span id="error-message"></span>
        </div>
    </div>
    
    <script>
        async function fetchJsonResponse(url) {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'include'
            });

            const rawText = await response.text();
            const trimmed = (rawText || '').trim();
            let data = null;

            if (trimmed) {
                try {
                    data = JSON.parse(trimmed);
                } catch (parseError) {
                    const shortBody = trimmed.length > 400 ? trimmed.slice(0, 400) + '…' : trimmed;
                    throw new Error(`HTTP ${response.status} ${response.statusText}: odpowiedź nie jest JSON. Body: ${shortBody}`);
                }
            }

            return { response, data, rawText: trimmed };
        }

        async function runDiagnostics() {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('results').classList.add('hidden');
            document.getElementById('error').classList.add('hidden');
            
            try {
                const { response, data, rawText } = await fetchJsonResponse('/api/diagnostics/environment');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${rawText || 'Brak treści odpowiedzi'}`);
                }

                displayResults(data);
                
            } catch (error) {
                console.error('Błąd diagnostyki:', error);
                document.getElementById('error-message').textContent = error.message;
                document.getElementById('error').classList.remove('hidden');
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }
        
        function displayResults(data) {
            const container = document.getElementById('results-content');
            container.innerHTML = '';
            
            // Environment
            addSection(container, '🌍 Środowisko', {
                'Timestamp': data.timestamp,
                'Environment': data.environment
            });
            
            // PHP Extensions
            addSection(container, '📦 Rozszerzenia PHP', data.php_extensions);
            
            // PHP Info
            addSection(container, 'ℹ️ Informacje PHP', data.php_info);
            
            // Temp Directories
            const tempDirs = {};
            for (const [key, value] of Object.entries(data.temp_directories)) {
                tempDirs[key] = formatTempDirInfo(value);
            }
            addSection(container, '📁 Katalogi Tymczasowe', tempDirs);
            
            // File Creation Test
            addSection(container, '✍️ Test Tworzenia Plików', data.file_creation_test);
            
            // PhpWord
            addSection(container, '📝 PhpOffice/PhpWord', data.phpword);
            
            // Database
            addSection(container, '🗄️ Baza Danych', data.database);
            
            document.getElementById('results').classList.remove('hidden');
        }
        
        function formatTempDirInfo(info) {
            let result = `Path: ${info.path}<br>`;
            result += `Exists: ${formatStatus(info.exists)}<br>`;
            result += `Writable: ${formatStatus(info.writable)}<br>`;
            result += `Readable: ${formatStatus(info.readable)}`;
            return result;
        }
        
        function formatStatus(value) {
            if (value === true || value === '✅ Zainstalowane' || value === '✅ Działa (można tworzyć, zapisywać, odczytywać)' || value === '✅ Połączono') {
                return `<span class="status-ok">✅ ${typeof value === 'boolean' ? 'TAK' : value}</span>`;
            } else if (value === false) {
                return `<span class="status-error">❌ NIE</span>`;
            } else if (typeof value === 'string' && value.includes('❌')) {
                return `<span class="status-error">${value}</span>`;
            } else if (typeof value === 'string' && value.includes('⚠️')) {
                return `<span class="status-warning">${value}</span>`;
            } else if (value === 'N/A (katalog nie istnieje)') {
                return `<span class="status-warning">${value}</span>`;
            }
            return value;
        }
        
        function addSection(container, title, data) {
            const section = document.createElement('div');
            section.className = 'border border-gray-200 rounded-lg p-4';
            
            const titleEl = document.createElement('h3');
            titleEl.className = 'text-xl font-bold mb-3';
            titleEl.textContent = title;
            section.appendChild(titleEl);
            
            const table = document.createElement('table');
            table.className = 'w-full text-sm';
            
            for (const [key, value] of Object.entries(data)) {
                const row = table.insertRow();
                const cellKey = row.insertCell(0);
                const cellValue = row.insertCell(1);
                
                cellKey.className = 'font-semibold pr-4 py-1 align-top';
                cellKey.textContent = key;
                
                cellValue.className = 'py-1';
                cellValue.innerHTML = formatStatus(value);
            }
            
            section.appendChild(table);
            container.appendChild(section);
        }
        
        async function testWordGeneration() {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('results').classList.add('hidden');
            document.getElementById('error').classList.add('hidden');
            
            try {
                const { response, data, rawText } = await fetchJsonResponse('/api/diagnostics/test-word');
                if (!data) {
                    throw new Error(`HTTP ${response.status}: ${rawText || 'Brak danych JSON'}`);
                }
                
                if (!response.ok) {
                    // Błąd - pokaż szczegóły
                    const container = document.getElementById('results-content');
                    container.innerHTML = '';
                    
                    const errorSection = document.createElement('div');
                    errorSection.className = 'border border-red-400 bg-red-50 rounded-lg p-6';
                    errorSection.innerHTML = `
                        <h3 class="text-2xl font-bold text-red-700 mb-4">❌ Test Generowania Word - BŁĄD</h3>
                        <div class="space-y-2">
                            <p><strong>Błąd:</strong> <span class="text-red-600">${data.error || 'Unknown error'}</span></p>
                            ${data.details ? `
                                <p><strong>Klasa wyjątku:</strong> ${data.details.exception_class || 'N/A'}</p>
                                <p><strong>Plik:</strong> ${data.details.file || 'N/A'}</p>
                                <p><strong>Linia:</strong> ${data.details.line || 'N/A'}</p>
                                ${data.details.trace ? `
                                    <details class="mt-4">
                                        <summary class="cursor-pointer font-semibold text-red-700">Stack Trace (kliknij aby rozwinąć)</summary>
                                        <pre class="mt-2 p-4 bg-red-100 rounded text-xs overflow-auto">${data.details.trace.join('\n')}</pre>
                                    </details>
                                ` : ''}
                            ` : ''}
                        </div>
                    `;
                    container.appendChild(errorSection);
                    
                    document.getElementById('results').classList.remove('hidden');
                } else {
                    // Sukces
                    const container = document.getElementById('results-content');
                    container.innerHTML = '';
                    
                    const successSection = document.createElement('div');
                    successSection.className = 'border border-green-400 bg-green-50 rounded-lg p-6';
                    successSection.innerHTML = `
                        <h3 class="text-2xl font-bold text-green-700 mb-4">✅ ${data.message}</h3>
                        <div class="space-y-2">
                            <p><strong>Plik tymczasowy:</strong> ${data.details.temp_file}</p>
                            <p><strong>Rozmiar pliku:</strong> ${data.details.file_size}</p>
                            <p><strong>Katalog temp:</strong> ${data.details.temp_dir}</p>
                            <p><strong>Środowisko:</strong> ${data.details.environment}</p>
                            <p><strong>PHP Version:</strong> ${data.details.php_version}</p>
                        </div>
                        <div class="mt-4 p-4 bg-green-100 rounded">
                            <p class="font-semibold">✅ Generowanie prostych dokumentów Word <strong>DZIAŁA</strong> na Railway!</p>
                            <p class="text-sm mt-2">Jeśli generowanie ofert nadal nie działa, problem może być w:</p>
                            <ul class="list-disc ml-6 text-sm mt-1">
                                <li>Konkretnych danych oferty (np. złe znaki, zbyt duże obrazy)</li>
                                <li>Rozmiarze generowanego dokumentu</li>
                                <li>Timeout serwera Railway (domyślnie 30s)</li>
                            </ul>
                        </div>
                    `;
                    container.appendChild(successSection);
                    
                    document.getElementById('results').classList.remove('hidden');
                }
                
            } catch (error) {
                console.error('Błąd testu Word:', error);
                document.getElementById('error-message').textContent = error.message;
                document.getElementById('error').classList.remove('hidden');
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }

        async function testXlsxGeneration() {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('results').classList.add('hidden');
            document.getElementById('error').classList.add('hidden');

            try {
                const container = document.getElementById('results-content');
                container.innerHTML = '';

                const lite = await fetchJsonResponse('/api/diagnostics/test-xlsx-lite');
                if (!lite.response.ok || !lite.data) {
                    throw new Error(`Lite test: HTTP ${lite.response.status}: ${lite.rawText || 'Brak danych'}`);
                }

                const liteSection = document.createElement('div');
                liteSection.className = 'border border-blue-300 bg-blue-50 rounded-lg p-6';
                liteSection.innerHTML = `
                    <h3 class="text-xl font-bold text-blue-800 mb-3">ℹ️ Test XLSX Lite</h3>
                    <p><strong>Status:</strong> ${lite.data.message || 'OK'}</p>
                    <p><strong>Rozszerzenia:</strong> ${Object.entries(lite.data.details.extensions || {}).map(([k,v]) => `${k}=${v ? 'OK' : 'BRAK'}`).join(', ')}</p>
                    <p><strong>Plik testowy:</strong> ${lite.data.details.file_write_test || 'brak'}</p>
                `;
                container.appendChild(liteSection);

                const full = await fetchJsonResponse('/api/diagnostics/test-xlsx');
                const response = full.response;
                const data = full.data;
                if (!data) {
                    throw new Error(`Full test: HTTP ${response.status}: ${full.rawText || 'Brak danych JSON'}`);
                }

                if (!response.ok) {
                    const errorSection = document.createElement('div');
                    errorSection.className = 'border border-red-400 bg-red-50 rounded-lg p-6';
                    errorSection.innerHTML = `
                        <h3 class="text-2xl font-bold text-red-700 mb-4">❌ Test Generowania XLSX - BŁĄD</h3>
                        <div class="space-y-2">
                            <p><strong>Błąd:</strong> <span class="text-red-600">${data.error || 'Unknown error'}</span></p>
                            ${data.details ? `
                                <p><strong>Klasa wyjątku:</strong> ${data.details.exception_class || 'N/A'}</p>
                                <p><strong>Plik:</strong> ${data.details.file || 'N/A'}</p>
                                <p><strong>Linia:</strong> ${data.details.line || 'N/A'}</p>
                                ${data.details.trace ? `
                                    <details class="mt-4">
                                        <summary class="cursor-pointer font-semibold text-red-700">Stack Trace (kliknij aby rozwinąć)</summary>
                                        <pre class="mt-2 p-4 bg-red-100 rounded text-xs overflow-auto">${data.details.trace.join('\n')}</pre>
                                    </details>
                                ` : ''}
                            ` : ''}
                            <p><strong>HTTP:</strong> ${response.status} ${response.statusText}</p>
                        </div>
                    `;
                    container.appendChild(errorSection);
                } else {
                    const successSection = document.createElement('div');
                    successSection.className = 'border border-green-400 bg-green-50 rounded-lg p-6';
                    successSection.innerHTML = `
                        <h3 class="text-2xl font-bold text-green-700 mb-4">✅ ${data.message}</h3>
                        <div class="space-y-2">
                            <p><strong>Liczba produktów:</strong> ${data.details.parts_count}</p>
                            <p><strong>Przetestowane ID:</strong> ${(data.details.tested_ids || []).join(', ')}</p>
                            <p><strong>Rozmiar wygenerowanego pliku:</strong> ${data.details.generated_bytes} B</p>
                            <p><strong>Środowisko:</strong> ${data.details.environment}</p>
                            <p><strong>PHP Version:</strong> ${data.details.php_version}</p>
                        </div>
                        <div class="mt-4 p-4 bg-green-100 rounded">
                            <p class="font-semibold">✅ Silnik XLSX działa na Railway.</p>
                            <p class="text-sm mt-2">Jeśli przycisk "Pobierz do Excel" dalej zwraca 503, sprawdź logi z endpointu /magazyn/sprawdz/eksport-xlsx i porównaj z tym testem.</p>
                        </div>
                    `;
                    container.appendChild(successSection);
                }

                document.getElementById('results').classList.remove('hidden');
            } catch (error) {
                console.error('Błąd testu XLSX:', error);
                const container = document.getElementById('results-content');
                container.innerHTML = '';

                const failSection = document.createElement('div');
                failSection.className = 'border border-red-400 bg-red-50 rounded-lg p-6';
                failSection.innerHTML = `
                    <h3 class="text-2xl font-bold text-red-700 mb-4">❌ Test Generowania XLSX - BŁĄD TRANSPORTU</h3>
                    <p><strong>Komunikat:</strong> ${error.message}</p>
                    <p class="text-sm mt-2">Próba odczytu ostatniego śladu serwera...</p>
                `;
                container.appendChild(failSection);

                try {
                    const traceResp = await fetchJsonResponse('/api/diagnostics/xlsx-trace');
                    if (traceResp.response.ok && traceResp.data && traceResp.data.trace) {
                        const traceSection = document.createElement('div');
                        traceSection.className = 'border border-yellow-400 bg-yellow-50 rounded-lg p-6 mt-4';

                        const trace = traceResp.data.trace;
                        const entries = Array.isArray(trace.entries) ? trace.entries : [];
                        const lastEntry = entries.length ? entries[entries.length - 1] : null;
                        const lastDetails = lastEntry && lastEntry.details ? lastEntry.details : null;
                        const lastMessage = lastDetails && lastDetails.message ? lastDetails.message : 'N/A';
                        const lastFile = lastDetails && lastDetails.file ? lastDetails.file : 'N/A';
                        const lastLine = lastDetails && typeof lastDetails.line !== 'undefined' ? lastDetails.line : 'N/A';

                        traceSection.innerHTML = `
                            <h3 class="text-xl font-bold text-yellow-800 mb-3">🧭 Ostatni ślad diagnostyczny XLSX</h3>
                            <p><strong>Plik śladu:</strong> ${traceResp.data.trace_file || 'N/A'}</p>
                            <p><strong>Start:</strong> ${trace.started_at || 'N/A'}</p>
                            <p><strong>Ostatni etap:</strong> ${lastEntry ? lastEntry.stage : 'N/A'}</p>
                            ${lastEntry ? `<p><strong>Czas etapu:</strong> ${lastEntry.time || 'N/A'}</p>` : ''}
                            ${lastEntry ? `<p><strong>Ostatni błąd:</strong> ${lastMessage}</p>` : ''}
                            ${lastEntry ? `<p><strong>Plik/Linia:</strong> ${lastFile}:${lastLine}</p>` : ''}
                            <details class="mt-4">
                                <summary class="cursor-pointer font-semibold text-yellow-800">Pokaż pełny ślad JSON</summary>
                                <pre class="mt-2 p-4 bg-yellow-100 rounded text-xs overflow-auto">${JSON.stringify(trace, null, 2)}</pre>
                            </details>
                        `;
                        container.appendChild(traceSection);
                    }
                } catch (traceError) {
                    console.error('Nie udało się pobrać śladu XLSX:', traceError);
                }

                document.getElementById('results').classList.remove('hidden');
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }
        async function probeXlsx() {
            await runProbe('/api/diagnostics/probe-xlsx', 'XLSX');
        }

        async function probeWord() {
            await runProbe('/api/diagnostics/probe-word', 'Word');
        }

        async function runProbe(url, label) {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('results').classList.add('hidden');
            document.getElementById('error').classList.add('hidden');
            try {
                const { response, data } = await fetchJsonResponse(url);
                const container = document.getElementById('results-content');
                container.innerHTML = '';
                const ok = response.ok && data && data.success;
                const section = document.createElement('div');
                section.className = ok
                    ? 'border border-green-400 bg-green-50 rounded-lg p-6'
                    : 'border border-red-400 bg-red-50 rounded-lg p-6';
                const rows = data ? Object.entries(data).map(([k,v]) => {
                    const val = typeof v === 'object' ? JSON.stringify(v, null, 2) : String(v);
                    const isOk = v === true || (typeof v === 'number' && v > 0);
                    const isBad = v === false || (typeof v === 'string' && (v.startsWith('Błąd') || v.startsWith('Brak')));
                    const color = isOk ? 'text-green-700' : (isBad ? 'text-red-700' : 'text-gray-800');
                    return `<tr><td class="font-mono font-semibold pr-4 align-top py-1 text-sm">${k}</td><td class="py-1 text-sm ${color} whitespace-pre-wrap">${val}</td></tr>`;
                }).join('') : '';
                section.innerHTML = `
                    <h3 class="text-xl font-bold mb-4 ${ok ? 'text-green-700' : 'text-red-700'}">${ok ? '✅' : '❌'} Probe ${label}</h3>
                    <table class="w-full">${rows}</table>`;
                container.appendChild(section);
                document.getElementById('results').classList.remove('hidden');
            } catch (error) {
                document.getElementById('error-message').textContent = error.message;
                document.getElementById('error').classList.remove('hidden');
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }
    </script>
</body>
</html>
