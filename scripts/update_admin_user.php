<?php
// Skrypt do aktualizacji użytkownika admin na Railway
// Uruchom: php scripts/update_admin_user.php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Aktualizacja użytkownika admin ===\n\n";

// Sprawdź czy istnieje użytkownik slawomir.janosz@gmail.com
$oldUser = User::where('email', 'slawomir.janosz@gmail.com')->first();

if ($oldUser) {
    echo "Znaleziono użytkownika slawomir.janosz@gmail.com\n";
    echo "Aktualizacja na proximalumine@gmail.com...\n";
    
    $oldUser->email = 'proximalumine@gmail.com';
    $oldUser->name = 'ProximaLumine';
    $oldUser->first_name = 'Proxima';
    $oldUser->last_name = 'Lumine';
    $oldUser->short_name = 'ProLum';
    $oldUser->password = Hash::make('Lumine1!');
    $oldUser->is_admin = true;
    $oldUser->can_view_catalog = true;
    $oldUser->can_add = true;
    $oldUser->can_remove = true;
    $oldUser->can_orders = true;
    $oldUser->can_settings = true;
    $oldUser->can_settings_categories = true;
    $oldUser->can_settings_suppliers = true;
    $oldUser->can_settings_company = true;
    $oldUser->can_settings_users = true;
    $oldUser->can_settings_export = true;
    $oldUser->can_settings_other = true;
    $oldUser->can_delete_orders = true;
    $oldUser->show_action_column = true;
    $oldUser->save();
    
    echo "✓ Użytkownik zaktualizowany!\n";
} else {
    echo "Nie znaleziono użytkownika slawomir.janosz@gmail.com\n";
}

// Sprawdź czy istnieje użytkownik proximalumine@gmail.com
$newUser = User::where('email', 'proximalumine@gmail.com')->first();

if (!$newUser) {
    echo "Tworzenie użytkownika proximalumine@gmail.com...\n";
    
    User::create([
        'email' => 'proximalumine@gmail.com',
        'name' => 'ProximaLumine',
        'first_name' => 'Proxima',
        'last_name' => 'Lumine',
        'short_name' => 'ProLum',
        'password' => Hash::make('Lumine1!'),
        'is_admin' => true,
        'can_view_catalog' => true,
        'can_add' => true,
        'can_remove' => true,
        'can_orders' => true,
        'can_settings' => true,
        'can_settings_categories' => true,
        'can_settings_suppliers' => true,
        'can_settings_company' => true,
        'can_settings_users' => true,
        'can_settings_export' => true,
        'can_settings_other' => true,
        'can_delete_orders' => true,
        'show_action_column' => true,
    ]);
    
    echo "✓ Użytkownik utworzony!\n";
} else {
    echo "✓ Użytkownik proximalumine@gmail.com już istnieje\n";
}

echo "\n=== Aktualizacja zakończona ===\n";
echo "Login: proximalumine@gmail.com\n";
echo "Hasło: Lumine1!\n";
