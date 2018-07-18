<?php
namespace CsrDelft\Orm\Entity;

use function common\pdo_bool;
use CsrDelft\Orm\JsonSerializer\SafeJsonSerializer;
use Exception;

/**
 * PersistentEntity.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Requires static properties in subclass: $persistent_attributes, $primary_key and $table_name
 *
 * @see PersistenceModel::retrieveAttributes for a usage example of sparse and foreign keys.
 */
abstract class PersistentEntity implements \JsonSerializable {

	/**
	 * Array of persistent attributes, mapped to column names in the database. Each persistent attribute must have a
	 * public attribute the data can be saved in.
	 *
	 * @var array
	 */
	protected static $persistent_attributes = [];

	/**
	 * Table name the entity is saved in.
	 *
	 * @var string
	 */
	protected static $table_name = null;

	/**
	 * Primary key for the table, can be any number of columns.
	 *
	 * @var array
	 */
	protected static $primary_key = [];

	/**
	 * Static constructor is called (by inheritance) once and only from PersistenceModel.
	 *
	 * Optional: run conversion code before checkTables() here
	 */
	public static function __static() {
		// Extend the persistent attributes with all parent persistent attributes
		$class = get_called_class();
		while ($class = get_parent_class($class)) {
			$parent = get_class_vars($class);
			if (isset($parent['persistent_attributes'])) {
				static::$persistent_attributes =
					$parent['persistent_attributes'] + static::$persistent_attributes;
			}
		}
	}

	/**
	 * The names of attributes that have been retrieved for this instance.
	 * Used to discern unset values as these are invalid.
	 * @var array|null Only set on sparse retrieval!
	 */
	private $attributes_retrieved;

	/**
	 * Constructor is called late (after attributes are set)
	 * by PDO::FETCH_CLASS with $cast = true
	 *
	 * @param boolean $cast Regular construction should not cast (unset) attributes!
	 * @param array $attributes_retrieved Names of attributes that are set before construction
	 * in case of sparse retrieval
	 */
	public function __construct(
		$cast = false,
		array $attributes_retrieved = null
	) {
		$this->attributes_retrieved = $attributes_retrieved;
		if ($attributes_retrieved == null) {
			// Cast all attributes
			$attributes_retrieved = $this->getAttributes();
		}
		if ($cast) {
			$this->castValues($attributes_retrieved);
		}
	}

	/**
	 * Get name of the table for this entity.
	 *
	 * @return string
	 */
	public function getTableName() {
		return static::$table_name;
	}

	/**
	 * Get all attribute names.
	 *
	 * @return string[]
	 */
	public function getAttributes() {
		return array_keys(static::$persistent_attributes);
	}

	/**
	 * @param $attribute_name
	 * @return array
	 */
	public function getAttributeDefinition($attribute_name) {
		return static::$persistent_attributes[$attribute_name];
	}

	/**
	 * Get primary key of entity.
	 *
	 * @return string[]
	 */
	public function getPrimaryKey() {
		return array_values(static::$primary_key);
	}


	/**
	 * Get universal Id for entity.
	 *
	 * Warning: assumes unique PersistentEntity names.
	 *
	 * @return string
	 */
	public function getUUID() {
		return strtolower(sprintf(
			'%s@%s.csrdelft.nl',
			implode('.', $this->getValues(true)),
			\common\short_class($this)
		));
	}

	/**
	 * Get array ready for json serialization.
	 *
	 * @return string[]
	 */
	public function jsonSerialize() {
		$array = get_object_vars($this);
		$array['UUID'] = $this->getUUID();
		return $array;
	}

	/**
	 * @internal Used when Model received attributes.
	 * @param array $attributes
	 */
	public function onAttributesRetrieved(array $attributes) {
		if (isset($this->attributes_retrieved)) {
			// Bookkeeping only in case of sparse retrieval
			$this->attributes_retrieved = array_merge($this->attributes_retrieved, $attributes);
		}
		$this->castValues($attributes); // PDO does not cast values automatically (yet)
	}

	/**
	 * Get the (non-sparse) attributes and their values of this object.
	 * Relies on getters and setters to update $attributes_retrieved
	 *
	 * @param boolean $primary_key_only
	 * @return array
	 */
	public function getValues($primary_key_only = false) {
		$values = [];
		if ($primary_key_only) {
			$attributes = $this->getPrimaryKey();
		} else {
			$attributes = $this->getAttributes();
		}
		// Do not return sparse attribute values as these are invalid
		if (isset($this->attributes_retrieved)) {
			$attributes = array_intersect($attributes, $this->attributes_retrieved);
		}
		foreach ($attributes as $attribute) {
			$values[$attribute] = pdo_bool($this->$attribute);
			$attributeDef = $this->getAttributeDefinition($attribute);
			if ($attributeDef[0]==T::JSON) {
				$serializer = new SafeJsonSerializer($attributeDef[2]);
				$values[$attribute] = $serializer->serialize($this->$attribute);
			}
		}
		if ($primary_key_only) {
			return array_values($values);
		}
		return $values;
	}

	/**
	 * Cast values to defined type.
	 * PDO does not cast values automatically (yet).
	 *
	 * @param array $attributes Attributes to cast
	 * @throws Exception
	 */
	private function castValues(array $attributes) {
		foreach ($attributes as $attribute) {
			$definition = $this->getAttributeDefinition($attribute);
			if (isset($definition[1]) AND $definition[1] AND $this->$attribute === null) {
				// Do not cast allowed null fields
			} elseif ($definition[0] === T::Boolean) {
				$this->$attribute = (boolean)$this->$attribute;
			} elseif ($definition[0] === T::Integer) {
				$this->$attribute = (int)$this->$attribute;
			} elseif ($definition[0] === T::Float) {
				$this->$attribute = (float)$this->$attribute;
			} elseif ($definition[0] === T::JSON) {
				$serializer = new SafeJsonSerializer($definition[2]);
				$this->$attribute = $serializer->unserialize($this->$attribute);
			}else {
				$this->$attribute = (string)$this->$attribute;
			}
			// If $definition comes from PersistentAttribute->toDefinition, $definition[2] is an array if the definition is an enum
			if (
				defined('DB_CHECK')
				AND DB_CHECK
				AND $definition[0] === T::Enumeration
				AND !in_array(
					$this->$attribute,
					is_array($definition[2])
						? $definition[2]
						: $definition[2]::getTypeOptions()
				)
			) {
				throw new Exception(sprintf(
					'%s.%s invalid %s.enum value: "%s"',
					static::$table_name,
					$attribute,
					$definition[2],
					$this->$attribute
				));
			}
		}
	}
}
