<?php
namespace CsrDelft\Orm\Persistence;

use Memcache;

/**
 * CsrMemcache.singleton.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Wrapper for Memcache if available; DummyCache otherwise.
 */
class OrmMemcache {

	/**
	 * Singleton instance
	 * @var Memcache|DummyCache
	 */
	private static $instance;

	public static function init($path) {
		assert(!isset(self::$instance));
		if (class_exists('Memcache')) {
			self::$instance = new Memcache();
			if (self::$instance->connect('unix://' . $path . 'csrdelft-cache.socket', 0)) {
				return;
			}
		}

		self::$instance = new DummyCache();
	}

	/**
	 * Get singleton CsrMemcache instance.
	 *
	 * @return Memcache
	 */
	public static function instance() {
		assert(isset(self::$instance), 'Call OrmMemcache::init(...) first.');
		return self::$instance;
	}

	/**
	 * OrmMemcache constructor.
	 */
	private function __construct() {
		// never called
	}

}
