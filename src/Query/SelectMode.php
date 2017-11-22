<?php

namespace CsrDelft\Orm\Query;

use CsrDelft\Orm\Common\Enum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 29/10/2017
 */
class SelectMode extends Enum {
	/**
	 * Select type constants.
	 */
	const TYPE_EXACT = 'exact';
	const TYPE_IEXACT = 'iexact';
	const TYPE_CONTAINS = 'contains';
	const TYPE_ICONTAINS = 'icontains';
	const TYPE_STARTSWITH = 'startswith';
	const TYPE_ISTARTSWITH = 'istartswith';
	const TYPE_ENDSWITH = 'endswith';
	const TYPE_IENDSWITH = 'iendswith';
	const TYPE_LT = 'lt';
	const TYPE_LTE = 'lte';
	const TYPE_GT = 'gt';
	const TYPE_GTE = 'gte';

	/**
	 * Sql operator constants.
	 */
	const COLLATE_CASE_SENSITIVE = 'COLLATE \'utf8_bin\' ';
	const OPERATOR_ENDSWITH = 'LIKE \'%\'';
	const OPERATOR_STARTSWITH = 'LIKE ? || \'%\'';
	const OPERATOR_CONTAINS = 'LIKE \'%\' || ? || \'%\'';
	const OPERATOR_EXACT = '= ?';
	const OPERATOR_LT = '< ?';
	const OPERATOR_LTE = '<= ?';
	const OPERATOR_GT = '> ?';
	const OPETAROR_GTE = '>= ?';
	const SQL_AND = ' AND ';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::TYPE_EXACT => self::TYPE_EXACT,
		self::TYPE_IEXACT => self::TYPE_IEXACT,
		self::TYPE_CONTAINS => self::TYPE_CONTAINS,
		self::TYPE_ICONTAINS => self::TYPE_ICONTAINS,
		self::TYPE_STARTSWITH => self::TYPE_STARTSWITH,
		self::TYPE_ISTARTSWITH => self::TYPE_ISTARTSWITH,
		self::TYPE_ENDSWITH => self::TYPE_ENDSWITH,
		self::TYPE_IENDSWITH => self::TYPE_IENDSWITH,
		self::TYPE_LT => self::TYPE_LT,
		self::TYPE_LTE => self::TYPE_LTE,
		self::TYPE_GT => self::TYPE_GT,
		self::TYPE_GTE => self::TYPE_GTE,
	];

	/**
	 * @var string[]
	 */
	protected static $typeToOperator = [
		self::TYPE_EXACT => self::COLLATE_CASE_SENSITIVE . self::OPERATOR_EXACT,
		self::TYPE_IEXACT => self::OPERATOR_EXACT,
		self::TYPE_CONTAINS => self::COLLATE_CASE_SENSITIVE . self::OPERATOR_CONTAINS,
		self::TYPE_ICONTAINS => self::OPERATOR_CONTAINS,
		self::TYPE_STARTSWITH => self::COLLATE_CASE_SENSITIVE . self::OPERATOR_STARTSWITH,
		self::TYPE_ISTARTSWITH => self::OPERATOR_STARTSWITH,
		self::TYPE_ENDSWITH => self::COLLATE_CASE_SENSITIVE . self::OPERATOR_ENDSWITH,
		self::TYPE_IENDSWITH => self::OPERATOR_ENDSWITH,
		self::TYPE_LT => self::OPERATOR_LT,
		self::TYPE_LTE => self::OPERATOR_LTE,
		self::TYPE_GT => self::OPERATOR_GT,
		self::TYPE_GTE => self::OPETAROR_GTE,
	];

	/**
	 * @return string
	 */
	public function getOperator() {
		return static::$typeToOperator[$this->choice];
	}
}
