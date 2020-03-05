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
	 * @param int $port
	 */
	public function __construct($path, $port = 0) {
		if (class_exists('Memcache')) {
			$this->cache = new Memcache();
			if ($this->cache->connect($path, $port)) {
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
