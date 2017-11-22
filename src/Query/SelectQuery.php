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
	 * Sql operator constants.
	 */
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

	/** @var string[] */
	protected $order;

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
		$this->order = [];
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
		$type = isset($matches[1]) ? new SelectMode($matches[1]) : new SelectMode(SelectMode::TYPE_IEXACT);

		if (in_array($field, $attributes)) {
			$this->criteria[] = $this->getCriteriaSql($field, $type);
			$this->criteria_params[] = $value;
		} else {
			throw new OrmException(sprintf(self::ERROR_SELECT_NOT_POSSIBLE, $field, get_class($this->model)));
		}

		return $this;
	}

	/**
	 * @return PersistentEntity[]
	 */
	public function getAll() {
		return $this->execute()->fetchAll();
	}

	/**
	 * @return PersistentEntity
	 * @throws OrmException
	 */
	public function getOne() {
		$this->limit(1);

		$entity = $this->execute()->fetch();

		if ($entity === false) {
			throw new OrmException(self::ERROR_NO_RESULT);
		}

		return $entity;
	}

	/**
	 * Get an (unfiltered) count of the query.
	 *
	 * @return int Number of records
	 */
	public function countUnfiltered() {
		$result = $this->database->sqlSelect(
			['COUNT(*)'],
			$this->model->getTableName()
		);

		return (int)$result->fetchColumn();
	}

	/**
	 * Get a count count of the query.
	 *
	 * @return int Number of records
	 */
	public function count() {
		$result = $this->database->sqlSelect(
			['COUNT(*)'],
			$this->model->getTableName(),
			$this->getCriteriaString(),
			$this->criteria_params
		);

		return (int)$result->fetchColumn();
	}

	/**
	 * @return PersistentEntity[]|\PDOStatement
	 */
	public function execute() {
		$result = $this->database->sqlSelect(
			$this->attributes,
			$this->model->getTableName(),
			$this->getCriteriaString(),
			$this->criteria_params,
			null,
			$this->getOrderString(),
			$this->limit,
			$this->offset
		);

		$model = $this->model;

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$result->setFetchMode(PDO::FETCH_CLASS, $model::ORM, [true]);

		return $result;
	}

	/**
	 * Create a copy of this query.
	 *
	 * @return SelectQuery
	 */
	public function copy() {
		$query = new SelectQuery($this->model, $this->database);
		$query->attributes = $this->attributes;
		$query->limit = $this->limit;
		$query->criteria = $this->criteria;
		$query->criteria_params = $this->criteria_params;

		return $query;
	}

	/**
	 * @param string $column
	 * @param OrderDirection $direction
	 */
	public function addOrderBy($column, OrderDirection $direction) {
		$this->order[] = $column . ' ' . $direction->getChoice();
	}

	/**
	 * @param string $field
	 * @param SelectMode $type
	 * @return string
	 * @throws OrmException
	 */
	private function getCriteriaSql($field, SelectMode $type) {
		return sprintf('%s %s', $field, $type->getOperator());
	}

	/**
	 * @return string|null
	 */
	private function getCriteriaString() {
		if (count($this->criteria) === 0) {
			return null;
		} else {
			return implode(self::SQL_AND, $this->criteria);
		}
	}

	private function getOrderString() {
		if (count($this->order) === 0) {
			return null;
		} else {
			return implode(', ', $this->order);
		}
	}
}
