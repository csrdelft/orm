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
abstract class PersistentEnum {
	protected static $supportedChoices = [];
	protected static $mapChoiceToDescription = [];
	protected static $mapChoiceToChar = [];

	/**
	 * @return string[]
	 */
	public static function getTypeOptions()
	{
		return static::$supportedChoices;
	}

	/**
	 * @param string $option
	 * @return string
	 * @throws \Exception
	 */
	public static function getDescription($option)
	{
		if (isset(static::$mapChoiceToDescription[$option])) {
			return static::$mapChoiceToDescription[$option];
		} else {
			throw new \Exception(sprintf('Enum choice "%s" unknown.', $option));
		}
	}

	/**
	 * @param string $option
	 * @return string
	 * @throws \Exception
	 */
	public static function getChar($option)
	{
		if (isset(static::$mapChoiceToChar[$option])) {
			return static::$mapChoiceToChar[$option];
		} else {
			throw new \Exception(sprintf('Enum choice "%s" unknown.', $option));
		}
	}
}
