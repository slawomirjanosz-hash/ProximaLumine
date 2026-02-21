<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Real Deal Update Test - Railway</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .box { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { border-left: 4px solid #16a34a; background: #dcfce7; }
        .error { border-left: 4px solid #dc2626; background: #fee2e2; }
        .warning { border-left: 4px solid #f59e0b; background: #fef3c7; }
        .info { border-left: 4px solid #3b82f6; background: #dbeafe; }
        pre { background: #1a1a1a; color: #4ade80; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        h1 { margin: 0 0 10px 0; }
        h2 { margin: 0 0 15px 0; }
        h3 { margin: 15px 0 10px 0; color: #dc2626; }
    </style>
</head>
<body>

<div class="container">
    <div class="box">
        <h1>🔥 CRM REAL Deal Update Test</h1>
        <p>Wykonuje PRAWDZIWY update na szansie z nowym etapem</p>
        <p style="color: #dc2626; margin: 10px 0 0 0;">⚠️ Ten test MODYFIKUJE dane w bazie!</p>
    </div>

    @php
        $testResults = [];
        $exception = null;
        $stackTrace = null;
        
        // Używamy DB i Model bezpośrednio, bez use statement (może to powodowało błąd)
    @endphp

    <!-- Znajdź testową szansę -->
    <div class="box">
        <h2>1️⃣ Znajdź testową szansę</h2>
        
        @php
            $testDeal = \App\Models\CrmDeal::orderBy('id', 'desc')->first();
            $testResults['deal_found'] = $testDeal ? true : false;
        @endphp

        @if($testDeal)
            <div class="box success">
                <strong>✅ Znaleziono szansę do testu: ID {{ $testDeal->id }}</strong>
            </div>
            <pre>{{ json_encode($testDeal->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @else
            <div class="box error">
                <strong>❌ Brak szans w bazie - utwórz jakąkolwiek szansę</strong>
            </div>
        @endif
    </div>

    @if($testDeal)
        <!-- Znajdź nowy etap (ID > 6) -->
        <div class="box">
            <h2>2️⃣ Znajdź NOWY etap (ID > 6)</h2>
            
            @php
                $newStage = DB::table('crm_stages')->where('id', '>', 6)->orderBy('id', 'desc')->first();
                $testResults['new_stage_found'] = $newStage ? true : false;
            @endphp

            @if($newStage)
                <div class="box success">
                    <strong>✅ Znaleziono nowy etap: {{ $newStage->name }} (slug: {{ $newStage->slug }})</strong>
                </div>
                <pre>{{ json_encode($newStage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @else
                <div class="box error">
                    <strong>❌ Brak nowych etapów (ID > 6)</strong>
                    <p style="margin-top: 10px;">Dodaj etap przez /crm-settings</p>
                </div>
            @endif
        </div>
    @endif

    @if($testDeal && $newStage)
        <!-- WYKONAJ PRAWDZIWY UPDATE -->
        <div class="box">
            <h2>3️⃣ WYKONAJ UPDATE (symulacja kontrolera)</h2>
            
            @php
                $updateSuccess = false;
                $originalStage = $testDeal->stage;
                
                try {
                    // Zapisz oryginalny stage
                    $testResults['original_stage'] = $originalStage;
                    $testResults['new_stage_slug'] = $newStage->slug;
                    
                    // Symuluj dokładnie to co robi updateDeal()
                    $validated = [
                        'name' => $testDeal->name,
                        'company_id' => $testDeal->company_id,
                        'value' => $testDeal->value,
                        'currency' => $testDeal->currency ?? 'PLN',
                        'stage' => $newStage->slug, // ZMIANA NA NOWY ETAP!
                        'probability' => $testDeal->probability,
                        'expected_close_date' => $testDeal->expected_close_date,
                        'actual_close_date' => $testDeal->actual_close_date,
                        'owner_id' => $testDeal->owner_id,
                        'description' => $testDeal->description,
                        'lost_reason' => $testDeal->lost_reason,
                    ];
                    
                    // Pobierz slugi etapów zamykających - DOKŁADNIE JAK W KONTROLERZE
                    if (\Schema::hasColumn('crm_stages', 'is_closed')) {
                        $closedStageSlugs = \DB::table('crm_stages')->where('is_closed', 1)->pluck('slug')->toArray();
                    } else {
                        $closedStageSlugs = ['wygrana', 'przegrana'];
                    }
                    
                    $testResults['closed_stage_slugs'] = $closedStageSlugs;
                    
                    // Logika actual_close_date - DOKŁADNIE JAK W KONTROLERZE
                    if (in_array($validated['stage'], $closedStageSlugs) && 
                        !in_array($testDeal->stage, $closedStageSlugs) && 
                        empty($validated['actual_close_date'])) {
                        $validated['actual_close_date'] = now();
                        $testResults['set_actual_close_date'] = true;
                    }
                    
                    if (!in_array($validated['stage'], $closedStageSlugs) && in_array($testDeal->stage, $closedStageSlugs)) {
                        $validated['actual_close_date'] = null;
                        $testResults['cleared_actual_close_date'] = true;
                    }
                    
                    $testResults['validated_data'] = $validated;
                    
                    // TUTAJ JEST KRYTYCZNA LINIA!
                    $testDeal->update($validated);
                    
                    $updateSuccess = true;
                    $testResults['update_success'] = true;
                    
                } catch (\Exception $e) {
                    $exception = $e;
                    $stackTrace = $e->getTraceAsString();
                    $testResults['update_success'] = false;
                    $testResults['exception_message'] = $e->getMessage();
                    $testResults['exception_class'] = get_class($e);
                    $testResults['exception_file'] = $e->getFile();
                    $testResults['exception_line'] = $e->getLine();
                }
            @endphp

            @if($updateSuccess)
                <div class="box success">
                    <strong>✅ UPDATE SUKCES!</strong>
                    <p style="margin-top: 10px;">Zmieniono stage z "{{ $originalStage }}" na "{{ $newStage->slug }}"</p>
                </div>
                
                <h3>Dane które zostały zapisane:</h3>
                <pre>{{ json_encode($testResults['validated_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                
                @php
                    // Odczytaj ponownie z bazy
                    $updatedDeal = \App\Models\CrmDeal::find($testDeal->id);
                @endphp
                
                <h3>Po UPDATE (odczytane z bazy):</h3>
                <pre>{{ json_encode($updatedDeal->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                
                @php
                    // PRZYWRÓĆ ORYGINALNY STAGE
                    try {
                        $testDeal->update(['stage' => $originalStage]);
                        $testResults['restored'] = true;
                    } catch (\Exception $e) {
                        $testResults['restored'] = false;
                        $testResults['restore_error'] = $e->getMessage();
                    }
                @endphp
                
                @if($testResults['restored'])
                    <div class="box info" style="margin-top: 15px;">
                        <strong>🔄 Stage został przywrócony do "{{ $originalStage }}"</strong>
                    </div>
                @endif
            @else
                <div class="box error">
                    <strong>❌ UPDATE FAILED - TO JEST TWÓJ BŁĄD 500!</strong>
                    <p style="margin-top: 10px; font-weight: bold; color: #dc2626;">{{ $testResults['exception_message'] ?? 'Unknown error' }}</p>
                </div>
                
                <h3>Exception Info:</h3>
                <div style="background: #fee2e2; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                    <p><strong>Class:</strong> {{ $testResults['exception_class'] ?? 'N/A' }}</p>
                    <p><strong>File:</strong> {{ $testResults['exception_file'] ?? 'N/A' }}</p>
                    <p><strong>Line:</strong> {{ $testResults['exception_line'] ?? 'N/A' }}</p>
                </div>
                
                @if($exception)
                    <h3>Full Exception:</h3>
                    <pre>{{ $exception }}</pre>
                    
                    <h3>Stack Trace:</h3>
                    <pre>{{ $stackTrace }}</pre>
                @endif
                
                <h3>Dane które próbowano zapisać:</h3>
                <pre>{{ json_encode($testResults['validated_data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @endif
        </div>

        <!-- Test Relationship assignedUsers() -->
        <div class="box">
            <h2>4️⃣ Test Relationship assignedUsers()</h2>
            
            @php
                $relationshipError = null;
                $assignedUsers = null;
                
                try {
                    $assignedUsers = $testDeal->assignedUsers;
                    $testResults['relationship_works'] = true;
                } catch (\Exception $e) {
                    $relationshipError = $e->getMessage();
                    $testResults['relationship_works'] = false;
                    $testResults['relationship_error'] = $relationshipError;
                }
            @endphp

            @if($testResults['relationship_works'])
                <div class="box success">
                    <strong>✅ Relationship assignedUsers() działa</strong>
                </div>
                <pre>{{ json_encode($assignedUsers->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @else
                <div class="box error">
                    <strong>❌ Relationship assignedUsers() FAILED</strong>
                    <p style="margin-top: 10px; font-weight: bold; color: #dc2626;">{{ $relationshipError ?? 'Unknown error' }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- PODSUMOWANIE -->
    <div class="box">
        <h2>📊 Podsumowanie</h2>
        <pre>{{ json_encode($testResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>

    <!-- REKOMENDACJA -->
    <div class="box" style="background: #1f2937; color: white;">
        <h2>💡 Co teraz?</h2>
        
        @if(isset($testResults['update_success']) && !$testResults['update_success'])
            <div style="margin-top: 15px;">
                <p style="color: #fca5a5; font-weight: bold; font-size: 18px;">🔥 ZNALEZIONO DOKŁADNĄ PRZYCZYNĘ BŁĘDU 500!</p>
                
                <p style="margin-top: 15px;"><strong>Exception:</strong></p>
                <p style="color: #fca5a5;">{{ $testResults['exception_message'] ?? 'N/A' }}</p>
                
                <p style="margin-top: 15px;"><strong>Klasa:</strong></p>
                <p style="color: #fde68a;">{{ $testResults['exception_class'] ?? 'N/A' }}</p>
                
                <p style="margin-top: 15px; color: #86efac;">✅ Prześlij mi CAŁY output tej strony (screenshot lub copy-paste)</p>
                <p style="color: #86efac;">✅ Szczególnie sekcję "Full Exception" i "Stack Trace"</p>
            </div>
        @elseif(isset($testResults['update_success']) && $testResults['update_success'])
            <div style="margin-top: 15px;">
                <p style="color: #86efac; font-weight: bold; font-size: 18px;">✅ UPDATE DZIAŁA!</p>
                <p style="margin-top: 10px;">Problem NIE jest w updateDeal() - sprawdź:</p>
                <p style="margin-top: 10px;">1. Cache Railway: php artisan config:clear && php artisan cache:clear</p>
                <p>2. Logi Railway podczas błędu 500</p>
                <p>3. CSRF token w formularzu</p>
            </div>
        @else
            <div style="margin-top: 15px;">
                <p style="color: #fde68a;">⚠️ Brak danych do testu</p>
                <p style="margin-top: 10px;">Utwórz szansę i dodaj etap (ID > 6) przez CRM UI</p>
            </div>
        @endif
    </div>
</div>

</body>
</html>
