<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanySetting;

class CompanySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sprawdź czy istnieją ustawienia firmy
        $existing = CompanySetting::first();
        
        if (!$existing) {
            // Utwórz domyślne ustawienia firmy
            CompanySetting::create([
                'name' => 'Moja Firma',
                'address' => 'Słoneczna',
                'city' => 'Warszawa',
                'postal_code' => '40-100',
                'nip' => null,
                'phone' => null,
                'email' => 'test@example.com',
                'logo' => null, // Logo będzie dodane przez użytkownika w ustawieniach
            ]);
            
            $this->command->info('Utworzono domyślne ustawienia firmy.');
        } else {
            $this->command->info('Ustawienia firmy już istnieją.');
        }
    }
}
