<?php namespace Poppy\Extension\Fe;

use Poppy\Extension\Fe\Console\Bower;
use Poppy\Framework\Support\PoppyServiceProvider;

class ExtensionServiceProvider extends PoppyServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../config/fe.php'                         => config_path('ext-fe.php'),
			__DIR__ . '/../resources/css/be_login.css'            => public_path('assets/css/poppy-ext-fe/be_login.css'),
			__DIR__ . '/../resources/mixes/poppy/cp.js'           => public_path('assets/js/poppy/cp.js'),
			__DIR__ . '/../resources/mixes/poppy/doc.js'          => public_path('assets/js/poppy/doc.js'),
			__DIR__ . '/../resources/mixes/poppy/plugin.js'       => public_path('assets/js/poppy/plugin.js'),
			__DIR__ . '/../resources/mixes/poppy/util.js'         => public_path('assets/js/poppy/util.js'),
			__DIR__ . '/../resources/mixes/poppy/backend/cp.js'   => public_path('assets/js/poppy/backend/cp.js'),
			__DIR__ . '/../resources/mixes/poppy/backend/util.js' => public_path('assets/js/poppy/backend/util.js'),
		], 'ext-fe');

		$this->loadViewsFrom(__DIR__ . '/../resources/views/', 'ext-fe');
	}

	/**
	 * Register the service provider.
	 * @return void
	 */
	public function register()
	{
		// 配置文件合并
		$this->mergeConfigFrom(__DIR__ . '/../config/fe.php', 'ext-fe');
		$this->registerConsoleCommand('extension.fe.bower', Bower::class);

	}

	/**
	 * Get the services provided by the provider.
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}
