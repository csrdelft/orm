<?php

namespace CsrDelft\Orm\Query;
use CsrDelft\Orm\Common\Enum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 02/11/2017
 */
class OrderDirection extends Enum {
	/**
	 * Possible choices.
	 */
	const CHOICE_ASC = 'ASC';
	const CHOICE_DESC = 'DESC';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::CHOICE_ASC => self::CHOICE_ASC,
		self::CHOICE_DESC => self::CHOICE_DESC,
	];

	/**
	 * @return OrderDirection
	 */
	public static function ASC(): OrderDirection {
		return new static(self::CHOICE_ASC);
	}

	/**
	 * @return OrderDirection
	 */
	public static function DESC(): OrderDirection {
		return new static(self::CHOICE_DESC);
	}
}
