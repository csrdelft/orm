<?php

/**
 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function starts_with($haystack, $needle) {
	return $needle === "" || strpos($haystack, $needle) === 0;
}

/**
 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function ends_with($haystack, $needle) {
	return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

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
