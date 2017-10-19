<?php
namespace CsrDelft\Orm\Persistence;

use CsrDelft\Orm\Entity\DynamicEntity;
use CsrDelft\Orm\Entity\PersistentAttribute;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\PersistentEnum;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\DependencyManager;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionProperty;

/**
 * DatabaseAdmin.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DatabaseAdmin extends DependencyManager {
	/**
	 * Property constants.
	 */
	const PROPERTY_PERSISTENT_ATTRIBUTES = 'persistent_attributes';
	const PROPERTY_PRIMARY_KEY = 'primary_key';
	const PROPERTY_TABLE_NAME = 'table_name';

	/**
	 * Database constants.
	 */
	const DATABASE_KEY_PRIMARY = 'PRI';

	/**
	 * Creates queries
	 *
	 * @var QueryBuilder
	 */
	private $queryBuilder;

	/**
	 * @var PDO
	 */
	private $database;

	/**
	 * DatabaseAdmin constructor.
	 * @param PDO $pdo
	 */
	public function __construct($pdo) {
		$this->database = $pdo;
		$this->queryBuilder = new QueryBuilder();
	}

	/**
	 * Check for differences in persistent attributes.
	 *
	 * @unsupported INDEX check; FOREIGN KEY check;
	 *
	 * @param PersistentEntity $class
	 */
	public function checkTable($class) {
		// Do not check DynamicEntities
		if ($class === DynamicEntity::class) return;
		if (is_subclass_of($class, DynamicEntity::class)) return;
		if (!is_subclass_of($class, PersistentEntity::class)) return;

		$properties = $this->getStaticProperties($class);
		$modelAttributes = $this->getModelAttributes($properties);

		$tableName = $properties[self::PROPERTY_TABLE_NAME];
		$primaryKey = $properties[self::PROPERTY_PRIMARY_KEY];

		if ($this->sqlExistsTable($tableName)) {
			$databaseAttributes = $this->getDatabaseAttributes($tableName);

			$previous_attribute = null;
			foreach ($properties[self::PROPERTY_PERSISTENT_ATTRIBUTES] as $name => $definition) {
				// Add missing persistent attributes
				if (!isset($databaseAttributes[$name])) {
					$this->sqlAddAttribute($tableName, $modelAttributes[$name], $previous_attribute);
				} else {
					$databaseAttribute = $databaseAttributes[$name];
					$modelAttribute = $modelAttributes[$name];

					if ($this->shouldChangeAttribute($modelAttribute, $databaseAttribute, $definition)) {
						$this->sqlChangeAttribute($tableName, $modelAttribute);
					}
				}
				$previous_attribute = $name;
			}
			$this->deleteAttributes($databaseAttributes, $properties);
		} else {
			$this->sqlCreateTable($tableName, $modelAttributes, $primaryKey);
		}
	}

	/**
	 * Array of SQL statements for file.sql
	 * @var array
	 */
	private static $queries = [];

	/**
	 * Get array of SQL statements for file.sql
	 * @return array
	 */
	public function getQueries() {
		return self::$queries;
	}

	/**
	 * Get table attributes.
	 *
	 * @param string $name
	 * @return PDOStatement|PersistentAttribute[]
	 */
	public function sqlDescribeTable($name) {
		$sql = $this->queryBuilder->buildDescribeTable($name);
		$query = $this->database->prepare($sql);
		// Force column names to lower case.
		$this->database->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
		$query->execute();
		// Leave column names as returned by the database driver.
		$this->database->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$query->setFetchMode(PDO::FETCH_CLASS, PersistentAttribute::class);
		return $query;
	}

	/**
	 * Check if a table exists.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function sqlExistsTable($name) {
		$sql = $this->queryBuilder->buildExistsTable($name);
		$query = $this->database->prepare($sql);
		// Force column names to lower case.
		$this->database->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
		$query->execute();
		// Leave column names as returned by the database driver.
		$this->database->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);

		return $query->fetchColumn() === strtolower($name);
	}

	/**
	 * @param string $name
	 * @param array $attributes
	 * @param string[] $primary_key
	 */
	public function sqlCreateTable($name, array $attributes, array $primary_key) {
		$sql = $this->queryBuilder->buildCreateTable($name, $attributes, $primary_key);
		$query = $this->database->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	/**
	 * @param string $table
	 * @param PersistentAttribute $attribute
	 * @param string $after_attribute
	 */
	public function sqlAddAttribute($table, PersistentAttribute $attribute, $after_attribute = null) {
		$sql = $this->queryBuilder->buildAddAttribute($table, $attribute, $after_attribute);
		$query = $this->database->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	/**
	 * @param string $table
	 * @param PersistentAttribute $attribute
	 * @param string $old_name
	 */
	public function sqlChangeAttribute($table, PersistentAttribute $attribute, $old_name = null) {
		$sql = $this->queryBuilder->buildChangeAttribute($table, $attribute, $old_name);
		$query = $this->database->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	/**
	 * @param string $table
	 * @param PersistentAttribute $attribute
	 */
	public function sqlDeleteAttribute($table, PersistentAttribute $attribute) {
		$sql = $this->queryBuilder->buildDeleteAttribute($table, $attribute);
		$query = $this->database->prepare($sql);
		$esc = '-- ';
		if (
			defined('DB_MODIFY')
			AND defined('DB_DROP')
			AND DB_MODIFY
			AND DB_DROP === true
		) {
			$query->execute();
			$esc = '';
		}
		self::$queries[] = $esc . $query->queryString;
	}

	/**
	 * @param $class
	 * @return mixed[]|ReflectionProperty[]
	 */
	public function getStaticProperties($class) {
		$reflection_class = new ReflectionClass($class);

		$properties = $reflection_class->getProperties(ReflectionProperty::IS_STATIC);

		// Reduce $properties to an associative array
		/** @var mixed[] $properties */
		$properties = array_reduce(
			$properties,
			function ($result, ReflectionProperty $item) {
				$item->setAccessible(true);
				$result[$item->getName()] = $item->getValue();
				return $result;
			},
			[]
		);
		return $properties;
	}

	/**
	 * @param mixed[] $properties
	 *
	 * @return PersistentAttribute[]
	 */
	public function getModelAttributes($properties) {
		$attributes = [];

		foreach ($properties[self::PROPERTY_PERSISTENT_ATTRIBUTES] as $name => $definition) {
			$attributes[$name] = new PersistentAttribute($name, $definition);
			if (in_array($name, $properties[self::PROPERTY_PRIMARY_KEY])) {
				$attributes[$name]->key = self::DATABASE_KEY_PRIMARY;
			} else {
				$attributes[$name]->key = '';
			}
		}
		return $attributes;
	}

	/**
	 * Remove non-persistent attributes
	 *
	 * @param PersistentAttribute[] $databaseAttributes
	 * @param mixed[] $properties
	 */
	public function deleteAttributes($databaseAttributes, $properties) {
		foreach ($databaseAttributes as $name => $attribute) {
			if (!isset($properties[self::PROPERTY_PERSISTENT_ATTRIBUTES][$name])) {
				$this->sqlDeleteAttribute($properties[self::PROPERTY_TABLE_NAME], $attribute);
			}
		}
	}

	/**
	 * @param PersistentAttribute $modelAttribute
	 * @param PersistentAttribute $databaseAttribute
	 * @param mixed[] $definition
	 * @return bool
	 */
	public function shouldChangeAttribute($modelAttribute, $databaseAttribute, $definition) {
		// Check existing persistent attributes for differences
		if ($modelAttribute->type !== $databaseAttribute->type) {
			if ($definition[0] === T::Enumeration) {
				if ($this->isEnumDifferent($databaseAttribute->type, $definition[2])) {
					return true;
				}
			} else {
				return true;
			}
		}

		if ($modelAttribute->null !== $databaseAttribute->null) {
			return true;
		}

		if ($modelAttribute->extra !== $databaseAttribute->extra) {
			return true;
		}
		// TODO: support other key types: MUL, UNI, etc.
		if (
			$modelAttribute->key !== $databaseAttribute->key
			AND (
				$modelAttribute->key === self::DATABASE_KEY_PRIMARY
				OR $databaseAttribute->key === self::DATABASE_KEY_PRIMARY
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $tableName
	 * @return PersistentAttribute[]
	 */
	public function getDatabaseAttributes($tableName) {
		$table_attributes = $this->sqlDescribeTable($tableName);
		$database_attributes = [];
		foreach ($table_attributes as $attribute) {
			$database_attributes[$attribute->field] = $attribute;
		}
		return $database_attributes;
	}

	/**
	 * @param string $databaseEnum
	 * @param PersistentEnum $enum
	 * @return bool
	 */
	protected function isEnumDifferent($databaseEnum, $enum) {
		$enumSql = $this->queryBuilder->buildEnum($enum::getTypeOptions());

		return $databaseEnum !== $enumSql;
	}
}
