<?php
namespace CsrDelft\Orm\Entity;

use CsrDelft\Orm\Schema\DynamicTableDefinition;

/**
 * DynamicEntity.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Dynamic entities are defined by the table structure instead of the other way around.
 * Conversion: only define the new class and dynamically load the old class definition.
 *
 */
class DynamicEntity extends PersistentEntity {

	/**
	 * Definition of the DynamicEntity
	 * @var DynamicTableDefinition
	 */
	public $definition;

	public function __construct(
		$cast = false,
		array $attributes_retrieved = null,
		DynamicTableDefinition $definition = null
	) {
		$this->definition = $definition;
		parent::__construct($cast, $attributes_retrieved);
	}

	public function getTableName() {
		return $this->definition->getTableName();
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
		return array_keys($this->definition->getColumnDefinitions());
	}

	public function getAttributeDefinition($attribute_name) {
		return $this->definition->getColumnDefinitions()[$attribute_name];
	}

	public function getPrimaryKey() {
		return array_values($this->definition->getPrimaryKey());
	}

	public function __set($attribute, $value) {
		$this->$attribute = $value;
	}

	public function __get($attribute) {
		if (property_exists(get_class($this), $attribute)) {
			return $this->$attribute;
		}
		return null;
	}

	public function __isset($attribute) {
		return $this->__get($attribute) !== null;
	}

	public function __unset($attribute) {
		if ($this->__isset($attribute)) {
			unset($this->$attribute);
		}
	}
}
