<?php namespace Poppy\Framework\Helper;

/**
 * // todo
 * 这个原来 是 SysCookie 现在还不知道如何去重构
 */
class RawCookieHelper
{


	/**
	 * 判断Cookie是否存在
	 * @param $name
	 * @return bool
	 */
	public static function has($name)
	{
		return isset($_COOKIE[$name]);
	}

	/**
	 * 获取某个Cookie值
	 * @param $name
	 * @return string
	 */
	public static function get($name)
	{
		$value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
		if (config('app.cookie_base64')) {
			$value = base64_decode(unserialize($value));
		}
		return $value;
	}

	// 设置某个Cookie值
	public static function set($name, $value, $expire = 0, $path = '', $domain = '')
	{
		if (empty($path)) {
			$path = '/';
		}
		if (empty($domain)) {
			$domain = EnvHelper::domain();
		}

		$expire = !empty($expire) ? time() + $expire : 0;
		if (config('app.cookie_base64')) {
			$value = base64_encode(serialize($value));
		}
		return setcookie($name, $value, $expire, $path, $domain);
	}

	/**
	 * 删除某个Cookie值
	 * @param $name
	 */
	public static function remove($name)
	{
		self::set($name, '', time() - 3600);
		unset($_COOKIE[$name]);
	}


	/**
	 * 清空所有Cookie值
	 */
	public static function clear()
	{
		unset($_COOKIE);
	}
}