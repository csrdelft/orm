<?php
namespace CsrDelft\Orm\Entity;

/**
 * PersistentEnum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * An enumeration has type-options.
 *
 */
interface PersistentEnum {

	/**
	 * @return string[]
	 */
	public static function getTypeOptions();

	/**
	 * @param string $option
	 * @return string
	 */
	public static function getDescription($option);

	/**
	 * @param string $option
	 * @return string
	 */
	public static function getChar($option);
}
