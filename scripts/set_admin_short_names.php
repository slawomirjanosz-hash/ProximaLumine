<?php
// Skrypt do ustawiania short_name dla adminów
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';

// proximalumine@gmail.com -> ProLum
$admin1 = User::where('email', 'proximalumine@gmail.com')->first();
if ($admin1) {
    $admin1->short_name = 'ProLum';
    $admin1->save();
    echo "Zmieniono short_name na ProLum dla proximalumine@gmail.com\n";
} else {
    echo "Nie znaleziono admina proximalumine@gmail.com\n";
}

// Admin@admin.com -> Adm
$admin2 = User::where('email', 'Admin@admin.com')->first();
if ($admin2) {
    $admin2->short_name = 'Adm';
    $admin2->save();
    echo "Zmieniono short_name na Adm dla Admin@admin.com\n";
} else {
    echo "Nie znaleziono admina Admin@admin.com\n";
}
