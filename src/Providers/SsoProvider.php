<?php

namespace ZamanTech\Sso\Providers;

use Illuminate\Support\ServiceProvider;

class SsoProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            // Can't locate path: </var/www/html/vendor/zaman-tech/sso/src/Providers/../config/zaman-tech.php>
            __DIR__.'/../../../config/zaman-tech.php' => config_path('zaman-tech.php')
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
