<?php

namespace ZamanTech\Sso\Providers;

use Illuminate\Support\ServiceProvider;

class SsoProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/zaman-tech.php' => config_path('zaman-tech.php')
        ], 'zaman-tech-config');
    }
}



/*
composer add
,
            "aliases": {
                "ZamanTech": "ZamanTech\\Sso\\Facade"
            }
 */
