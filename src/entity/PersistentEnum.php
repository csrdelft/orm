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

	public static function getTypeOptions();

	public static function getDescription($option);

	public static function getChar($option);
}
