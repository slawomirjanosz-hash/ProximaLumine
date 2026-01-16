<?php

namespace App\Helpers;

use App\Models\Part;
use App\Services\ExchangeRateService;

class WarehouseHelper
{
    /**
     * Oblicz sumaryczną wartość magazynu w PLN oraz kurs EUR/PLN
     * @return array [value_pln, eur_pln_rate]
     */
    public static function getWarehouseValuePln(): array
    {
        $eurPln = ExchangeRateService::getEurPlnRate();
        $sum = 0;
        foreach (Part::all() as $part) {
            $price = (float)($part->net_price ?? 0);
            $qty = (float)($part->quantity ?? 0);
            if ($price <= 0 || $qty <= 0) continue;
            if (strtolower($part->currency) === 'eur' || strtolower($part->currency) === 'euro') {
                $sum += $price * $qty * $eurPln;
            } else {
                $sum += $price * $qty;
            }
        }
        return [round($sum,2), $eurPln];
    }
}
