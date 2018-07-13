<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 13-7-18
 * Time: 11:08
 */

namespace CsrDelft\Orm\JsonSerializer;

use ReflectionClass;
use Zumba\JsonSerializer\Exception\JsonSerializerException;

/**
 * JsonSerializer that only allows serializing and deserializing of classes that are explicitly allowed.
 * @package CsrDelft\Orm\JsonSerializer
 */
class SafeJsonSerializer extends \Zumba\JsonSerializer\JsonSerializer {

	/**
	 * Array of allowed classes
	 * @var string[]
	 */
	protected $allowedClasses;

	/**
	 * SafeJsonSerializer constructor.
	 * @param string[] $allowedClasses The classnames of classes that this serializer is allowed to (de)serialize. Passing null will allow all classes.
	 * @param array $customObjectSerializerMap
	 */
	public function __construct(array $allowedClasses = null, array $customObjectSerializerMap = array()) {
		parent::__construct(null, $customObjectSerializerMap);
		$this->allowedClasses = (array)$allowedClasses;
	}

	/**
	 * @param object $value
	 * @return array
	 * @throws \ReflectionException
	 */
	protected function serializeObject($value) {
		$ref = new ReflectionClass($value);
		$className = $ref->getName();
		if ($this->classAllowed($className)) {
			return parent::serializeObject($value);
		} else {
			throw new SafeJsonSerializerException("Serializing of $className is not allowed by this SafeJsonSerializer");
		}
	}

	protected function unserializeObject($value) {
		$className = $value[static::CLASS_IDENTIFIER_KEY];
		if ($this->classAllowed($className)) {
			return parent::unserializeObject($value);
		} else {
			throw new SafeJsonSerializerException("Deserializing of $className is not allowed by this SafeJsonSerializer");
		}
	}

	/**
	 * Whether this classname is allowed to be (un)serialized.
	 * @param $className
	 */
	protected function classAllowed($className) {
		return $this->allowedClasses === null || in_array($className, $this->allowedClasses);
	}

}