<?php
namespace CsrDelft\Orm;

use CsrDelft\Orm\Entity\DynamicEntity;
use CsrDelft\Orm\Entity\DynamicEntityDefinition;
use CsrDelft\Orm\Persistence\DatabaseAdmin;
use PDO;

/**
 * DynamicEntityModel.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Defines the DynamicEntity class by the DynamicEntityDefinition.
 * Factory pattern instead of singleton, so ::instance() won't work!
 *
 */
class DynamicEntityModel extends PersistenceModel {

	const ORM = DynamicEntity::class;

	/**
	 * @param string $table_name
	 * @return static
	 */
	public static function makeModel($table_name) {
		parent::__static();
		return new static($table_name);
	}

	/**
	 * Definition of the DynamicEntity
	 * @var DynamicEntityDefinition
	 */
	private $definition;

	/**
	 * Override the constructor of PersistentModel and create DynamicEntityDefinition from table structure.
	 *
	 * @param string $table_name
	 */
	protected function __construct($table_name) {
		parent::__construct();
		$this->definition = new DynamicEntityDefinition();
		$this->definition->table_name = $table_name;
		foreach (DatabaseAdmin::instance()->sqlDescribeTable($this->definition->table_name) as $attribute) {
			$this->definition->persistent_attributes[$attribute->field] = $attribute->toDefinition();
			if ($attribute->key === 'PRI') {
				$this->definition->primary_key[] = $attribute->field;
			}
		}
	}

	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->definition->table_name;
	}

	/**
	 * Get all attribute names.
	 *
	 * @return array
	 */
	public function getAttributes() {
		return array_keys($this->definition->persistent_attributes);
	}

	/**
	 * @param string $attribute_name
	 * @return array
	 */
	public function getAttributeDefinition($attribute_name) {
		return $this->definition->persistent_attributes[$attribute_name];
	}

	/**
	 * @return string[]
	 */
	public function getPrimaryKey() {
		return array_values($this->definition->primary_key);
	}

	/**
	 * Load saved entity data and create new object.
	 *
	 * @param array $primary_key_values
	 * @return DynamicEntity|false
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		/** @var DynamicEntity $entity */
		$entity = parent::retrieveByPrimaryKey($primary_key_values);
		if ($entity) {
			$entity->definition = $this->definition;
		}
		return $entity;
	}

	/**
	 * @param string[] $criteria
	 * @param mixed[] $criteria_params
	 * @param string $group_by
	 * @param string $order_by
	 * @param int $limit
	 * @param int $start
	 * @return \PDOStatement
	 */
	public function find(
		$criteria = null,
		array $criteria_params = [],
		$group_by = null,
		$order_by = null,
		$limit = null,
		$start = 0
	) {
		$result = parent::find($criteria, $criteria_params, $group_by, $order_by, $limit, $start);
		if ($result) {
			/** @noinspection PhpMethodParametersCountMismatchInspection */
			$result->setFetchMode(PDO::FETCH_CLASS, static::ORM, [true, null, $this->definition]);
		}
		return $result;
	}
}
