<?php

namespace Acelle\Extra\LogViewer;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * List of providers to register.
     */
    private $providers = [
        Providers\RouteServiceProvider::class,
    ];

    /**
     * Register the service provider.
     */
    public function register()
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }
}
