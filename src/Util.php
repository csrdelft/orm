<?php
namespace CsrDelft\Orm;

class Util {
	/**
	 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function starts_with($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) === 0;
	}

	/**
	 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function ends_with($haystack, $needle) {
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}

	/**
	 * Print a variable with <pre>-tags around it.
	 *
	 * @param string $sString
	 * @param string $cssID
	 */
	public static function debug_print($sString, $cssID = 'pubcie_debug') {
//	if (DEBUG OR LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued()) {
//		echo '<pre class="' . $cssID . '">' . print_r($sString, true) . '</pre>';
//	}
	}

	/**
	 * PDO does a string cast (false = '') and MySql uses tinyint for booleans so expects 0/1
	 * @param $value bool|bool-like
	 * @return int
	 */
	public static function pdo_bool($value) {
		if (is_bool($value)) {
			$value = (int)$value;
		}
		return $value;
	}
}
