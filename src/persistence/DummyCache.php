<?php
namespace CsrDelft\Orm\Persistence;

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

	public function __call($name, $arguments) {
		return false;
	}

}