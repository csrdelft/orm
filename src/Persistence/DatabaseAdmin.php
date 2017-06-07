<?php

namespace CsrDelft\Orm\Persistence;

use CsrDelft\Orm\Entity\DynamicEntity;
use CsrDelft\Orm\Entity\PersistentAttribute;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\PersistentEnum;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Schema\TableDefinition;
use Exception;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionProperty;

/**
 * DatabaseAdmin.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class DatabaseAdmin {
	/**
	 * Singleton instance
	 * @var DatabaseAdmin
	 */
	private static $instance;

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

	public static function init($pdo) {
		assert(!isset(self::$instance));
		self::$instance = new DatabaseAdmin($pdo);
	}

	/**
	 * Get singleton DatabaseAdmin instance.
	 *
	 * @return DatabaseAdmin
	 */
	public static function instance() {
		assert(isset(self::$instance));
		return self::$instance;
	}

	public function __construct($pdo) {
		$this->database = $pdo;
		$this->queryBuilder = new QueryBuilder();
	}

	/**
	 * Check for differences in persistent attributes.
	 *
	 * @unsupported INDEX check; FOREIGN KEY check;
	 *
	 * @param TableDefinition $tableDefinition
	 * @param string $class
	 *
	 * @throws Exception
	 */
	public function checkTable(TableDefinition $tableDefinition, $class) {
		// Do not check DynamicEntities
		if ($class === DynamicEntity::class) return;
		if (is_subclass_of($class, DynamicEntity::class)) return;

		// Only check PersistentEntities
		if (!(new ReflectionClass($class))->isSubclassOf(PersistentEntity::class)) return;

		$attributes = $this->createAttributes($tableDefinition);

		try {
			$table_attributes = $this->sqlDescribeTable($tableDefinition->getTableName());
			/** @var PersistentAttribute[] $database_attributes */
			$database_attributes = [];
			foreach ($table_attributes as $attribute) {
				$database_attributes[$attribute->field] = $attribute; // overwrite existing
			}
		} catch (Exception $e) {
			if (\common\ends_with($e->getMessage(), $tableDefinition->getTableName() . "' doesn't exist")) {
				$this->sqlCreateTable($tableDefinition->getTableName(), $attributes, $tableDefinition->getPrimaryKey());
				return;
			} else {
				throw $e; // Rethrow to controller
			}
		}

		$this->addAttributes($tableDefinition, $database_attributes, $attributes);
		$this->changeAttributes($tableDefinition, $database_attributes, $attributes);
		$this->deleteAttributes($tableDefinition, $database_attributes);
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
	 * Get all tables.
	 *
	 * @return PDOStatement
	 */
	public function sqlShowTables() {
		$sql = $this->queryBuilder->buildShowTable();
		$query = $this->database->prepare($sql);
		$query->execute();
		return $query;
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
	 * Get query to (re-)create existing table.
	 *
	 * @param string $name
	 * @return string SQL query
	 */
	public function sqlShowCreateTable($name) {
		$sql = $this->queryBuilder->buildShowCreateTable($name);
		$query = $this->database->prepare($sql);
		$query->execute();
		return $query->fetchColumn(1);
	}

	public function sqlCreateTable($name, array $attributes, array $primary_key) {
		$sql = $this->queryBuilder->buildCreateTable($name, $attributes, $primary_key);
		$query = $this->database->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlDropTable($name) {
		$sql = $this->queryBuilder->buildDropTable($name);
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

	public function sqlAddAttribute(
		$table,
		PersistentAttribute $attribute,
		$after_attribute = null
	) {
		$sql = $this->queryBuilder->buildAddAttribute(
			$table,
			$attribute,
			$after_attribute
		);
		$query = $this->database->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlChangeAttribute(
		$table,
		PersistentAttribute $attribute,
		$old_name = null
	) {
		$sql = $this->queryBuilder->buildChangeAttribute(
			$table,
				$attribute,
				$old_name
		);
		$query = $this->database->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlDeleteAttribute(
		$table,
		PersistentAttribute $attribute
	) {
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
	 * @param TableDefinition $tableDefinition
	 *
	 * @param $database_attributes
	 */
	private function deleteAttributes(TableDefinition $tableDefinition, $database_attributes) {
// Remove non-persistent attributes
		foreach ($database_attributes as $name => $attribute) {
			if (!isset($tableDefinition->getColumnDefinitions()[$name])) {
				$this->sqlDeleteAttribute($tableDefinition->getTableName(), $attribute);
			}
		}
	}

	/**
	 * @param TableDefinition $tableDefinition
	 *
	 * @return PersistentAttribute[]
	 */
	private function createAttributes(TableDefinition $tableDefinition) {
		/** @var PersistentAttribute[] $attributes */
		$attributes = [];

		foreach ($tableDefinition->getColumnDefinitions() as $name => $definition) {
			$attributes[$name] = $definition;
			if (in_array($name, $tableDefinition->getPrimaryKey())) {
				$attributes[$name]->key = 'PRI';
			} else {
				$attributes[$name]->key = '';
			}
		}

		return $attributes;
	}

	/**
	 * @param TableDefinition $tableDefinition
	 * @param $database_attributes
	 * @param $attributes
	 */
	private function addAttributes(TableDefinition $tableDefinition, $database_attributes, $attributes) {
		$previous_attribute = null;
		foreach (array_keys($tableDefinition->getColumnDefinitions()) as $name) {
			if (!isset($database_attributes[$name])) {
				$this->sqlAddAttribute($tableDefinition->getTableName(), $attributes[$name], $previous_attribute);
			}
			$previous_attribute = $name;
		}
	}

	/**
	 * @param TableDefinition $tableDefinition
	 * @param $database_attributes
	 * @param $attributes
	 */
	private function changeAttributes(TableDefinition $tableDefinition, $database_attributes, $attributes) {
		foreach ($tableDefinition->getColumnDefinitions() as $name => $definition) {
			if (isset($database_attributes[$name])) {
				// Check existing persistent attributes for differences
				$diff = false;
				if ($attributes[$name]->type !== $database_attributes[$name]->type) {
					if ($definition->type === T::Enumeration) {
						/** @var PersistentEnum $enum */
						$enum = $definition->extra;
						$enumSql = sprintf(
							'enum(\'%s\')',
							implode('\',\'', $enum::getTypeOptions())
						);
						if ($database_attributes[$name]->type !== $enumSql) {
							$diff = true;
						}
					} else {
						$diff = true;
					}
				}
				if ($attributes[$name]->null !== $database_attributes[$name]->null) {
					$diff = true;
				}
				// Cast database value if default value is defined
				if ($attributes[$name]->default !== null) {
					if ($definition->type === T::Boolean) {
						$database_attributes[$name]->default = (boolean)$database_attributes[$name]->default;
					} elseif ($definition->type === T::Integer) {
						$database_attributes[$name]->default = (int)$database_attributes[$name]->default;
					} elseif ($definition->type === T::Float) {
						$database_attributes[$name]->default = (float)$database_attributes[$name]->default;
					}
				}
//				if ($attributes[$name]->default !== $database_attributes[$name]->default) {
//					$diff = true;
//				}
				if ($attributes[$name]->extra !== $database_attributes[$name]->extra) {
					$diff = true;
				}
				// TODO: support other key types: MUL, UNI, etc.
				if (
					$attributes[$name]->key !== $database_attributes[$name]->key
					AND (
						$attributes[$name]->key === 'PRI'
						OR $database_attributes[$name]->key === 'PRI'
					)
				) {
					$diff = true;
				}
				if ($diff) {
					$this->sqlChangeAttribute($tableDefinition->getTableName(), $attributes[$name]);
				}
			}
		}
	}
}
