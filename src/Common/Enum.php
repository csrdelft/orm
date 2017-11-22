<?php

namespace CsrDelft\Orm\Common;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 29/10/2017
 */
abstract class Enum {
	/**
	 * Error constants.
	 */
	const ERROR_CHOICE_NOT_SUPPORTED = 'Choice "%s" not supported.';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [];

	/**
	 * @var string Current choice
	 */
	protected $choice;

	/**
	 * Enum constructor.
	 * @param string $choice
	 * @throws OrmException
	 */
	public function __construct($choice) {

		if (isset(static::$supportedChoices[$choice])) {
			$this->choice = $choice;
		} else {
			throw new OrmException(sprintf(self::ERROR_CHOICE_NOT_SUPPORTED, $choice));
		}
	}

	/**
	 * @return string
	 */
	public function getChoice() {
		return $this->choice;
	}
}
