<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Live Test - Railway</title>
    @vite(['resources/css/app.css'])
    <style>
        .success { @apply bg-green-100 border-green-500 text-green-900; }
        .error { @apply bg-red-100 border-red-500 text-red-900; }
        .warning { @apply bg-yellow-100 border-yellow-500 text-yellow-900; }
        .info { @apply bg-blue-100 border-blue-500 text-blue-900; }
        .box { @apply border-l-4 p-4 mb-4 rounded; }
        pre { @apply bg-gray-900 text-green-400 p-4 rounded overflow-x-auto text-xs font-mono; }
        .badge-success { @apply bg-green-500 text-white px-2 py-1 rounded text-xs font-bold; }
        .badge-error { @apply bg-red-500 text-white px-2 py-1 rounded text-xs font-bold; }
    </style>
</head>
<body class="bg-gray-50 p-6">

<div class="max-w-6xl mx-auto">
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🧪 CRM Live Test - Railway</h1>
        <p class="text-gray-600">Test INSERT i UPDATE etapów CRM z pełnym logowaniem błędów</p>
        <p class="text-sm text-gray-500 mt-2">Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <!-- TEST 1: INSERT NOWEGO ETAPU -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🔨 Test 1: INSERT nowego etapu</h2>
        
        @php
            $insertSuccess = false;
            $insertError = null;
            $insertException = null;
            $insertId = null;
            $insertData = null;
            
            try {
                $testSlug = 'live_test_' . time();
                
                $insertData = [
                    'name' => 'Live Test Etap',
                    'slug' => $testSlug,
                    'color' => '#00ff00',
                    'order' => 999,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Dodaj is_closed jeśli kolumna istnieje
                if (Schema::hasColumn('crm_stages', 'is_closed')) {
                    $insertData['is_closed'] = 1;
                }
                
                // WYKONAJ INSERT NA ŻYWO
                $insertId = DB::table('crm_stages')->insertGetId($insertData);
                $insertSuccess = true;
                
            } catch (\Exception $e) {
                $insertError = $e->getMessage();
                $insertException = $e;
            }
        @endphp

        @if($insertSuccess)
            <div class="box success">
                <strong>✅ INSERT SUKCES!</strong>
                <p class="mt-2">Wstawiono rekord ID: <strong>{{ $insertId }}</strong></p>
            </div>
            
            <h3 class="font-bold mb-2">Wstawione dane:</h3>
            <pre>{{ json_encode($insertData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            
            @php
                // Odczytaj wstawiony rekord
                $inserted = DB::table('crm_stages')->where('id', $insertId)->first();
            @endphp
            
            <h3 class="font-bold mt-4 mb-2">Odczytane z bazy:</h3>
            <pre>{{ json_encode($inserted, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @else
            <div class="box error">
                <strong>❌ INSERT FAILED!</strong>
                <p class="mt-2 font-bold text-red-700">{{ $insertError }}</p>
            </div>
            
            @if($insertException)
                <h3 class="font-bold mb-2 text-red-600">Exception Details:</h3>
                <pre>{{ $insertException }}</pre>
                
                <h3 class="font-bold mt-4 mb-2 text-red-600">Stack Trace:</h3>
                <pre>{{ $insertException->getTraceAsString() }}</pre>
            @endif
            
            <h3 class="font-bold mt-4 mb-2">Dane które próbowano wstawić:</h3>
            <pre>{{ json_encode($insertData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @endif
    </div>

    <!-- TEST 2: UPDATE ISTNIEJĄCEGO ETAPU -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">✏️ Test 2: UPDATE istniejącego etapu</h2>
        
        @php
            $updateSuccess = false;
            $updateError = null;
            $updateException = null;
            $updateData = null;
            $testStageId = null;
            
            // Znajdź pierwszy etap który nie jest domyślny
            $testStage = DB::table('crm_stages')
                ->whereNotIn('slug', ['wygrana', 'przegrana'])
                ->orderBy('id')
                ->first();
            
            if ($testStage) {
                $testStageId = $testStage->id;
                
                try {
                    $updateData = [
                        'name' => $testStage->name . ' (Updated)',
                        'color' => '#ff00ff',
                        'order' => $testStage->order,
                        'is_active' => 1,
                        'updated_at' => now(),
                    ];
                    
                    // Dodaj is_closed jeśli kolumna istnieje
                    if (Schema::hasColumn('crm_stages', 'is_closed')) {
                        $updateData['is_closed'] = 0;
                    }
                    
                    // WYKONAJ UPDATE NA ŻYWO
                    DB::table('crm_stages')
                        ->where('id', $testStageId)
                        ->update($updateData);
                    
                    $updateSuccess = true;
                    
                } catch (\Exception $e) {
                    $updateError = $e->getMessage();
                    $updateException = $e;
                }
            }
        @endphp

        @if(!$testStage)
            <div class="box warning">
                <strong>⚠️ Brak etapu do testu UPDATE</strong>
                <p class="mt-2">Wszystkie etapy to domyślne (wygrana/przegrana)</p>
            </div>
        @elseif($updateSuccess)
            <div class="box success">
                <strong>✅ UPDATE SUKCES!</strong>
                <p class="mt-2">Zaktualizowano etap ID: <strong>{{ $testStageId }}</strong></p>
            </div>
            
            <h3 class="font-bold mb-2">Dane UPDATE:</h3>
            <pre>{{ json_encode($updateData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            
            @php
                $updated = DB::table('crm_stages')->where('id', $testStageId)->first();
            @endphp
            
            <h3 class="font-bold mt-4 mb-2">Po UPDATE:</h3>
            <pre>{{ json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @else
            <div class="box error">
                <strong>❌ UPDATE FAILED!</strong>
                <p class="mt-2 font-bold text-red-700">{{ $updateError }}</p>
            </div>
            
            @if($updateException)
                <h3 class="font-bold mb-2 text-red-600">Exception Details:</h3>
                <pre>{{ $updateException }}</pre>
                
                <h3 class="font-bold mt-4 mb-2 text-red-600">Stack Trace:</h3>
                <pre>{{ $updateException->getTraceAsString() }}</pre>
            @endif
        @endif
    </div>

    <!-- TEST 3: SYMULACJA KONTROLERA addCrmStage -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🎯 Test 3: Symulacja kontrolera addCrmStage()</h2>
        
        @php
            $controllerSuccess = false;
            $controllerError = null;
            $controllerException = null;
            $controllerInsertId = null;
            
            try {
                // Symuluj dane z formularza
                $formData = [
                    'name' => 'Test Kontroler Etap',
                    'slug' => 'test_kontroler_' . time(),
                    'color' => '#0000ff',
                    'order' => 998,
                    'is_closed' => 1, // Zaznaczony checkbox
                ];
                
                // Dokładnie taki sam kod jak w kontrolerze
                $insertData = [
                    'name' => $formData['name'],
                    'slug' => $formData['slug'],
                    'color' => $formData['color'],
                    'order' => $formData['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Dodaj is_closed tylko jeśli kolumna istnieje
                if (Schema::hasColumn('crm_stages', 'is_closed')) {
                    $insertData['is_closed'] = isset($formData['is_closed']) ? 1 : 0;
                }
                
                $controllerInsertId = DB::table('crm_stages')->insertGetId($insertData);
                $controllerSuccess = true;
                
            } catch (\Exception $e) {
                $controllerError = $e->getMessage();
                $controllerException = $e;
            }
        @endphp

        @if($controllerSuccess)
            <div class="box success">
                <strong>✅ KONTROLER SYMULACJA SUKCES!</strong>
                <p class="mt-2">Dodano etap ID: <strong>{{ $controllerInsertId }}</strong></p>
            </div>
            
            @php
                $controllerInserted = DB::table('crm_stages')->where('id', $controllerInsertId)->first();
            @endphp
            
            <h3 class="font-bold mb-2">Wstawiony rekord:</h3>
            <pre>{{ json_encode($controllerInserted, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @else
            <div class="box error">
                <strong>❌ KONTROLER SYMULACJA FAILED!</strong>
                <p class="mt-2 font-bold text-red-700">{{ $controllerError }}</p>
            </div>
            
            @if($controllerException)
                <h3 class="font-bold mb-2 text-red-600">Exception Details:</h3>
                <pre>{{ $controllerException }}</pre>
                
                <h3 class="font-bold mt-4 mb-2 text-red-600">Stack Trace:</h3>
                <pre>{{ $controllerException->getTraceAsString() }}</pre>
            @endif
        @endif
    </div>

    <!-- TEST 4: WALIDACJA KONTROLERA -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🔍 Test 4: Walidacja Request</h2>
        
        @php
            $validationSuccess = false;
            $validationError = null;
            $validationException = null;
            
            try {
                $testData = [
                    'name' => 'Test Walidacja',
                    'slug' => 'test_walidacja_' . time(),
                    'color' => '#ff0000',
                    'order' => 997,
                    'is_closed' => true,
                ];
                
                // Symuluj walidację z kontrolera
                $rules = [
                    'name' => 'required|string|max:255',
                    'slug' => 'required|string|max:255|unique:crm_stages',
                    'color' => 'nullable|string|max:50',
                    'order' => 'required|integer|min:0',
                    'is_closed' => 'nullable|boolean',
                ];
                
                $validator = Validator::make($testData, $rules);
                
                if ($validator->fails()) {
                    $validationError = $validator->errors()->toJson();
                } else {
                    $validationSuccess = true;
                }
                
            } catch (\Exception $e) {
                $validationError = $e->getMessage();
                $validationException = $e;
            }
        @endphp

        @if($validationSuccess)
            <div class="box success">
                <strong>✅ WALIDACJA PASSED</strong>
            </div>
            <h3 class="font-bold mb-2">Zwalidowane dane:</h3>
            <pre>{{ json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @else
            <div class="box error">
                <strong>❌ WALIDACJA FAILED!</strong>
                <p class="mt-2 font-bold text-red-700">{{ $validationError }}</p>
            </div>
            
            @if($validationException)
                <h3 class="font-bold mb-2 text-red-600">Exception:</h3>
                <pre>{{ $validationException }}</pre>
            @endif
        @endif
    </div>

    <!-- PODSUMOWANIE -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">📊 Podsumowanie Testów</h2>
        
        <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">Test</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2 text-left">Błąd</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border p-2">INSERT nowego etapu</td>
                    <td class="border p-2 text-center">
                        @if($insertSuccess)
                            <span class="badge-success">SUKCES</span>
                        @else
                            <span class="badge-error">FAILED</span>
                        @endif
                    </td>
                    <td class="border p-2 text-xs">{{ $insertError ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="border p-2">UPDATE etapu</td>
                    <td class="border p-2 text-center">
                        @if($updateSuccess)
                            <span class="badge-success">SUKCES</span>
                        @elseif(!$testStage)
                            <span class="badge-warning">SKIP</span>
                        @else
                            <span class="badge-error">FAILED</span>
                        @endif
                    </td>
                    <td class="border p-2 text-xs">{{ $updateError ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="border p-2">Symulacja kontrolera</td>
                    <td class="border p-2 text-center">
                        @if($controllerSuccess)
                            <span class="badge-success">SUKCES</span>
                        @else
                            <span class="badge-error">FAILED</span>
                        @endif
                    </td>
                    <td class="border p-2 text-xs">{{ $controllerError ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="border p-2">Walidacja</td>
                    <td class="border p-2 text-center">
                        @if($validationSuccess)
                            <span class="badge-success">PASSED</span>
                        @else
                            <span class="badge-error">FAILED</span>
                        @endif
                    </td>
                    <td class="border p-2 text-xs">{{ $validationError ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- CLEANUP -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🧹 Cleanup</h2>
        
        @php
            $cleanupIds = [];
            $cleanupCount = 0;
            
            if ($insertSuccess && $insertId) {
                $cleanupIds[] = $insertId;
            }
            if ($controllerSuccess && $controllerInsertId) {
                $cleanupIds[] = $controllerInsertId;
            }
            
            if (count($cleanupIds) > 0) {
                $cleanupCount = DB::table('crm_stages')
                    ->whereIn('id', $cleanupIds)
                    ->delete();
            }
        @endphp

        @if($cleanupCount > 0)
            <div class="box success">
                <strong>✓ Usunięto {{ $cleanupCount }} testowych etapów</strong>
                <p class="mt-2 text-sm">IDs: {{ implode(', ', $cleanupIds) }}</p>
            </div>
        @else
            <div class="box info">
                <strong>Brak testowych etapów do usunięcia</strong>
            </div>
        @endif
    </div>

    <!-- INSTRUKCJE -->
    <div class="bg-gray-800 text-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">💡 Co dalej?</h2>
        
        <div class="space-y-2 text-sm">
            <p><strong>Jeśli wszystkie testy SUKCES:</strong></p>
            <p>→ Problem nie jest w bazie ani w operacjach DB</p>
            <p>→ Sprawdź logi Railway podczas dodawania etapu przez UI</p>
            <p>→ Może być problem z route, middleware, lub CSRF</p>
            
            <p class="mt-4"><strong>Jeśli jakiś test FAILED:</strong></p>
            <p>→ Zobacz dokładny błąd i stack trace powyżej</p>
            <p>→ To jest DOKŁADNA przyczyna błędu 500</p>
            
            <p class="mt-4"><strong>Następny krok:</strong></p>
            <p>→ Prześlij mi screenshot tej strony</p>
            <p>→ Szczególnie sekcję która failuje</p>
        </div>
    </div>
</div>

</body>
</html>
