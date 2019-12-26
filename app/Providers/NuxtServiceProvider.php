<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class NuxtServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->mergeConfigFrom(config_path('nuxt.php'), 'nuxt');
    }
}
