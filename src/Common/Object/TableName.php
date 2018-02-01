<?php

namespace CsrDelft\Orm\Common\Object;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 22/11/2017
 */
class TableName extends StringObject {
	public function getTableName() {
		return $this->value;
	}
}
