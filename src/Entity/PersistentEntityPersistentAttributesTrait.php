<?php

namespace CsrDelft\Orm\Entity;

/**
 * Attributes of a PersistentEntity.
 */
trait PersistentEntityPersistentAttributesTrait {
	/**
	 * Array of persistent attributes, mapped to column names in the database. Each persistent attribute must have a
	 * public attribute the data can be saved in.
	 *
	 * @var array
	 */
	protected static $persistent_attributes = [];

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
	 * Get all attribute names.
	 *
	 * @return string[]
	 */
	public function getAttributes() {
		$this->getUUID();
		return array_keys(static::$persistent_attributes);
	}

	/**
	 * @param $attribute_name
	 * @return array
	 */
	public function getAttributeDefinition($attribute_name) {
		return static::$persistent_attributes[$attribute_name];
	}
}
