<?php

namespace maree\alrajhibankPayments;

use Illuminate\Support\ServiceProvider;

class AlrajhibankServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__.'/config/alrajhiBank.php' => config_path('alrajhiBank.php'),
        ],'alrajhiBank');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/alrajhiBank.php', 'alrajhiBank'
        );
    }
}
}
