<?php

namespace Tuurbo\Spreedly;

use Illuminate\Support\ServiceProvider;

class SpreedlyServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('spreedly', function ($app) {
            return new Spreedly($app['config']->get('services.spreedly'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['spreedly'];
    }
}
