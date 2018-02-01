<?php

namespace CsrDelft\Orm\Common\Object;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 22/11/2017
 */
class SqlQuery extends StringObject {
	public function getQuery() {
		return $this->value;
	}
}
