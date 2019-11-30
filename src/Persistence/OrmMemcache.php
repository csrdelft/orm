<?php
namespace CsrDelft\Orm\Persistence;

use CsrDelft\Orm\DependencyManager;
use Memcache;

/**
 * CsrMemcache.singleton.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Wrapper for Memcache if available; DummyCache otherwise.
 */
class OrmMemcache extends DependencyManager {
	/**
	 * @var DummyCache|Memcache
	 */
	protected $cache;

	/**
	 * OrmMemcache constructor.
	 * @param $path
	 */
	public function __construct($path) {
		if (class_exists('Memcache')) {
			$this->cache = new Memcache();
			if ($this->cache->connect('unix://' . $path . 'csrdelft-cache.socket', 0)) {
				return;
			}
		}

		$this->cache = new DummyCache();
	}

	/**
	 * @return DummyCache|Memcache
	 */
	public function getCache() {
		return $this->cache;
	}
}
