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
                'name' => '3C Automation sp. z o. o.',
                'address' => 'Gliwicka 14',
                'city' => 'Kleszczów',
                'postal_code' => '44-167',
                'nip' => null,
                'phone' => null,
                'email' => 'biuro@3cautomation.eu',
                'logo' => null, // Logo będzie dodane przez użytkownika w ustawieniach
            ]);
            
            $this->command->info('Utworzono domyślne ustawienia firmy.');
        } else {
            $this->command->info('Ustawienia firmy już istnieją.');
        }
    }
}
