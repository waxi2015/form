<?php

namespace Waxis\Form;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class FormServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/routes.php';
        }

        $this->publishes([
            __DIR__.'/assets' => resource_path('assets/common/libs/form/'),
            __DIR__.'/Descriptors/Example.php' => app_path('Descriptors/Form/Example.php'),
            __DIR__.'/lang' => resource_path('lang'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
