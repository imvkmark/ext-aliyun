<?php namespace System;

/**
 * Copyright (C) Update For IDE
 */

use Illuminate\Console\Scheduling\Schedule;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;
use System\Backend\BackendServiceProvider;
use System\Console\DevHtmlCommand;
use System\Console\InstallCommand;
use System\Console\LogCommand;
use System\Events\ListenerServiceProvider;
use System\Extension\ExtensionServiceProvider;
use System\Models\PamRole;
use System\Module\ModuleServiceProvider;
use System\Pam\BindChangeServiceProvider;
use System\Pam\Commands\UserCommand;
use System\Pam\PamServiceProvider;
use System\Pam\Policies\RolePolicy;
use System\Permission\Commands\PermissionCommand;
use System\Permission\PermissionServiceProvider;
use System\Rbac\RbacServiceProvider;
use System\Request\MiddlewareServiceProvider;
use System\Request\RouteServiceProvider;
use System\Setting\SettingServiceProvider;


class ServiceProvider extends PoppyServiceProvider
{

	protected $listens = [
		'Poppy\Framework\Poppy\Events\PoppyOptimized' => [
			'System\Module\Listeners\ClearCacheListener',
			'System\Extension\Listeners\ClearCacheListener',
		],
		'System\Setting\Events\SettingUpdated'        => [
			'System\Module\Listeners\ClearCacheListener',
		],
	];

	protected $policies = [
		PamRole::class => RolePolicy::class,
	];

	/**
	 * @var string Module name
	 */
	protected $name = 'system';

	/**
	 * Bootstrap the module services.
	 * @return void
	 * @throws ModuleNotFoundException
	 */
	public function boot()
	{
		parent::boot($this->name);
		$path = poppy_path($this->name);
		$this->mergeConfigFrom($path . '/resources/config/graphql.php', 'graphql');

		// register extension
		$this->app['extension']->register();
	}

	/**
	 * Register the module services.
	 * @return void
	 */
	public function register()
	{
		$this->app->register(MiddlewareServiceProvider::class);
		$this->app->register(RouteServiceProvider::class);
		$this->app->register(SettingServiceProvider::class);
		$this->app->register(BackendServiceProvider::class);
		$this->app->register(ExtensionServiceProvider::class);
		$this->app->register(ModuleServiceProvider::class);
		$this->app->register(RbacServiceProvider::class);
		$this->app->register(ListenerServiceProvider::class);
		$this->app->register(PermissionServiceProvider::class);
		$this->app->register(PamServiceProvider::class);
		$this->app->register(BindChangeServiceProvider::class);

		$this->registerSchedule();
		$this->registerConsole();
	}


	private function registerSchedule()
	{
		$this->app['events']->listen('console.schedule', function(Schedule $schedule) {

			$schedule->command('clockwork:clean')->everyThirtyMinutes();
		});
	}


	private function registerConsole()
	{
		// system
		$this->registerConsoleCommand('system.permission', PermissionCommand::class);
		$this->registerConsoleCommand('system.user', UserCommand::class);
		$this->registerConsoleCommand('system.install', InstallCommand::class);
		$this->registerConsoleCommand('system.dev_html', DevHtmlCommand::class);
		$this->registerConsoleCommand('system.log', LogCommand::class);
	}


	public function provides()
	{
		return [];
	}
}
