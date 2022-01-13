<?php

namespace ZamanTech\Sso;

use Illuminate\Support\ServiceProvider;

class SsoProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            // Can't locate path: </var/www/html/vendor/zaman-tech/sso/src/Providers/../config/zaman-tech.php>
            __DIR__.'/config/zaman-tech.php' => config_path('zaman-tech.php')
        ], 'zaman-tech-config');


        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}



/*
composer add
,
            "aliases": {
                "ZamanTech": "ZamanTech\\Sso\\Facade"
            }
 */
