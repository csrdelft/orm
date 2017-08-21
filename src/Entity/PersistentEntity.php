<?php

namespace CsrDelft\Orm\Entity;

/**
 * PersistentEntity.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
abstract class PersistentEntity implements \JsonSerializable {

	/**
	 * PersistentEntity constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Get universal Id for entity.
	 *
	 * Warning: assumes unique PersistentEntity names.
	 *
	 * @return string
	 */
	public function getUUID() {
		return strtolower(sprintf(
			'%s@%s.csrdelft.nl',
			implode('.', $this->getValues(true)),
			\common\short_class($this)
		));
	}

	/**
	 * Get array ready for json serialization.
	 *
	 * @return string[]
	 */
	public function jsonSerialize() {
		$array = get_object_vars($this);
		$array['UUID'] = $this->getUUID();
		return $array;
	}
}
