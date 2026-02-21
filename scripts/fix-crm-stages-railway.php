<?php

/**
 * Skrypt naprawczy dla Railway - dodaje kolumnę is_closed do crm_stages
 * 
 * Uruchom na Railway:
 * php scripts/fix-crm-stages-railway.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Naprawa tabeli crm_stages ===\n\n";

try {
    // Sprawdź czy kolumna is_closed istnieje
    $hasColumn = Schema::hasColumn('crm_stages', 'is_closed');
    
    if (!$hasColumn) {
        echo "Kolumna 'is_closed' nie istnieje. Dodawanie...\n";
        
        // Dodaj kolumnę
        Schema::table('crm_stages', function ($table) {
            $table->boolean('is_closed')->default(false)->after('is_active');
        });
        
        echo "✓ Kolumna 'is_closed' została dodana.\n\n";
        
        // Ustaw is_closed = 1 dla domyślnych etapów
        $updated = DB::table('crm_stages')
            ->whereIn('slug', ['wygrana', 'przegrana'])
            ->update(['is_closed' => 1]);
        
        echo "✓ Zaktualizowano {$updated} domyślnych etapów (wygrana, przegrana).\n";
    } else {
        echo "✓ Kolumna 'is_closed' już istnieje.\n\n";
        
        // Sprawdź czy domyślne etapy mają is_closed = 1
        $needsUpdate = DB::table('crm_stages')
            ->whereIn('slug', ['wygrana', 'przegrana'])
            ->where('is_closed', 0)
            ->count();
        
        if ($needsUpdate > 0) {
            echo "Aktualizacja domyślnych etapów...\n";
            $updated = DB::table('crm_stages')
                ->whereIn('slug', ['wygrana', 'przegrana'])
                ->update(['is_closed' => 1]);
            echo "✓ Zaktualizowano {$updated} etapów.\n";
        } else {
            echo "✓ Domyślne etapy są już poprawnie skonfigurowane.\n";
        }
    }
    
    echo "\n=== Aktualny stan tabeli crm_stages ===\n\n";
    $stages = DB::table('crm_stages')->orderBy('order')->get();
    
    foreach ($stages as $stage) {
        $isClosed = isset($stage->is_closed) ? ($stage->is_closed ? 'TAK' : 'NIE') : 'BRAK';
        $isActive = $stage->is_active ? 'TAK' : 'NIE';
        echo sprintf(
            "ID: %d | %s (%s) | Kolejność: %d | Aktywny: %s | Zakończenie: %s\n",
            $stage->id,
            $stage->name,
            $stage->slug,
            $stage->order,
            $isActive,
            $isClosed
        );
    }
    
    echo "\n✓ Naprawa zakończona pomyślnie!\n";
    
} catch (Exception $e) {
    echo "\n✗ BŁĄD: " . $e->getMessage() . "\n";
    echo "\nStos wywołań:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
