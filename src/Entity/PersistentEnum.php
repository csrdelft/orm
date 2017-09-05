<?php
namespace CsrDelft\Orm\Entity;

/**
 * PersistentEnum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * An enumeration has type-options.
 */
abstract class PersistentEnum {
	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [];

	/**
	 * @return string[]
	 */
	public static function getTypeOptions()
	{
		return array_values(static::$supportedChoices);
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
			throw new \Exception(sprintf('%s: Enum option "%s" unknown.', static::class, $option));
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
		} elseif (isset(static::$supportedChoices[$option])) {
			return ucfirst($option);
		} else {
			throw new \Exception(sprintf('%s: Enum option "%s" unknown.', static::class, $option));
		}
	}
}
