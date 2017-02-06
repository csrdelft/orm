<?php
namespace CsrDelft\Orm;
/**
 * Created by PhpStorm.
 * User: gerbe
 * Date: 06/02/2017
 * Time: 12:47
 */

class Util {
	/**
	 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function startsWith($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) === 0;
	}

	/**
	 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function endsWith($haystack, $needle) {
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}

	/**
	 * print_r een variabele met <pre>-tags eromheen.
	 *
	 * @param string $sString
	 * @param string $cssID
	 */
	public static function debugprint($sString, $cssID = 'pubcie_debug') {
//	if (DEBUG OR LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued()) {
//		echo '<pre class="' . $cssID . '">' . print_r($sString, true) . '</pre>';
//	}
	}

	/**
	 * PDO does a stringcast (false = '') and MySql uses tinyint for booleans so expects 0/1
	 */
	public static function werkomheen_pdo_bool($value) {
		if (is_bool($value)) {
			$value = (int)$value;
		}
		return $value;
	}

	/**
	 * Group by distinct object property
	 *
	 * @param string $prop
	 * @param array|\PDOStatement $in
	 * @param boolean $del delete from $in array
	 * @return array $out
	 */
	public static function group_by_distinct($prop, $in, $del = true) {
		$del &= is_array($in);
		$out = array();
		foreach ($in as $i => $obj) {
			$out[$obj->$prop] = $obj; // overwrite existing
			if ($del) {
				unset($in[$i]);
			}
		}
		return $out;
	}
}
