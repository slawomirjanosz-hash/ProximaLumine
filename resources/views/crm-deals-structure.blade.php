<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>CRM Deals Table Structure - Railway</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; }
        .box { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .error { border-left: 4px solid #dc2626; background: #fee2e2; }
        pre { background: #1a1a1a; color: #4ade80; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 13px; }
        th { background: #f3f4f6; font-weight: bold; }
        .highlight { background: #fef3c7; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="box">
        <h1>🔍 CRM Deals Table Structure</h1>
        <p>Sprawdzenie struktury kolumny 'stage' w tabeli crm_deals</p>
    </div>

    <!-- Struktura tabeli crm_deals -->
    <div class="box">
        <h2>📋 DESCRIBE crm_deals</h2>
        
        @php
            $columns = DB::select('DESCRIBE crm_deals');
        @endphp

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
                    <tr class="{{ $col->Field === 'stage' ? 'highlight' : '' }}">
                        <td><strong>{{ $col->Field }}</strong></td>
                        <td><code>{{ $col->Type }}</code></td>
                        <td>{{ $col->Null }}</td>
                        <td>{{ $col->Key }}</td>
                        <td>{{ $col->Default ?? 'NULL' }}</td>
                        <td>{{ $col->Extra }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $stageColumn = collect($columns)->firstWhere('Field', 'stage');
        @endphp

        @if($stageColumn)
            <div class="box error" style="margin-top: 20px;">
                <h3>🔥 Kolumna 'stage' w crm_deals:</h3>
                <pre>{{ json_encode($stageColumn, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                
                <h3 style="margin-top: 15px;">Typ kolumny:</h3>
                <p style="font-size: 18px; font-weight: bold; color: #dc2626;">{{ $stageColumn->Type }}</p>
                
                @if(str_contains(strtoupper($stageColumn->Type), 'ENUM'))
                    <p style="margin-top: 10px; color: #dc2626; font-weight: bold;">
                        ⚠️ PROBLEM: Kolumna jest ENUM - może zawierać tylko hardcodowane wartości!
                    </p>
                @elseif(str_contains(strtoupper($stageColumn->Type), 'VARCHAR'))
                    @php
                        preg_match('/\((\d+)\)/', $stageColumn->Type, $matches);
                        $length = $matches[1] ?? 'unknown';
                    @endphp
                    <p style="margin-top: 10px;">
                        Długość VARCHAR: <strong>{{ $length }}</strong> znaków
                    </p>
                    @if($length < 50)
                        <p style="color: #dc2626; font-weight: bold;">
                            ⚠️ PROBLEM: VARCHAR za krótki dla niektórych slugów!
                        </p>
                    @endif
                @endif
            </div>
        @endif
    </div>

    <!-- SHOW CREATE TABLE -->
    <div class="box">
        <h2>🔨 SHOW CREATE TABLE crm_deals</h2>
        
        @php
            $createTable = DB::select('SHOW CREATE TABLE crm_deals');
            $createStatement = $createTable[0]->{'Create Table'} ?? 'N/A';
        @endphp

        <pre>{{ $createStatement }}</pre>
    </div>

    <!-- Sprawdź migracje -->
    <div class="box">
        <h2>📦 Migracje tabeli crm_deals</h2>
        
        @php
            $migrations = DB::table('migrations')
                ->where('migration', 'LIKE', '%crm_deal%')
                ->orderBy('batch')
                ->get();
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Migration</th>
                    <th>Batch</th>
                </tr>
            </thead>
            <tbody>
                @foreach($migrations as $migration)
                    <tr>
                        <td>{{ $migration->migration }}</td>
                        <td>{{ $migration->batch }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Test stage values -->
    <div class="box">
        <h2>🧪 Test zapisywania różnych wartości 'stage'</h2>
        
        @php
            $testValues = ['test', 'wygrana', 'przegrana', 'nowy_lead', 'bardzo_dlugi_slug_testowy_123456'];
            $testResults = [];
            
            foreach ($testValues as $value) {
                try {
                    // Próba update bez wykonania (za pomocą dry-run)
                    DB::connection()->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                    $stmt = DB::connection()->getPdo()->prepare('UPDATE crm_deals SET stage = ? WHERE id = 99999999');
                    $stmt->bindValue(1, $value, \PDO::PARAM_STR);
                    // Nie wykonujemy, tylko bindujemy
                    $testResults[$value] = [
                        'length' => strlen($value),
                        'can_bind' => true,
                        'error' => null
                    ];
                } catch (\Exception $e) {
                    $testResults[$value] = [
                        'length' => strlen($value),
                        'can_bind' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Test Value</th>
                    <th>Length</th>
                    <th>Can Bind?</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testResults as $value => $result)
                    <tr>
                        <td><code>{{ $value }}</code></td>
                        <td>{{ $result['length'] }}</td>
                        <td>{{ $result['can_bind'] ? '✅' : '❌' }}</td>
                        <td style="font-size: 11px;">{{ $result['error'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ROZWIĄZANIE -->
    <div class="box" style="background: #1f2937; color: white;">
        <h2>💡 Rozwiązanie</h2>
        
        @if($stageColumn && str_contains(strtoupper($stageColumn->Type), 'ENUM'))
            <div style="margin-top: 15px;">
                <p style="color: #fca5a5; font-weight: bold; font-size: 18px;">🔥 Kolumna 'stage' jest ENUM!</p>
                
                <p style="margin-top: 15px;"><strong>Musisz zmienić typ kolumny na VARCHAR(255):</strong></p>
                <pre style="background: #4ade80; color: #1a1a1a; padding: 10px; border-radius: 4px; margin-top: 10px;">-- Railway Console SQL
ALTER TABLE crm_deals MODIFY COLUMN stage VARCHAR(255) NOT NULL;</pre>

                <p style="margin-top: 15px;"><strong>Lub utwórz migrację Laravel:</strong></p>
                <pre style="background: #4ade80; color: #1a1a1a; padding: 10px; border-radius: 4px; margin-top: 10px;">php artisan make:migration change_stage_column_type_in_crm_deals_table

// W migracji:
Schema::table('crm_deals', function (Blueprint $table) {
    $table->string('stage', 255)->change();
});</pre>
            </div>
        @elseif($stageColumn && str_contains(strtoupper($stageColumn->Type), 'VARCHAR'))
            @php
                preg_match('/\((\d+)\)/', $stageColumn->Type, $matches);
                $length = $matches[1] ?? 'unknown';
            @endphp
            
            @if($length < 50)
                <div style="margin-top: 15px;">
                    <p style="color: #fca5a5; font-weight: bold; font-size: 18px;">🔥 VARCHAR za krótki ({{ $length }})!</p>
                    
                    <p style="margin-top: 15px;"><strong>Zwiększ długość do VARCHAR(255):</strong></p>
                    <pre style="background: #4ade80; color: #1a1a1a; padding: 10px; border-radius: 4px; margin-top: 10px;">ALTER TABLE crm_deals MODIFY COLUMN stage VARCHAR(255) NOT NULL;</pre>
                </div>
            @else
                <p style="color: #86efac;">✅ Typ kolumny wygląda OK (VARCHAR {{ $length }})</p>
                <p style="margin-top: 10px;">Problem może być gdzie indziej - sprawdź logi Railway</p>
            @endif
        @endif
    </div>
</div>

</body>
</html>
