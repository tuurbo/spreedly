<?php namespace Tuurbo\Spreedly;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class SpreedlyServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['spreedly'] = $this->app->share(function($app)
		{
			$config = $app['config']->get('services.spreedly');

			return new Spreedly($config);
		});
	}

}