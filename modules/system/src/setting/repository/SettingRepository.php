<?php namespace System\Setting\Repository;

use Illuminate\Support\Collection;
use Poppy\Framework\Classes\Traits\KeyParserTrait;
use System\Classes\Traits\SystemTrait;
use System\Models\SysConfig;
use System\Setting\Contracts\SettingContract;

/**
 * Config Repository
 */
class SettingRepository implements SettingContract
{
	use KeyParserTrait, SystemTrait;

	private $table;
	private $cacheName;
	private $cacheMinute;
	private static $cache = [];

	public function __construct()
	{
		$this->table       = (new SysConfig())->getTable();
		$this->cacheName   = cache_name(__CLASS__);
		$this->cacheMinute = 60;
		self::$cache       = (array) \Cache::get($this->cacheName);
	}

	/**
	 * Resets a setting value by deleting the record.
	 * @param string $key specifies the setting key value
	 * @return bool
	 * @throws \Exception
	 */
	public function delete($key)
	{
		if (!$this->keyParserMatch($key)) {
			return $this->setError(trans('system::setting.repository.setting.key_not_match', [
				'key' => $key,
			]));
		}
		$record = $this->findRecord($key);
		if (!$record) {
			return false;
		}

		$record->delete();

		unset(static::$cache[$key]);
		$this->writeCache();

		return true;
	}

	/**
	 * Returns a setting value by the module (or plugin) name and setting name.
	 * @param string $key     Specifies the setting key value, for example 'system:updates.check'
	 * @param mixed  $default the default value to return if the setting doesn't exist in the DB
	 * @return mixed returns the setting value loaded from the database or the default value
	 */
	public function get($key, $default = null)
	{
		if (array_key_exists($key, static::$cache)) {
			return static::$cache[$key];
		}

		if (!$this->keyParserMatch($key)) {
			return $this->setError(trans('system::conf.resp.key_not_match', [
				'key' => $key,
			]));
		}

		$record = $this->findRecord($key);
		if (!$record) {
			// get default by setting.yaml
			$settingItem = $this->getModule()->settings()->get($key);
			if ($settingItem) {
				$type           = $settingItem['type'] ?? 'string';
				$defaultSetting = $settingItem['default'] ?? '';
				switch ($type) {
					case 'string':
					default:
						$default = strval($defaultSetting);
						break;
					case 'int':
						$default = intval($defaultSetting);
						break;
					case 'bool':
					case 'boolean':
						$default = boolval($defaultSetting);
						break;
				}
			}

			static::$cache[$key] = $default;
			$this->writeCache();

			return static::$cache[$key];
		}

		static::$cache[$key] = unserialize($record->value);
		$this->writeCache();

		return static::$cache[$key];
	}

	/**
	 * Stores a setting value to the database.
	 * @param string $key   Specifies the setting key value, for example 'system:updates.check'
	 * @param mixed  $value the setting value to store, serializable
	 * @return bool
	 */
	public function set($key, $value)
	{
		if (is_array($key)) {
			foreach ($key as $_key => $_value) {
				$this->set($_key, $_value);
			}

			return true;
		}

		if (!$this->keyParserMatch($key)) {
			return $this->setError(trans('system::conf.resp.key_not_match', [
				'key' => $key,
			]));
		}

		$record = $this->findRecord($key);
		if (!$record) {
			$record                         = new SysConfig();
			list($namespace, $group, $item) = $this->parseKey($key);
			$record->namespace              = $namespace;
			$record->group                  = $group;
			$record->item                   = $item;
		}

		$record->value = serialize($value);
		$record->save();

		static::$cache[$key] = $value;
		$this->writeCache();

		return true;
	}

	/**
	 * Get setting via namespace and group.
	 * @param string $namespace
	 * @param string $group
	 * @return Collection
	 */
	public function getNsGroup($namespace, $group)
	{
		$collection = collect([]);
		$this->getModule()->settings()->each(
			function ($define, $settingKey) use ($namespace, $group, $collection) {
				$key = $namespace . '::' . $group;
				if (starts_with($settingKey, $key)) {
					$value = $this->get($settingKey);
					$collection->push([
						'value'       => $value,
						'type'        => data_get($define, 'type') ?: 'string',
						'key'         => $settingKey,
						'description' => data_get($define, 'description'),
					]);
				}
			}
		);

		return $collection;
	}

	/**
	 * Returns a record (cached)
	 * @param $key
	 * @return SysConfig
	 */
	private function findRecord($key)
	{
		/** @var SysConfig $record */
		$record = SysConfig::query();

		return $record->applyKey($key)->first();
	}

	private function writeCache()
	{
		\Cache::forever($this->cacheName, static::$cache);
	}
}
