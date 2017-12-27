<?php namespace Sample;

/**
 * Copyright (C) Update For IDE
 */

use Sample\Request\RouteServiceProvider;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider as ModuleServiceProviderBase;

class ServiceProvider extends ModuleServiceProviderBase
{
	/**
	 * @var string The poppy name slug.
	 */
	private $name = 'sample';

	/**
	 * Bootstrap the module services.
	 * @return void
	 * @throws ModuleNotFoundException
	 */
	public function boot()
	{
		parent::boot($this->name);
	}

	/**
	 * Register the module services.
	 * @return void
	 */
	public function register()
	{
		$this->app->register(RouteServiceProvider::class);
	}
}
