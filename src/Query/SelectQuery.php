<?php
namespace CsrDelft\Orm\Query;

use CsrDelft\Orm\Common\OrmException;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\Persistence\QueryBuilder;
use CsrDelft\Orm\PersistenceModel;
use PDO;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 22/09/2017
 */
class SelectQuery {
	/**
	 * Error constants.
	 */
	const ERROR_OPERATOR_DOES_NOT_EXIST = 'Operator type "%s" does not exist.';
	const ERROR_NO_RESULT = 'Expected at least one result';
	const ERROR_SELECT_NOT_POSSIBLE = 'Selecting field "%s" in model "%s" not possible.';

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

	/** @var PersistenceModel */
	protected $model;

	/** @var string[] */
	protected $criteria;

	/** @var mixed */
	protected $criteria_params;

	/** @var QueryBuilder */
	protected $queryBuilder;

	/** @var string[] */
	protected $attributes;

	/** @var Database */
	protected $database;

	/** @var int */
	protected $limit;

	/** @var int */
	protected $offset;

	/**
	 * Query constructor.
	 * @param PersistenceModel $model
	 * @param Database $database
	 */
	public function __construct(PersistenceModel $model, Database $database) {
		$this->model = $model;
		$this->database = $database;
		$this->queryBuilder = new QueryBuilder();
		$this->attributes = ['*'];
		$this->limit = -1;
		$this->criteria = [];
		$this->criteria_params = [];
	}

	/**
	 * @param int $limit
	 * @return $this
	 */
	public function limit($limit) {
		$this->limit = $limit;

		return $this;
	}

	/**
	 * @param int $offset
	 * @return $this
	 */
	public function offset($offset) {
		$this->offset = $offset;

		return $this;
	}

	/**
	 * @param string[] $attributes
	 * @return $this
	 */
	public function setAttributes($attributes) {
		$this->attributes = $attributes;

		return $this;
	}

	/**
	 * @param $data
	 * @return $this
	 */
	public function filter($data) {
		foreach ($data as $field_type => $value) {
			$this->filterBy($field_type, $value);
		}

		return $this;
	}

	/**
	 * @param string $field_type
	 * @param $value
	 * @return $this
	 * @throws OrmException
	 */
	public function filterBy($field_type, $value) {
		$attributes = $this->model->getAttributes();

		$matches = explode('__', $field_type);

		$field = $matches[0];
		$type = isset($matches[1]) ? $matches[1] : self::TYPE_IEXACT;

		if (in_array($field, $attributes)) {
			$this->criteria[] = $this->getCriteriaSql($field, $type);
			$this->criteria_params[] = $value;
		} else {
			throw new OrmException(sprintf(self::ERROR_SELECT_NOT_POSSIBLE, $field, get_class($this->model)));
		}

		return $this;
	}

	public function getAll() {
		return $this->execute()->fetchAll();
	}

	/**
	 * @return PersistentEntity
	 * @throws OrmException
	 */
	public function getOne() {
		$entity = $this->execute()->fetch();

		if ($entity === false) {
			throw new OrmException(self::ERROR_NO_RESULT);
		}

		return $entity;
	}

	/**
	 * @return PersistentEntity[]|\PDOStatement
	 */
	public function execute() {
		$result = $this->database->sqlSelect(
			$this->attributes,
			$this->model->getTableName(),
			implode(self::SQL_AND, $this->criteria),
			$this->criteria_params,
			null,
			null,
			$this->limit
		);

		$model = $this->model;

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$result->setFetchMode(PDO::FETCH_CLASS, $model::ORM, [true]);

		return $result;
	}

	/**
	 * @param $field
	 * @param string $type
	 * @return string
	 * @throws OrmException
	 */
	private function getCriteriaSql($field, $type) {
		$mapTypeToOperator = [
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

		if (isset($mapTypeToOperator[$type])) {
			$operator = $mapTypeToOperator[$type];
		} else {
			throw new OrmException(sprintf(self::ERROR_OPERATOR_DOES_NOT_EXIST, $type));
		}

		return sprintf('%s %s', $field, $operator);
	}
}
