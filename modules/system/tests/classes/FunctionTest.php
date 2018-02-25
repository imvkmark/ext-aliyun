<?php namespace System\Tests\Classes;

/**
 * Copyright (C) Update For IDE
 */

use Poppy\Framework\Application\TestCase;
use System\Action\Verification;
use System\Models\PamCaptcha;

class FunctionTest extends TestCase
{

	public function testOrderMatch()
	{

		$prefix = sys_order_prefix('Game2018');
		$this->assertEquals('Game', $prefix);

		$prefix = sys_order_prefix('Game');
		$this->assertEquals('Game', $prefix);

	}
}