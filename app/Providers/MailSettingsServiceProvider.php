<?php

namespace App\Providers;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MailSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap database-backed mail settings.
     */
    public function boot(): void
    {
        if (! Schema::hasTable('mail_settings')) {
            return;
        }

        MailSetting::applyToConfig();
    }
}
