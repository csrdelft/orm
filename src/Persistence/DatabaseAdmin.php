<?php
namespace CsrDelft\Orm\Persistence;

use CsrDelft\Orm\Entity\DynamicEntity;
use CsrDelft\Orm\Entity\PersistentAttribute;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\PersistentEnum;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Util;
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
	 * @param PersistentEntity $class
	 * @throws Exception
	 */
	public function checkTable($class) {
		// Do not check DynamicEntities
		if ($class === DynamicEntity::class) return;

		/** @var PersistentAttribute[] $attributes */
		$attributes = array();
		$reflection_class = new ReflectionClass($class);
		// Only check PersistentEntities
		if (!$reflection_class->isSubclassOf(PersistentEntity::class)) return;

		$properties = $reflection_class->getProperties(ReflectionProperty::IS_STATIC);

		// Reduce $properties to an associative array
		/** @var ReflectionProperty[] $properties */
		$properties = array_reduce($properties, function ($result, ReflectionProperty $item) {
			$item->setAccessible(true);
			$result[$item->getName()] = $item->getValue();
			return $result;
		}, array());

		foreach ($properties['persistent_attributes'] as $name => $definition) {
			$attributes[$name] = new PersistentAttribute($name, $definition);
			if (in_array($name, $properties['primary_key'])) {
				$attributes[$name]->key = 'PRI';
			} else {
				$attributes[$name]->key = '';
			}
		}
		try {
			$table_attributes = $this->sqlDescribeTable($properties['table_name']);
			/** @var PersistentAttribute[] $database_attributes */
			$database_attributes = array();
			foreach ($table_attributes as $attribute) {
				$database_attributes[$attribute->field] = $attribute; // overwrite existing
			}
		} catch (Exception $e) {
			if (ends_with($e->getMessage(), $properties['table_name'] . "' doesn't exist")) {
				$this->sqlCreateTable($properties['table_name'], $attributes, $properties['primary_key']);
				return;
			} else {
				throw $e; // Rethrow to controller
			}
		}
		// Rename attributes

		if (property_exists($class, 'rename_attributes')) {
			$rename = $properties['rename_attributes'];
			foreach ($rename as $old_name => $new_name) {
				if (property_exists($class, $new_name)) {
					$this->sqlChangeAttribute($properties['table_name'], $attributes[$new_name], $old_name);
				}
			}
		} else {
			$rename = array();
		}
		$previous_attribute = null;
		foreach ($properties['persistent_attributes'] as $name => $definition) {
			// Add missing persistent attributes
			if (!isset($database_attributes[$name])) {
				if (!isset($rename[$name])) {
					$this->sqlAddAttribute($properties['table_name'], $attributes[$name], $previous_attribute);
				}
			} else {
				// Check existing persistent attributes for differences
				$diff = false;
				if ($attributes[$name]->type !== $database_attributes[$name]->type) {
					if ($definition[0] === T::Enumeration) {
						/** @var PersistentEnum $enum */
						$enum = $definition[2];
						if ($database_attributes[$name]->type !== "enum('" . implode("','", $enum::getTypeOptions()) . "')") {
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
					if ($definition[0] === T::Boolean) {
						$database_attributes[$name]->default = (boolean)$database_attributes[$name]->default;
					} elseif ($definition[0] === T::Integer) {
						$database_attributes[$name]->default = (int)$database_attributes[$name]->default;
					} elseif ($definition[0] === T::Float) {
						$database_attributes[$name]->default = (float)$database_attributes[$name]->default;
					}
				}
				if ($attributes[$name]->default !== $database_attributes[$name]->default) {
					$diff = true;
				}
				if ($attributes[$name]->extra !== $database_attributes[$name]->extra) {
					$diff = true;
				}
				// TODO: support other key types: MUL, UNI, etc.
				if ($attributes[$name]->key !== $database_attributes[$name]->key AND ($attributes[$name]->key === 'PRI' OR $database_attributes[$name]->key === 'PRI')) {
					$diff = true;
				}
				if ($diff) {
					$this->sqlChangeAttribute($properties['table_name'], $attributes[$name]);
				}
			}
			$previous_attribute = $name;
		}
		// Remove non-persistent attributes
		foreach ($database_attributes as $name => $attribute) {
			if (!isset($properties['persistent_attributes'][$name]) AND !isset($rename[$name])) {
				$this->sqlDeleteAttribute($properties['table_name'], $attribute);
			}
		}
	}

	/**
	 * Array of SQL statements for file.sql
	 * @var array
	 */
	private static $queries = array();

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
		$this->database->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); // Force column names to lower case.
		$query->execute();
		$this->database->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); // Leave column names as returned by the database driver.
		$query->setFetchMode(PDO::FETCH_CLASS, 'CsrDelft\Orm\Entity\PersistentAttribute');
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
		if (defined('DB_MODIFY') AND defined('DB_DROP') AND DB_MODIFY AND DB_DROP === true) {
			$query->execute();
			$esc = '';
		}
		self::$queries[] = $esc . $query->queryString;
	}

	public function sqlAddAttribute($table, PersistentAttribute $attribute, $after_attribute = null) {
		$sql = $this->queryBuilder->buildAddAttribute($table, $attribute, $after_attribute);
		$query = $this->database->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlChangeAttribute($table, PersistentAttribute $attribute, $old_name = null) {
		$sql = $this->queryBuilder->buildChangeAttribute($table, $attribute, $old_name);
		$query = $this->database->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlDeleteAttribute($table, PersistentAttribute $attribute) {
		$sql = $this->queryBuilder->buildDeleteAttribute($table, $attribute);
		$query = $this->database->prepare($sql);
		$esc = '-- ';
		if (defined('DB_MODIFY') AND defined('DB_DROP') AND DB_MODIFY AND DB_DROP === true) {
			$query->execute();
			$esc = '';
		}
		self::$queries[] = $esc . $query->queryString;
	}

}
