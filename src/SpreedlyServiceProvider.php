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
     */
    public function register()
    {
        $this->app['spreedly'] = $this->app->share(function ($app) {
            $config = $app['config']->get('services.spreedly');

            return new Spreedly($config);
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
