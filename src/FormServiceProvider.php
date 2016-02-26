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
            __DIR__.'/assets' => resource_path('assets/libs/form/'),
            __DIR__.'/assets/libs/ckeditor' => public_path('libs/ckeditor'),
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
