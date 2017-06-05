<?php

namespace CsrDelft\Orm\Persistence;

use Closure;
use Exception;
use PDO;

/**
 * Database.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Database {
	/**
	 * Singleton instance
	 * @var Database
	 */
	private static $instance;

	/**
	 * Creates queries
	 *
	 * @var QueryBuilder
	 */
	private $queryBuilder;

	/**
	 * Database connection
	 *
	 * @var PDO
	 */
	private $database;

	public static function init($pdo) {
		assert(!isset(self::$instance));
		self::$instance = new Database($pdo);
	}

	/**
	 * Get singleton Database instance.
	 *
	 * @return Database
	 */
	public static function instance() {
		assert(isset(self::$instance));
		return self::$instance;
	}

	public function __construct(PDO $pdo) {
		$this->database = $pdo;
		$this->queryBuilder = new QueryBuilder();
	}

	public static function transaction(Closure $function) {
		return static::instance()->_transaction($function);
	}

	/**
	 * Wrap anonymous function $function in a database transaction
	 * does a rollback if an exception is thrown and rethrows the
	 * exception.
	 *
	 * Silently continues if the database is in a transaction
	 *
	 * Use `use` to scope variables.
	 *
	 * @param Closure $function
	 * @throws Exception Any exception thrown by $function
	 * @return mixed Value returned by $function
	 */
	public function _transaction(Closure $function) {
		$db = $this->getDatabase();
		if ($db->inTransaction()) {
			return $function();
		}
		$db->beginTransaction();
		try {
			$response = $function();
		} catch (Exception $ex) {
			$db->rollBack();
			throw $ex;
		}
		$db->commit();

		return $response;
	}

	/**
	 * Array of SQL statements for debug
	 * @var array
	 */
	private static $queries = [];
	private static $trace = [];

	/**
	 * Get array of SQL statements for debug
	 * @return array
	 */
	public function getQueries() {
		return self::$queries;
	}

	public function getTrace() {
		return self::$trace;
	}

	/**
	 * Trace back where the query originated from
	 * @param string $query
	 * @param array $params
	 */
	private function addQuery($query, array $params) {
		$q = $this->queryBuilder->interpolateQuery($query, $params);
		self::$queries[] = $q;

		$trace = $q . "\n\n";
		foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $i => $t) {
			$trace .= '#' . $i . ' ';
			if (isset($t['file'])) {
				$trace .= $t['file'];
			}
			if (isset($t['line'])) {
				$trace .= '(' . $t['line'] . ')';
			}
			$trace .= ': ';
			if (isset($t['class'])) {
				$trace .= $t['class'];
			}
			if (isset($t['function'])) {
				$trace .= '->' . $t['function'];
			}
			$trace .= "\n";
		}
		self::$trace[] = $trace;
	}

	/**
	 * Optional named parameters.
	 *
	 *
	 * @param array $attributes
	 * @param string $from
	 * @param string $where
	 * @param array $params
	 * @param string $group_by
	 * @param string $order_by
	 * @param int $limit
	 * @param int $start
	 * @return \PDOStatement
	 */
	public function sqlSelect(array $attributes, $from, $where = null, array $params = [], $group_by = null, $order_by = null, $limit = null, $start = 0) {
		$sql = $this->queryBuilder->buildSelect(
			$attributes,
			$from,
			$where,
			$group_by,
			$order_by,
			$limit,
			$start
		);
		$query = $this->database->prepare($sql);
		$query->execute($params);
		$this->addQuery($query->queryString, $params);
		return $query;
	}

	/**
	 * Optional named parameters.
	 *
	 * @param string $from
	 * @param string $where
	 * @param array $params
	 * @return boolean
	 */
	public function sqlExists($from, $where = null, array $params = []) {
		$sql = $this->queryBuilder->buildExists($from, $where);
		$query = $this->database->prepare($sql);
		$query->execute($params);
		$this->addQuery($query->queryString, $params);
		return (boolean)$query->fetchColumn();
	}

	/**
	 * Requires named parameters.
	 * Optional REPLACE (DELETE & INSERT) if primary key already exists.
	 *
	 * @param string $into
	 * @param array $properties
	 * @return string last inserted row id or sequence value
	 * @throws Exception if number of rows affected !== 1
	 */
	public function sqlInsert(
		$into,
		array $properties
	) {
		$insert_params = [];
		foreach ($properties as $attribute => $value) {
			$insert_params[':I' . $attribute] = $value; // name parameters after attribute
		}
		$sql = $this->queryBuilder->buildInsert($into, $properties, $insert_params);
		$query = $this->database->prepare($sql);
		$query->execute($insert_params);
		$this->addQuery($query->queryString, $insert_params);
		if ($query->rowCount() !== 1) {
			throw new Exception('sqlInsert rowCount=' . $query->rowCount());
		}
		return $this->database->lastInsertId();
	}

	/**
	 * Requires positional parameters.
	 *
	 * @param string $into
	 * @param array $properties =
	 *        [
	 *            ["attr_name1", "attr_name2", ...],
	 *            ["entry1value1", "entry1value2", ...],
	 *            ["entry2value1", "entry2value2", ...],
	 *            ...
	 *        ]
	 * @param boolean $replace DELETE & INSERT if primary key already exists
	 * @return int number of rows affected
	 * @throws Exception if number of values !== number of properties
	 */
	public function sqlInsertMultiple($into, array $properties, $replace = false) {
		if ($replace) {
			$sql = 'REPLACE';
		} else {
			$sql = 'INSERT';
		}
		$insert_values = [];
		$attributes = array_shift($properties);
		$count = count($attributes);
		$sql .= ' INTO ' . $into . ' (' . implode(', ', $attributes) . ') VALUES ';
		foreach ($properties as $i => $props) { // for all entries
			if (count($props) !== $count) {
				throw new Exception('Missing property value(s) for entry: ' . $i);
			}
			if ($i > 0) {
				$sql .= ', ';
			}
			$sql .= '(';
			foreach ($props as $j => $value) {
				$param = ':I' . $i . $attributes[$j]; // name parameters after attribute with index
				$insert_values[$param] = $value;
				if ($j > 0) {
					$sql .= ', ';
				}
				$sql .= $param;  // named params
			}
			$sql .= ')';
		}
		$query = $this->database->prepare($sql);
		$query->execute($insert_values);
		$this->addQuery($query->queryString, $insert_values);
		return $query->rowCount();
	}

	/**
	 * Requires named parameters.
	 *
	 * @param string $table
	 * @param array $properties
	 * @param string $where
	 * @param array $where_params
	 * @param int $limit
	 * @return int number of rows affected
	 * @throws Exception if duplicate named parameter
	 */
	public function sqlUpdate($table, array $properties, $where, array $where_params = [], $limit = null) {
		$attributes = [];
		foreach ($properties as $attribute => $value) {
			$attributes[] = $attribute . ' = :U' . $attribute; // name parameters after attribute
			if (array_key_exists(':U' . $attribute, $where_params)) {
				throw new Exception('Named parameter already defined: ' . $attribute);
			}
			$where_params[':U' . $attribute] = $value;
		}
		$sql = $this->queryBuilder->buildUpdate($table, $attributes, $where, $limit);
		$query = $this->database->prepare($sql);
		$query->execute($where_params);
		$this->addQuery($query->queryString, $where_params);
		return $query->rowCount();
	}

	/**
	 * Optional named parameters.
	 *
	 * @param string $from
	 * @param string $where
	 * @param array $where_params
	 * @param int $limit
	 * @return int number of rows affected
	 */
	public function sqlDelete($from, $where, array $where_params, $limit = null) {
		$sql = $this->queryBuilder->buildDelete($from, $where, $limit);
		$query = $this->database->prepare($sql);
		$query->execute($where_params);
		$this->addQuery($query->queryString, $where_params);
		return $query->rowCount();
	}

	public function getDatabase() {
		return $this->database;
	}

}
