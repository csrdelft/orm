<?php
namespace CsrDelft\Orm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/09/2017
 */
abstract class DependencyManager {
	/**
	 * Error constants.
	 */
	const ERROR_CIRCULAR_DEPENDENCY = 'Circular dependency detected while loading parameter "%s" from "%s".';
	const ERROR_UNEXPECTED_AMOUNT_OF_PARAMETERS = 'Unexpected amount of parameters.';

	/**
	 * List of currently active instances.
	 *
	 * @var static[]
	 */
	private static $instance = [];

	/**
	 * Used to keep track of loading instances and detecting circular dependencies.
	 *
	 * @var bool[]
	 */
	private static $loading = [];

	/**
	 * Static constructor. Can be implemented in base classes.
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
			} elseif (isset(self::$loading[$parameterClass->name])) {
				throw new \Exception(sprintf(self::ERROR_CIRCULAR_DEPENDENCY, $parameterClass->name, static::class));
			} elseif ($parameterClass->isSubclassOf(DependencyManager::class)) {
				$parameterDependency = $parameterClass->name;
				self::$loading[$parameterDependency] = true;
				self::$instance[$parameterDependency] = $parameterDependency::init();
				$parameters[] = self::$instance[$parameterClass->name];
			} elseif (count($arguments) > 0) {
				$parameters[] = array_pop($arguments);
			} else {
				throw new \Exception(self::ERROR_UNEXPECTED_AMOUNT_OF_PARAMETERS);
			}
		}

		if (count($arguments) !== 0) {
			throw new \Exception(self::ERROR_UNEXPECTED_AMOUNT_OF_PARAMETERS);
		}

		$instance = new static(...$parameters);

		self::$loading = [];

		return $instance;
	}

	/**
	 * Manually add an instance to the DependencyManager.
	 *
	 * @param DependencyManager $dependency
	 */
	public static function addDependency(DependencyManager $dependency) {
		if (!isset(self::$instance[get_class($dependency)])) {
			self::$instance[get_class($dependency)] = $dependency;
		}
	}

	/**
	 * Retrieve an instance of this class.
	 *
	 * @return static
	 */
	final public static function instance() {
		if (!isset(self::$instance[static::class])) {
			self::$instance[static::class] = static::init();
		}

		return self::$instance[static::class];
	}
}
