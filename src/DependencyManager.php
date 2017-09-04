<?php
namespace CsrDelft\Orm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/09/2017
 */
abstract class DependencyManager {
	/** @var static[] */
	protected static $instance = [];

	/**
	 * Static constructor.
	 */
	public static function __static() {
		// Empty.
	}

	/**
	 * Used to initialize a Singleton if needed.
	 *
	 * @param mixed[] ...$arguments
	 * @return static
	 * @throws \Exception
	 */
	public static function init(...$arguments) {
		assert(!isset(self::$instance[static::class]));
		static::__static();

		$class = new \ReflectionClass(static::class);
		$constructor = $class->getConstructor();

		$parameters = [];
		foreach ($constructor->getParameters() as $parameter) {
			$parameterClass = $parameter->getClass();

			if (is_null($parameterClass)) {
				$parameters[] = array_pop($arguments);
			} elseif (isset(self::$instance[$parameterClass->name])) {
				$parameters[] = self::$instance[$parameterClass->name];
			} elseif ($parameterClass->isSubclassOf(DependencyManager::class)) {
				$initMethod = $parameterClass->getMethod('init');
				$parameters[] = $initMethod->invoke($parameterClass);
			} elseif (count($arguments) > 0) {
				$parameters[] = array_pop($arguments);
			} else {
				throw new \Exception('Unexpected amount of parameters.');
			}
		}

		if (count($arguments) !== 0) {
			throw new \Exception('Unexpected amount of parameters.');
		}

		$instance = new static(...$parameters);

		return $instance;
	}

	public static function addDependency(DependencyManager $dependency) {
		if (!isset(self::$instance[get_class($dependency)])) {
			self::$instance[get_class($dependency)] = $dependency;
		}
	}

	/**
	 * @return static
	 */
	final public static function instance() {
		if (!isset(self::$instance[static::class])) {
			self::$instance[static::class] = static::init();
		}

		return self::$instance[static::class];
	}
}
