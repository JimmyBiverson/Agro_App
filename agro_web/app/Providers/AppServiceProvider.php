<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $site = cache()->remember('site_settings', 3600, function () {
                $keys = ['site_name', 'site_tagline', 'site_favicon', 'site_logo', 'og_image', 'site_phone', 'site_email', 'site_address',
                    'theme_accent', 'theme_success', 'theme_warning', 'theme_danger', 'theme_info'];
                return \App\Models\Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();
            });
            $view->with('site', $site);
        });
    }
}
