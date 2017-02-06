<?php
namespace CsrDelft\Orm\DataBase;

class Cache {
	/**
	 * @var \Memcache
	 */
	private static $cache;

	public static function get() {
		return static::$cache;
	}

	public static function set($cache) {
		static::$cache = $cache;
	}
}