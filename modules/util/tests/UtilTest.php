<?php namespace Util\Tests;

/**
 * Copyright (C) Update For IDE
 */

use Poppy\Framework\Application\TestCase;

class UtilTest extends TestCase
{
	//
	public function testUtil()
	{

		$util = app('act.util');
		if (!$util->sendCaptcha('18864838035')) {
			dd($util->getError());
		}
		else {
			dd($util->getSuccess());
		}

	}
}
