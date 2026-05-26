<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS on production (Railway)
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Set mail FROM address and name dynamically from company settings
        try {
            $company = \App\Models\CompanySetting::first();
            if ($company) {
                if (!empty($company->email)) {
                    Config::set('mail.from.address', $company->email);
                }
                if (!empty($company->name)) {
                    Config::set('mail.from.name', $company->name);
                }
            }
        } catch (\Throwable $e) {
            // DB may not be available during migrations/artisan commands — ignore
        }
    }
}
