<?php
namespace CsrDelft\Orm;

use CsrDelft\Orm\Entity\DynamicEntity;
use CsrDelft\Orm\Persistence\DatabaseAdmin;
use CsrDelft\Orm\Schema\DynamicTableDefinition;
use Exception;
use PDO;

/**
 * DynamicEntityModel.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Defines the DynamicEntity class by the DynamicTableDefinition.
 * Factory pattern instead of singleton, so ::instance() won't work!
 *
 */
class DynamicEntityModel extends PersistenceModel {

	const ORM = DynamicEntity::class;

	/**
	 * Factory pattern instead of singleton.
	 * @see ::makeModel()
	 */
	public static function instance() {
		throw new Exception('Use makeModel');
	}

	public static function makeModel($table_name) {
		parent::__static();
		return new static($table_name);
	}

	/**
	 * Definition of the DynamicEntity
	 * @var DynamicTableDefinition
	 */
	private $definition;

	/**
	 * Override the constructor of PersistentModel and create DynamicTableDefinition from table structure.
	 *
	 * @param string $table_name
	 */
	protected function __construct($table_name) {
		parent::__construct();
		$persistent_attributes = [];
		$primary_key = [];
		foreach (DatabaseAdmin::instance()->sqlDescribeTable($table_name) as $attribute) {
			$persistent_attributes[$attribute->field] = $attribute->toDefinition();
			if ($attribute->key === 'PRI') {
				$primary_key[] = $attribute->field;
			}
		}

		$this->schema = new DynamicTableDefinition($table_name, $primary_key, $persistent_attributes);
	}

	protected function retrieveByPrimaryKey(array $primary_key_values) {
		/** @var DynamicEntity $entity */
		$entity = parent::retrieveByPrimaryKey($primary_key_values);
		if ($entity) {
			$entity->definition = $this->definition;
		}
		return $entity;
	}

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
