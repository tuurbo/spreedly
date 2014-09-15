<?php namespace Tuurbo\Spreedly;

use Illuminate\Support\Facades\Facade;

class SpreedlyFacade extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'spreedly'; }

}