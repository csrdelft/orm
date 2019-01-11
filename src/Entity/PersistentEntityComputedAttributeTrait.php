<?php

namespace CsrDelft\Orm\Entity;

use CsrDelft\Orm\Exception\CsrOrmException;

/**
 * Maps attributes to getters.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 09/01/2019
 */
trait PersistentEntityComputedAttributeTrait {
	/**
	 * @var array Cache for this request
	 */
	protected static $computed_attribute_cache = [];

	/**
	 * @var array Should be set by implementation
	 */
	protected static $computed_attributes = [];

	/**
	 * @param $name
	 * @return mixed
	 */
	public function __get($name) {
		if (isset(static::$computed_attributes[$name])) {
			return $this->{'get' . ucfirst($this->toCamelCase($name))}();
		} else {
			return null;
		}
	}

	public function getComputedAttributes() {
		return array_keys(static::$computed_attributes);
	}

	public function getComputedAttributeDefinition($name) {
		return static::$computed_attributes[$name];
	}

	public function jsonSerialize() {
		$computedProperties = [];
		foreach (static::$computed_attributes as $attribute => $definition) {
			$computedProperties[$attribute] = $this->{$attribute};
		}

		return $computedProperties;
	}

	private function toCamelCase($name) {
		return preg_replace_callback('/_([a-zA-Z])/', function ($matches) {
			return strtoupper($matches[1]);
		}, $name);
	}

}
