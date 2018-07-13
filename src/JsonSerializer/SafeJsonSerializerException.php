<?php


namespace CsrDelft\Orm\JsonSerializer;


use Zumba\JsonSerializer\Exception\JsonSerializerException;

class SafeJsonSerializerException extends JsonSerializerException {

	/**
	 * SafeJsonSerializerException constructor.
	 * @param string $string
	 */
	public function __construct($string) {
	}
}