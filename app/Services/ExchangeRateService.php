<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    /**
     * Get current EUR to PLN exchange rate from NBP API (cache for 12h)
     * @return float
     */
    public static function getEurPlnRate(): float
    {
        return Cache::remember('eur_pln_rate', 60 * 12, function () {
            try {
                $response = Http::timeout(5)->get('https://api.nbp.pl/api/exchangerates/rates/A/EUR/?format=json');
                if ($response->ok()) {
                    $data = $response->json();
                    return (float)($data['rates'][0]['mid'] ?? 4.5);
                }
            } catch (\Exception $e) {}
            return 4.5; // fallback
        });
    }
}
