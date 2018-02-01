<?php
namespace CsrDelft\Orm\Persistence;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DummyCache {

	/**
	 * Add should return true, because an addition should always work.
	 *
	 * @param array| ...$_ unused
	 * @return bool
	 */
	public function add(...$_): bool {
		return true;
	}

	/**
	 * Any call returns false.
	 *
	 * @param string $name
	 * @param mixed[] $arguments
	 * @return bool
	 */
	public function __call(string $name, $arguments): bool {
		return false;
	}
}
