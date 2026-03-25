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
            <div class="mb-4">
                <button onclick="runDiagnostics()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    🚀 Uruchom Diagnostykę
                </button>
                <button onclick="testWordGeneration()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition ml-2">
                    📄 Test Generowania Word
                </button>
                <button onclick="testXlsxGeneration()" class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition ml-2">
                    📊 Test Generowania XLSX
                </button>
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
        async function runDiagnostics() {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('results').classList.add('hidden');
            document.getElementById('error').classList.add('hidden');
            
            try {
                const response = await fetch('/api/diagnostics/environment', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${await response.text()}`);
                }
                
                const data = await response.json();
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
                const response = await fetch('/api/diagnostics/test-word', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                
                const data = await response.json();
                
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
                const response = await fetch('/api/diagnostics/test-xlsx', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });

                const data = await response.json();
                const container = document.getElementById('results-content');
                container.innerHTML = '';

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
                document.getElementById('error-message').textContent = error.message;
                document.getElementById('error').classList.remove('hidden');
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }
    </script>
</body>
</html>
