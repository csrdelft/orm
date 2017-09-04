<?php
namespace common;

/**
 * PDO does a string cast (false = '') and MySql uses tinyint for booleans so expects 0/1
 * @param $value bool|bool-like
 * @return int
 */
function pdo_bool($value) {
	if (is_bool($value)) {
		$value = (int)$value;
	}
	return $value;
}

/**
 * Get the short name for a class
 *
 * @param object|string $class
 *
 * @return string
 */
function short_class($class) {
    return (new \ReflectionClass($class))->getShortName();
}
