<?php
namespace CsrDelft\Orm\Entity;

/**
 * DynamicEntity.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Dynamic entities are defined by the table structure instead of the other way around.
 * Conversion: only define the new class and dynamically load the old class definition.
 */
class DynamicEntity extends PersistentEntity {

	/**
	 * Definition of the DynamicEntity
	 * @var DynamicEntityDefinition
	 */
	public $definition;

	/**
	 * DynamicEntity constructor.
	 * @param bool $cast
	 * @param array|null $attributes_retrieved
	 * @param DynamicEntityDefinition|null $definition
	 */
	public function __construct(
		$cast = false,
		array $attributes_retrieved = null,
		DynamicEntityDefinition $definition = null
	) {
		$this->definition = $definition;
		parent::__construct($cast, $attributes_retrieved);
	}

	/**
	 * Get name of the table for this entity.
	 *
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
		if (!isset($this->definition)) {
			return [];
		}
		return array_keys($this->definition->persistent_attributes);
	}

	/**
	 * @param $attribute_name
	 * @return array
	 */
	public function getAttributeDefinition($attribute_name) {
		return $this->definition->persistent_attributes[$attribute_name];
	}

	/**
	 * Get primary key of entity.
	 *
	 * @return string[]
	 */
	public function getPrimaryKey() {
		return array_values($this->definition->primary_key);
	}

	/**
	 * @param string $attribute
	 * @param mixed $value
	 */
	public function __set($attribute, $value) {
		$this->$attribute = $value;
	}

	/**
	 * @param string $attribute
	 * @return mixed
	 */
	public function __get($attribute) {
		if (property_exists(get_class($this), $attribute)) {
			return $this->$attribute;
		}
		return null;
	}

	/**
	 * @param string $attribute
	 * @return bool
	 */
	public function __isset($attribute) {
		return $this->__get($attribute) !== null;
	}

	/**
	 * @param string $attribute
	 */
	public function __unset($attribute) {
		if ($this->__isset($attribute)) {
			unset($this->$attribute);
		}
	}
}
