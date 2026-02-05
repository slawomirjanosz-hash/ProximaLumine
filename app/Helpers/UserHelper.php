<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class UserHelper
{
    /**
     * Zwraca liczbę aktualnie zalogowanych użytkowników (aktywnych w ciągu ostatnich 10 minut)
     */
    public static function getOnlineUsersCount($minutes = 10)
    {
        $threshold = now()->subMinutes($minutes)->timestamp;
        return DB::table('sessions')
            ->where('last_activity', '>=', $threshold)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
    }
}
