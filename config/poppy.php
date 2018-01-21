<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Default Pagination Num
	|--------------------------------------------------------------------------
	|
	*/
	'pagesize'         => 15,


	/*
	|--------------------------------------------------------------------------
	| Extension config
	|--------------------------------------------------------------------------
	|
	*/
	'extension'        => [

		/* 前端生成技术文档的配置
		 * ---------------------------------------- */
		'fe' => [
			'catalog' => [
				'dailian' => [
					'origin' => 'modules/system/src/request',
					'doc'    => 'public/docs/system',
				],
			],
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Backend Control Panel
	|--------------------------------------------------------------------------
	| Default : \System\Request\Backend\HomeController@index
	*/
	'backend_cp'       => '',


	/*
	|--------------------------------------------------------------------------
	| Backend Setting Paging
	|--------------------------------------------------------------------------
	| Backend Setting Pages
	*/
	'backend_pages'    => [

	],


	/*
	|--------------------------------------------------------------------------
	| Message Template For Differ Modules.
	|--------------------------------------------------------------------------
	|
	*/
	'message_template' => [
		'futian' => 'futian::tpl.inc_message',
	],

	'guard_location' => [
		'web' => 'slt:user.login',
	],
];