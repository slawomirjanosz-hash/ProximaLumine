<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Diagnostics - Railway Debug</title>
    @vite(['resources/css/app.css'])
    <style>
        .success { @apply bg-green-100 border-green-500 text-green-900; }
        .error { @apply bg-red-100 border-red-500 text-red-900; }
        .warning { @apply bg-yellow-100 border-yellow-500 text-yellow-900; }
        .info { @apply bg-blue-100 border-blue-500 text-blue-900; }
        .diagnostic-box { @apply border-l-4 p-4 mb-4 rounded; }
        pre { @apply bg-gray-100 p-4 rounded overflow-x-auto text-xs; }
        table { @apply w-full border-collapse text-sm; }
        th { @apply bg-gray-200 p-2 text-left font-bold border; }
        td { @apply p-2 border; }
        .badge { @apply inline-block px-2 py-1 rounded text-xs font-bold; }
        .badge-success { @apply bg-green-500 text-white; }
        .badge-error { @apply bg-red-500 text-white; }
        .badge-warning { @apply bg-yellow-500 text-white; }
    </style>
</head>
<body class="bg-gray-50 p-6">

<div class="max-w-7xl mx-auto">
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🔧 CRM Diagnostyka - Railway Debug</h1>
        <p class="text-gray-600">Kompleksowa diagnostyka tabeli crm_stages i funkcjonalności CRM</p>
        <p class="text-sm text-gray-500 mt-2">Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <!-- ENVIRONMENT INFO -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">📍 Środowisko</h2>
        
        <table>
            <tr>
                <th class="w-1/3">Parametr</th>
                <th>Wartość</th>
            </tr>
            <tr>
                <td>APP_ENV</td>
                <td><code>{{ config('app.env') }}</code></td>
            </tr>
            <tr>
                <td>APP_DEBUG</td>
                <td><code>{{ config('app.debug') ? 'true' : 'false' }}</code></td>
            </tr>
            <tr>
                <td>Database Connection</td>
                <td><code>{{ config('database.default') }}</code></td>
            </tr>
            <tr>
                <td>Database Name</td>
                <td><code>{{ config('database.connections.' . config('database.default') . '.database') }}</code></td>
            </tr>
            <tr>
                <td>Laravel Version</td>
                <td><code>{{ app()->version() }}</code></td>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><code>{{ phpversion() }}</code></td>
            </tr>
        </table>
    </div>

    <!-- TABLE STRUCTURE CHECK -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🗄️ Struktura Tabeli crm_stages</h2>
        
        @php
            try {
                $tableExists = Schema::hasTable('crm_stages');
                $hasIsClosedColumn = Schema::hasColumn('crm_stages', 'is_closed');
                $columns = DB::select('DESCRIBE crm_stages');
            } catch (\Exception $e) {
                $tableExists = false;
                $hasIsClosedColumn = false;
                $columns = [];
                $tableError = $e->getMessage();
            }
        @endphp

        @if($tableExists)
            <div class="diagnostic-box success">
                <strong>✓ Tabela crm_stages istnieje</strong>
            </div>

            <div class="diagnostic-box {{ $hasIsClosedColumn ? 'success' : 'error' }}">
                @if($hasIsClosedColumn)
                    <strong>✓ Kolumna is_closed istnieje</strong>
                @else
                    <strong>✗ BRAK kolumny is_closed!</strong>
                    <p class="mt-2">To jest główna przyczyna błędów 500. Uruchom migrację:</p>
                    <pre>railway run php artisan migrate --force</pre>
                @endif
            </div>

            <h3 class="font-bold mt-4 mb-2">Kolumny w tabeli:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($columns as $col)
                        <tr class="{{ $col->Field === 'is_closed' ? 'bg-green-50' : '' }}">
                            <td><strong>{{ $col->Field }}</strong></td>
                            <td>{{ $col->Type }}</td>
                            <td>{{ $col->Null }}</td>
                            <td>{{ $col->Key }}</td>
                            <td>{{ $col->Default ?? 'NULL' }}</td>
                            <td>{{ $col->Extra }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="diagnostic-box error">
                <strong>✗ Tabela crm_stages NIE istnieje!</strong>
                @if(isset($tableError))
                    <pre class="mt-2">{{ $tableError }}</pre>
                @endif
            </div>
        @endif
    </div>

    <!-- MIGRATION STATUS -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">📦 Status Migracji</h2>
        
        @php
            try {
                $migrations = DB::table('migrations')
                    ->where('migration', 'LIKE', '%crm%')
                    ->orderBy('batch')
                    ->get();
                $isClosedMigration = DB::table('migrations')
                    ->where('migration', 'LIKE', '%is_closed%')
                    ->first();
            } catch (\Exception $e) {
                $migrations = collect();
                $isClosedMigration = null;
                $migrationError = $e->getMessage();
            }
        @endphp

        @if($isClosedMigration)
            <div class="diagnostic-box success">
                <strong>✓ Migracja is_closed została uruchomiona</strong>
                <p class="mt-2">Batch: {{ $isClosedMigration->batch }}</p>
                <p>Migration: <code>{{ $isClosedMigration->migration }}</code></p>
            </div>
        @else
            <div class="diagnostic-box error">
                <strong>✗ Migracja is_closed NIE została uruchomiona!</strong>
                <p class="mt-2">Musisz uruchomić:</p>
                <pre>railway run php artisan migrate --force</pre>
            </div>
        @endif

        <h3 class="font-bold mt-4 mb-2">Wszystkie migracje CRM:</h3>
        @if($migrations->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Migration</th>
                        <th>Batch</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($migrations as $migration)
                        <tr class="{{ str_contains($migration->migration, 'is_closed') ? 'bg-green-50' : '' }}">
                            <td>{{ $migration->migration }}</td>
                            <td>{{ $migration->batch }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="diagnostic-box warning">
                <strong>⚠ Nie znaleziono migracji CRM</strong>
                @if(isset($migrationError))
                    <pre class="mt-2">{{ $migrationError }}</pre>
                @endif
            </div>
        @endif
    </div>

    <!-- STAGES DATA -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">📋 Dane w tabeli crm_stages</h2>
        
        @php
            try {
                $stages = DB::table('crm_stages')->orderBy('order')->get();
                $stagesError = null;
            } catch (\Exception $e) {
                $stages = collect();
                $stagesError = $e->getMessage();
            }
        @endphp

        @if($stagesError)
            <div class="diagnostic-box error">
                <strong>✗ Błąd podczas odczytu danych:</strong>
                <pre class="mt-2">{{ $stagesError }}</pre>
            </div>
        @elseif($stages->count() > 0)
            <div class="diagnostic-box info">
                <strong>Znaleziono {{ $stages->count() }} etapów</strong>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Color</th>
                        <th>Order</th>
                        <th>is_active</th>
                        <th>is_closed</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stages as $stage)
                        <tr>
                            <td>{{ $stage->id }}</td>
                            <td><strong>{{ $stage->name }}</strong></td>
                            <td><code>{{ $stage->slug }}</code></td>
                            <td>
                                <span class="inline-block px-3 py-1 rounded text-white" style="background-color: {{ $stage->color ?? '#gray' }};">
                                    {{ $stage->color ?? '#gray' }}
                                </span>
                            </td>
                            <td>{{ $stage->order }}</td>
                            <td>
                                @if($stage->is_active)
                                    <span class="badge badge-success">TAK</span>
                                @else
                                    <span class="badge badge-error">NIE</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($stage->is_closed))
                                    @if($stage->is_closed)
                                        <span class="badge badge-success">TAK</span>
                                    @else
                                        <span class="badge">NIE</span>
                                    @endif
                                @else
                                    <span class="badge badge-error">BRAK KOLUMNY</span>
                                @endif
                            </td>
                            <td class="text-xs">{{ $stage->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="diagnostic-box warning">
                <strong>⚠ Brak etapów w tabeli</strong>
            </div>
        @endif
    </div>

    <!-- TEST INSERT -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🧪 Test INSERT (symulacja)</h2>
        
        @php
            $testSlug = 'test_' . time();
            $insertTestResult = null;
            $insertTestError = null;
            
            // Symuluj to co robi addCrmStage
            try {
                $hasColumn = Schema::hasColumn('crm_stages', 'is_closed');
                
                $insertData = [
                    'name' => 'TEST STAGE',
                    'slug' => $testSlug,
                    'color' => '#ff0000',
                    'order' => 999,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                if ($hasColumn) {
                    $insertData['is_closed'] = 0;
                }
                
                // NIE wykonuj INSERT naprawdę, tylko testuj strukturę
                $insertTestResult = $insertData;
                
            } catch (\Exception $e) {
                $insertTestError = $e->getMessage();
            }
        @endphp

        @if($insertTestError)
            <div class="diagnostic-box error">
                <strong>✗ Błąd podczas przygotowania INSERT:</strong>
                <pre class="mt-2">{{ $insertTestError }}</pre>
            </div>
        @else
            <div class="diagnostic-box {{ isset($insertTestResult['is_closed']) ? 'success' : 'warning' }}">
                @if(isset($insertTestResult['is_closed']))
                    <strong>✓ INSERT zawierałby kolumnę is_closed</strong>
                @else
                    <strong>⚠ INSERT BEZ kolumny is_closed (fallback aktywny)</strong>
                @endif
            </div>

            <h3 class="font-bold mb-2">Dane które byłyby wstawione:</h3>
            <pre>{{ json_encode($insertTestResult, JSON_PRETTY_PRINT) }}</pre>
        @endif
    </div>

    <!-- SCHEMA HASCOLUMN TEST -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🔍 Test Schema::hasColumn()</h2>
        
        @php
            $schemaTests = [];
            $columnsToTest = ['id', 'name', 'slug', 'color', 'order', 'is_active', 'is_closed', 'nonexistent_column'];
            
            foreach ($columnsToTest as $col) {
                try {
                    $exists = Schema::hasColumn('crm_stages', $col);
                    $schemaTests[$col] = [
                        'exists' => $exists,
                        'error' => null
                    ];
                } catch (\Exception $e) {
                    $schemaTests[$col] = [
                        'exists' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Kolumna</th>
                    <th>Schema::hasColumn()</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schemaTests as $col => $result)
                    <tr class="{{ $col === 'is_closed' ? 'bg-yellow-50' : '' }}">
                        <td><strong>{{ $col }}</strong></td>
                        <td>
                            @if($result['exists'])
                                <span class="badge badge-success">TRUE</span>
                            @else
                                <span class="badge badge-error">FALSE</span>
                            @endif
                        </td>
                        <td>
                            @if($result['error'])
                                <span class="text-red-600">Error: {{ $result['error'] }}</span>
                            @elseif($result['exists'])
                                <span class="text-green-600">✓ Istnieje</span>
                            @else
                                <span class="text-gray-500">Nie istnieje</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- CONTROLLER CODE CHECK -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">💻 Weryfikacja Kodu Kontrolera</h2>
        
        @php
            $controllerPath = app_path('Http/Controllers/PartController.php');
            $controllerExists = file_exists($controllerPath);
            
            if ($controllerExists) {
                $controllerContent = file_get_contents($controllerPath);
                $hasSchemaCheck = str_contains($controllerContent, 'Schema::hasColumn');
                $hasTryCatch = str_contains($controllerContent, 'try {') && str_contains($controllerContent, 'catch');
                $hasIsClosedLogic = str_contains($controllerContent, 'is_closed');
            }
        @endphp

        @if($controllerExists)
            <div class="diagnostic-box success">
                <strong>✓ PartController.php istnieje</strong>
            </div>

            <table>
                <tr>
                    <th class="w-1/2">Sprawdzenie</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>Używa Schema::hasColumn()</td>
                    <td>
                        @if($hasSchemaCheck)
                            <span class="badge badge-success">TAK</span>
                        @else
                            <span class="badge badge-error">NIE</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Ma try-catch</td>
                    <td>
                        @if($hasTryCatch)
                            <span class="badge badge-success">TAK</span>
                        @else
                            <span class="badge badge-warning">NIE</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Obsługuje is_closed</td>
                    <td>
                        @if($hasIsClosedLogic)
                            <span class="badge badge-success">TAK</span>
                        @else
                            <span class="badge badge-error">NIE</span>
                        @endif
                    </td>
                </tr>
            </table>
        @else
            <div class="diagnostic-box error">
                <strong>✗ PartController.php NIE istnieje!</strong>
            </div>
        @endif
    </div>

    <!-- DATABASE RAW QUERY TEST -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🗃️ Test Zapytania SQL</h2>
        
        @php
            try {
                $rawQuery = "SELECT * FROM crm_stages LIMIT 1";
                $rawResult = DB::select($rawQuery);
                $rawError = null;
            } catch (\Exception $e) {
                $rawResult = null;
                $rawError = $e->getMessage();
            }
        @endphp

        @if($rawError)
            <div class="diagnostic-box error">
                <strong>✗ Błąd zapytania SQL:</strong>
                <pre class="mt-2">{{ $rawError }}</pre>
            </div>
        @else
            <div class="diagnostic-box success">
                <strong>✓ Zapytanie SQL działa</strong>
            </div>
            
            @if($rawResult && count($rawResult) > 0)
                <h3 class="font-bold mb-2">Przykładowy rekord (pierwszy):</h3>
                <pre>{{ json_encode($rawResult[0], JSON_PRETTY_PRINT) }}</pre>
            @endif
        @endif
    </div>

    <!-- RECOMMENDATIONS -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">💡 Rekomendacje</h2>
        
        @php
            $issues = [];
            $recommendations = [];
            
            if (!$tableExists) {
                $issues[] = 'Tabela crm_stages nie istnieje';
                $recommendations[] = 'Uruchom: railway run php artisan migrate --force';
            }
            
            if ($tableExists && !$hasIsClosedColumn) {
                $issues[] = 'Brak kolumny is_closed';
                $recommendations[] = 'Uruchom: railway run php artisan migrate --force';
                $recommendations[] = 'LUB wykonaj SQL: ALTER TABLE crm_stages ADD COLUMN is_closed TINYINT(1) DEFAULT 0 AFTER is_active;';
            }
            
            if (!$isClosedMigration) {
                $issues[] = 'Migracja is_closed nie została uruchomiona';
                $recommendations[] = 'Sprawdź czy plik migracji istnieje: database/migrations/*is_closed*.php';
            }
            
            if ($stages->count() === 0) {
                $issues[] = 'Brak etapów w tabeli';
                $recommendations[] = 'Uruchom seeder lub utwórz domyślne etapy';
            }
        @endphp

        @if(count($issues) > 0)
            <div class="diagnostic-box error">
                <strong>⚠️ Znaleziono {{ count($issues) }} problemów:</strong>
                <ul class="list-disc ml-6 mt-2">
                    @foreach($issues as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="diagnostic-box info mt-4">
                <strong>🔧 Zalecane działania:</strong>
                <ol class="list-decimal ml-6 mt-2">
                    @foreach($recommendations as $rec)
                        <li class="mb-1">{{ $rec }}</li>
                    @endforeach
                </ol>
            </div>
        @else
            <div class="diagnostic-box success">
                <strong>✅ Wszystko wygląda dobrze!</strong>
                <p class="mt-2">Nie znaleziono żadnych problemów z bazą danych CRM.</p>
            </div>
        @endif
    </div>

    <!-- FOOTER -->
    <div class="text-center text-gray-500 text-sm py-4">
        <p>ProximaLumine CRM Diagnostics v1.0</p>
        <p class="mt-1">Dostęp: <code>{{ url('/crm-diagnostics') }}</code></p>
    </div>
</div>

</body>
</html>
