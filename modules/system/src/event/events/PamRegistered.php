<?php namespace System\Event\Events;

use System\Models\PamAccount;

class PamRegistered
{

	/** @var PamAccount */
	private $pam;

	public function __construct(PamAccount $pam)
	{
		$this->pam = $pam;
	}

	public function pam()
	{
		return $this->pam;
	}
}