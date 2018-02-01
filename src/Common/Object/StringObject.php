<?php

namespace CsrDelft\Orm\Common\Object;

/**
 * Simple string wrapper.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 22/11/2017
 */
abstract class StringObject {
	/**
	 * Value of this object.
	 * @var string
	 */
	protected $value;

	/**
	 * StringObject constructor.
	 * @param string $value
	 */
	public function __construct(string $value) {
		$this->value = $value;
	}

	public function getString() {
		return $this->value;
	}

	public function equals($other): bool {
		if ($other instanceof static) {
			return $other->value === $this->value;
		} else {
			return false;
		}
	}
}
