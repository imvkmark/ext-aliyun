<?php namespace Develop\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class MiddlewareServiceProvider extends ServiceProvider
{

	public function boot(Router $router)
	{
		$router->aliasMiddleware('develop.auth', 'Poppy\Backend\Request\Middleware\Develop');
	}
}