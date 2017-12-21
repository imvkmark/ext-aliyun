<?php namespace Poppy\Framework\Translation\Events;

/**
 * Class LocaleUpdated.
 */
class LocaleUpdated
{
	/**
	 * The new locale.
	 * @var string
	 */
	public $locale;

	/**
	 * Create a new event instance.
	 *
	 * @param string $locale
	 */
	public function __construct($locale)
	{
		$this->locale = $locale;
	}
}

