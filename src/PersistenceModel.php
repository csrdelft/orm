<?php
namespace CsrDelft\Orm;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\Persistence\DatabaseAdmin;
use PDO;
use PDOException;
use PDOStatement;

/**
 * PersistenceModel.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Uses the database to provide persistence.
 * Requires an ORM class constant to be defined in superclass.
 * Requires a static property $instance in superclass.
 * Optional DIR class constant for location of ORM class.
 *
 */
abstract class PersistenceModel implements Persistence {

	public static function __static() {
		/** @var PersistentEntity $orm */
		$orm = static::ORM;
		if (defined('static::DIR')) {
			$dir = static::DIR;
		} else {
			$dir = '';
		}
		if (!class_exists(static::ORM)) {
			require_once 'model/entity/' . $dir . $orm . '.class.php';
		}
		$orm::__static(); // Extend the persistent attributes
		if (defined('DB_CHECK') AND DB_CHECK) {
			DatabaseAdmin::instance()->checkTable($orm);
		}
	}

	/**
	 * This has to be called once before using static methods due to
	 * static constructor emulation.
	 *
	 * @return $this
	 */
	public static function instance() {
		if (!isset(static::$instance)) {
			// Unfortunately PHP does not support static constructors
			static::__static(); // This is the next best thing for now
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = null;
	/**
	 * Object relational mapping
	 * @var PersistentEntity
	 */
	private $orm;
	/**
	 * Database connection
	 *
	 * @var Database
	 */
	protected $database;

	protected function __construct() {
		$orm = static::ORM;
		$this->orm = new $orm();
		$this->database = Database::instance();
	}

	public function getTableName() {
		return $this->orm->getTableName();
	}

	/**
	 * Get all attribute names.
	 *
	 * @return array
	 */
	public function getAttributes() {
		return $this->orm->getAttributes();
	}

	public function getAttributeDefinition($attribute_name) {
		return $this->orm->getAttributeDefinition($attribute_name);
	}

	public function getPrimaryKey() {
		return $this->orm->getPrimaryKey();
	}

	/**
	 * Find existing entities with optional search criteria.
	 * Retrieves all attributes.
	 *
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $group_by GROUP BY
	 * @param string $order_by ORDER BY
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return PDOStatement implements Traversable using foreach does NOT require ->fetchAll()
	 */
	public function find($criteria = null, array $criteria_params = array(), $group_by = null, $order_by = null, $limit = null, $start = 0) {
		if ($order_by == null) {
			$order_by = $this->default_order;
		}
		try {
			$result = $this->database->sqlSelect(array('*'), $this->getTableName(), $criteria, $criteria_params, $group_by, $order_by, $limit, $start);
			$result->setFetchMode(PDO::FETCH_CLASS, static::ORM, array(true));
			return $result;
		} catch (PDOException $ex) {
			throw $ex;
		}
	}

	/**
	 * Find existing entities with optional search criteria.
	 * Retrieves only requested attributes and the primary key values.
	 *
	 * @param array $attributes to retrieve
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $group_by GROUP BY
	 * @param string $order_by ORDER BY
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return PDOStatement implements Traversable using foreach does NOT require ->fetchAll()
	 */
	public function findSparse(array $attributes, $criteria = null, array $criteria_params = array(), $group_by = null, $order_by = null, $limit = null, $start = 0) {
		if ($order_by == null) {
			$order_by = $this->default_order;
		}
		$attributes = array_merge($this->getPrimaryKey(), $attributes);
		$result = $this->database->sqlSelect($attributes, $this->getTableName(), $criteria, $criteria_params, $group_by, $order_by, $limit, $start);
		// Fetch only attributes into ORM object
		$result->setFetchMode(PDO::FETCH_CLASS, static::ORM, array(true, $attributes));
		return $result;
	}

	/**
	 * Count existing entities with optional criteria.
	 *
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @return int count
	 */
	public function count($criteria = null, array $criteria_params = array()) {
		$result = $this->database->sqlSelect(array('COUNT(*)'), $this->getTableName(), $criteria, $criteria_params);
		return (int)$result->fetchColumn();
	}

	/**
	 * Select existing entities with optional criteria.
	 *
	 * Allows for selecting specific sums, averages and counts
	 *
	 * @param array $columns SELECT
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @return PDOStatement
	 */
	public function select(array $columns, $criteria = null, array $criteria_params = array()) {
		return $this->database->sqlSelect($columns, $this->getTableName(), $criteria, $criteria_params);
	}

	/**
	 * Check if entity exists.
	 *
	 * @param PersistentEntity $entity
	 * @return boolean entity exists
	 */
	public function exists(PersistentEntity $entity) {
		return $this->existsByPrimaryKey($entity->getValues(true));
	}

	/**
	 * Check if entity with primary key exists.
	 *
	 * @param array $primary_key_values
	 * @return boolean primary key exists
	 */
	protected function existsByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		return $this->database->sqlExists($this->getTableName(), implode(' AND ', $where), $primary_key_values);
	}

	/**
	 * Save new entity.
	 *
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function create(PersistentEntity $entity) {
		return $this->database->sqlInsert($entity->getTableName(), $entity->getValues());
	}

	/**
	 * Load saved entity data and replace current entity object values.
	 *
	 * @see retrieveAttributes
	 *
	 * @param PersistentEntity $entity
	 * @return PersistentEntity|false
	 */
	public function retrieve(PersistentEntity $entity) {
		return $this->retrieveAttributes($entity, $entity->getAttributes());
	}

	/**
	 * Load saved entity data and create new object.
	 *
	 * @param array $primary_key_values
	 * @return PersistentEntity|false
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = $this->database->sqlSelect(array('*'), $this->getTableName(), implode(' AND ', $where), $primary_key_values, null, null, 1);
		// Fetch into ORM object
		return $result->fetchObject(static::ORM, array(true));
	}

	/**
	 * Do NOT use @ and . in your primary keys or you WILL run into trouble here!
	 *
	 * @param string $UUID
	 * @return PersistentEntity|false
	 */
	public function retrieveByUUID($UUID) {
		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return $this->retrieveByPrimaryKey($primary_key_values);
	}

	/**
	 * Retrieve the value of sparse attributes.
	 *
	 * Usage example:
	 *
	 * $model = UserModel::instance();
	 * $users = $model->findSparse(array('name'), ...); // retrieves only name attribute
	 * foreach ($users as $user) {
	 *   echo $user->getAddress(); // address is sparse: retrieve address
	 * }
	 *
	 * class User extends PersistentEntity {
	 *   public function getAddress() {
	 *     $attributes = array('city' 'street', 'number', 'postal_code');
	 *     UserModel::instance()->retrieveAttributes($this, $attributes);
	 *   }
	 * }
	 *
	 * Foreign key example:
	 *
	 * $user->getAddress();
	 *
	 * class User extends PersistentEntity {
	 *   public $address_uuid; // foreign key
	 *   public $address;
	 *   public function getAddress() {
	 *     if (!isset($this->address)) {
	 *       $fk = array('address_uuid')
	 *       if ($this->isSparse($fk) {
	 *         UserModel::instance()->retrieveAttributes($this, $fk);
	 *       }
	 *       $this->address = AddressesModel::instance()->retrieveByUUID($this->address_uuid);
	 *     }
	 *     return $this->address;
	 *   }
	 * }
	 *
	 * @param PersistentEntity $entity
	 * @param array $attributes
	 * @return PersistentEntity|false
	 */
	public function retrieveAttributes(PersistentEntity $entity, array $attributes) {
		$where = array();
		foreach ($entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = $this->database->sqlSelect($attributes, $entity->getTableName(), implode(' AND ', $where), $entity->getValues(true), null, null, 1);
		$result->setFetchMode(PDO::FETCH_INTO, $entity);
		$success = $result->fetch();
		if ($success) {
			$entity->onAttributesRetrieved($attributes);
		}
		return $success;
	}

	/**
	 * Save existing entity.
	 * Sparse attributes that have not been retrieved are excluded by PersistentEntity->getValues().
	 *
	 * @param PersistentEntity $entity
	 * @return int number of rows affected
	 */
	public function update(PersistentEntity $entity) {
		$properties = $entity->getValues();
		$where = array();
		$params = array();
		foreach ($entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = :W' . $key; // name parameters after column
			$params[':W' . $key] = $properties[$key];
			unset($properties[$key]); // do not update primary key
		}
		return $this->database->sqlUpdate($entity->getTableName(), $properties, implode(' AND ', $where), $params, 1);
	}

	/**
	 * Delete existing entity.
	 *
	 * @param PersistentEntity $entity
	 * @return int number of rows affected
	 */
	public function delete(PersistentEntity $entity) {
		return $this->deleteByPrimaryKey($entity->getValues(true));
	}

	/**
	 * Requires positional values.
	 *
	 * @param array $primary_key_values
	 * @return int number of rows affected
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		return $this->database->sqlDelete($this->getTableName(), implode(' AND ', $where), $primary_key_values, 1);
	}

}
