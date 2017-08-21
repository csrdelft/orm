<?php
namespace CsrDelft\Orm\Persistence;

/**
 * Class DummyCache.
 *
 * Used when no cache implementation is available.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DummyCache {

	/**
	 * Add should return true, because an addition should always work.
	 *
	 * @param array| ...$_ unused
	 * @return bool
	 */
	public function add(...$_) {
		return true;
	}

	/**
	 * Every function returns false.
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return bool
	 */
	public function __call($name, $arguments) {
		return false;
	}

}
