<?php

namespace CsrDelft\Orm\Entity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 09/01/2019
 */
trait PersistentEntityPrimaryKeyTrait {
	/**
	 * Primary key for the table, can be any number of columns.
	 *
	 * @var array
	 */
	protected static $primary_key = [];

	/**
	 * Get primary key of entity.
	 *
	 * @return string[]
	 */
	public function getPrimaryKey() {
		return array_values(static::$primary_key);
	}

}
