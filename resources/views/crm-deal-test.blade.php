<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Deal Update Test - Railway</title>
    @vite(['resources/css/app.css'])
    <style>
        .success { @apply bg-green-100 border-green-500 text-green-900; }
        .error { @apply bg-red-100 border-red-500 text-red-900; }
        .warning { @apply bg-yellow-100 border-yellow-500 text-yellow-900; }
        .info { @apply bg-blue-100 border-blue-500 text-blue-900; }
        .box { @apply border-l-4 p-4 mb-4 rounded; }
        pre { @apply bg-gray-900 text-green-400 p-4 rounded overflow-x-auto text-xs font-mono; }
    </style>
</head>
<body class="bg-gray-50 p-6">

<div class="max-w-6xl mx-auto">
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🧪 CRM Deal Update Test</h1>
        <p class="text-gray-600">Symulacja updateDeal() z dokładną walidacją</p>
    </div>

    <!-- Sprawdź jakie etapy są dostępne -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">📋 Dostępne etapy CRM</h2>
        
        @php
            $stages = DB::table('crm_stages')->orderBy('order')->get();
        @endphp

        <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Name</th>
                    <th class="border p-2">Slug</th>
                    <th class="border p-2">is_closed</th>
                    <th class="border p-2">is_active</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stages as $stage)
                    <tr>
                        <td class="border p-2 text-center">{{ $stage->id }}</td>
                        <td class="border p-2">{{ $stage->name }}</td>
                        <td class="border p-2 font-mono">{{ $stage->slug }}</td>
                        <td class="border p-2 text-center">{{ $stage->is_closed ? '✅' : '❌' }}</td>
                        <td class="border p-2 text-center">{{ $stage->is_active ? '✅' : '❌' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Test walidacji różnych slugów -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🔍 Test Walidacji Slug</h2>
        
        @php
            $testSlugs = [
                'rezygnacja',
                'Rezygnacja',
                'wygrana',
                'przegrana',
                'nowy_lead',
                'nieistniejacy_etap_xyz',
            ];
            
            $validationResults = [];
            
            foreach ($testSlugs as $slug) {
                $exists = DB::table('crm_stages')->where('slug', $slug)->exists();
                $validationResults[$slug] = $exists;
            }
        @endphp

        <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">Test Slug</th>
                    <th class="border p-2">exists:crm_stages,slug</th>
                    <th class="border p-2">Status Walidacji</th>
                </tr>
            </thead>
            <tbody>
                @foreach($validationResults as $slug => $exists)
                    <tr>
                        <td class="border p-2 font-mono">{{ $slug }}</td>
                        <td class="border p-2 text-center">{{ $exists ? '✅ ISTNIEJE' : '❌ NIE ISTNIEJE' }}</td>
                        <td class="border p-2 text-center">
                            @if($exists)
                                <span class="bg-green-500 text-white px-2 py-1 rounded text-xs">PASS</span>
                            @else
                                <span class="bg-red-500 text-white px-2 py-1 rounded text-xs">FAIL</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Symulacja Validation updateDeal() -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">⚙️ Symulacja Validation updateDeal()</h2>
        
        @php
            $testStage = 'rezygnacja'; // Slug który użytkownik próbuje użyć
            $validationError = null;
            $validationPassed = false;
            
            // Dokładnie taka sama walidacja jak w kontrolerze (linia 5752)
            $testData = [
                'name' => 'Test Szansa',
                'company_id' => null,
                'value' => 10000,
                'currency' => 'PLN',
                'stage' => $testStage,
                'probability' => 50,
                'expected_close_date' => null,
                'actual_close_date' => null,
                'owner_id' => null,
                'description' => 'Test',
                'lost_reason' => null,
            ];
            
            $rules = [
                'name' => 'required|string|max:255',
                'company_id' => 'nullable|exists:crm_companies,id',
                'value' => 'required|numeric|min:0',
                'currency' => 'nullable|string|max:10',
                'stage' => 'required|exists:crm_stages,slug',
                'probability' => 'required|integer|min:0|max:100',
                'expected_close_date' => 'nullable|date',
                'actual_close_date' => 'nullable|date',
                'owner_id' => 'nullable|exists:users,id',
                'description' => 'nullable|string',
                'lost_reason' => 'nullable|string',
            ];
            
            try {
                $validator = Validator::make($testData, $rules);
                
                if ($validator->fails()) {
                    $validationError = $validator->errors()->toArray();
                } else {
                    $validationPassed = true;
                }
            } catch (\Exception $e) {
                $validationError = ['exception' => $e->getMessage()];
            }
        @endphp

        <div class="mb-4">
            <strong>Testowany stage slug:</strong> <code class="bg-gray-200 px-2 py-1 rounded">{{ $testStage }}</code>
        </div>

        @if($validationPassed)
            <div class="box success">
                <strong>✅ WALIDACJA PASSED</strong>
                <p class="mt-2">Slug "{{ $testStage }}" przeszedł walidację</p>
            </div>
        @else
            <div class="box error">
                <strong>❌ WALIDACJA FAILED</strong>
                <p class="mt-2 font-bold text-red-700">To jest DOKŁADNA przyczyna błędu 500!</p>
            </div>
            
            <h3 class="font-bold mb-2 text-red-600">Błędy walidacji:</h3>
            <pre>{{ json_encode($validationError, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @endif
    </div>

    <!-- Test logiki is_closed -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">🔒 Test Logiki is_closed</h2>
        
        @php
            $hasIsClosedColumn = Schema::hasColumn('crm_stages', 'is_closed');
            $closedStageSlugs = [];
            
            if ($hasIsClosedColumn) {
                $closedStageSlugs = DB::table('crm_stages')->where('is_closed', 1)->pluck('slug')->toArray();
            } else {
                $closedStageSlugs = ['wygrana', 'przegrana'];
            }
        @endphp

        <div class="mb-4">
            <strong>Schema::hasColumn('crm_stages', 'is_closed'):</strong> 
            <span class="ml-2">{{ $hasIsClosedColumn ? '✅ TRUE' : '❌ FALSE' }}</span>
        </div>

        <div class="mb-4">
            <strong>Etapy zamykające lejek ($closedStageSlugs):</strong>
            <pre>{{ json_encode($closedStageSlugs, JSON_PRETTY_PRINT) }}</pre>
        </div>

        @if(in_array($testStage, $closedStageSlugs))
            <div class="box info">
                ℹ️ Stage "{{ $testStage }}" jest etapem zamykającym lejek - zostanie ustawione actual_close_date
            </div>
        @else
            <div class="box warning">
                ⚠️ Stage "{{ $testStage }}" NIE jest etapem zamykającym lejek
            </div>
        @endif
    </div>

    <!-- REKOMENDACJA -->
    <div class="bg-gray-800 text-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">💡 Rozwiązanie</h2>
        
        @if(!$validationPassed && isset($validationError['stage']))
            <div class="space-y-2 text-sm">
                <p class="text-red-300 font-bold">❌ Stage "{{ $testStage }}" NIE ISTNIEJE w bazie!</p>
                
                <p class="mt-4"><strong>Dodaj brakujący etap:</strong></p>
                <pre class="bg-gray-900 p-3 rounded mt-2 text-green-400">INSERT INTO crm_stages (name, slug, color, `order`, is_active, is_closed, created_at, updated_at)
VALUES ('Rezygnacja', 'rezygnacja', '#dc2626', 7, 1, 1, NOW(), NOW());</pre>

                <p class="mt-4"><strong>Lub przez UI:</strong></p>
                <p>1. Otwórz: <a href="/crm-settings" class="text-blue-300 underline">CRM Settings</a></p>
                <p>2. Dodaj etap: nazwa="Rezygnacja", slug="rezygnacja", ✅ zaznacz "Zakończenie Lejka"</p>
            </div>
        @elseif($validationPassed)
            <div class="space-y-2 text-sm">
                <p class="text-green-300 font-bold">✅ Wszystko OK! Walidacja przeszła.</p>
                <p>Jeśli nadal masz błąd 500, problem jest gdzie indziej.</p>
                <p class="mt-4">Sprawdź logi Railway:</p>
                <pre class="bg-gray-900 p-3 rounded mt-2 text-green-400">railway logs</pre>
            </div>
        @endif
    </div>
</div>

</body>
</html>
