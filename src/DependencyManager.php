<?php
namespace CsrDelft\Orm;
use CsrDelft\Orm\Common\OrmException;

/**
 * Any class extending this class is able to be autoloaded.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/09/2017
 */
abstract class DependencyManager {
	/**
	 * Error constants.
	 */
	const ERROR_CIRCULAR_DEPENDENCY = 'Circular dependency detected while loading parameter "%s" from "%s".';
	const ERROR_UNEXPECTED_AMOUNT_OF_PARAMETERS = 'Unexpected amount of parameters.';
	const ERROR_TYPE_MISMATCH = 'Type mismatch when initializing "%s". Expected parameter of type "%s", got "%s".';

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
	 */
	public static function init(...$arguments) {
		assert(!isset(self::$instance[static::class]), sprintf('Class "%s" already initialized.', get_class()));

		static::__static();
		$parameters = self::determineParameters($arguments);

		return new static(...$parameters);
	}

	/**
	 * Manually add an instance to the DependencyManager.
	 *
	 * @param mixed $dependency
	 */
	public static function addDependency($dependency) {
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

	/**
	 * Determine the parameters to be used for initializing this Dependency.
	 * A parameter can be one of the following
	 *
	 * - A type available in the DependencyManager:
	 *  Will be loaded, can be any class. (cannot be scalar)
	 *
	 * - Of type DependencyManager:
	 *  It will be loaded from the instances or created when it is not yet initialized
	 *
	 * - Any other type:
	 *  It is expected to be in $arguments, if it isn't an OrmException is thrown.
	 *
	 * @param array $arguments
	 * @return array
	 * @throws OrmException
	 */
	private static function determineParameters($arguments) {
		$constructor = (new \ReflectionClass(static::class))->getConstructor();
		$parameters = [];

		if (!is_null($constructor)) {
			foreach ($constructor->getParameters() as $parameter) {
				$parameterClass = $parameter->getClass();

				if (!is_null($parameterClass) && isset(self::$instance[$parameterClass->name])) {
					$parameters[] = self::$instance[$parameterClass->name];
				} elseif (!is_null($parameterClass) && $parameterClass->isSubclassOf(DependencyManager::class)) {
					if (isset(self::$loading[$parameterClass->name])) {
						throw new OrmException(sprintf(self::ERROR_CIRCULAR_DEPENDENCY, $parameterClass->name, static::class));
					}

					$parameters[] = self::loadDependency($parameterClass->name);
				} elseif (count($arguments) > 0) {
					$argument = array_pop($arguments);
					self::assertTypeEqualOrSubClass($argument, $parameterClass);
					$parameters[] = $argument;
				} else {
					throw new OrmException(self::ERROR_UNEXPECTED_AMOUNT_OF_PARAMETERS);
				}
			}
		}

		if (count($arguments) !== 0) {
			throw new OrmException(self::ERROR_UNEXPECTED_AMOUNT_OF_PARAMETERS);
		}

		self::$loading = [];

		return $parameters;
	}

	/**
	 * Load a dependency from a class name. It is loaded when it is not initialized yet.
	 *
	 * @param string|static $parameterClass
	 * @return static
	 */
	private static function loadDependency($parameterClass) {
		if (!isset(self::$instance[$parameterClass])) {
			/** @var DependencyManager $parameterDependency */
			$parameterDependency = $parameterClass;

			self::$loading[$parameterClass] = true;
			self::$instance[$parameterClass] = $parameterDependency::init();
		}

		return self::$instance[$parameterClass];
	}

	/**
	 * @param mixed $argument
	 * @param \ReflectionClass|null $class
	 * @throws OrmException
	 */
	private static function assertTypeEqualOrSubClass($argument, $class = null) {
		if (is_null($class)) {
			// Don't check.
		} elseif (is_a($argument, $class->name)) {
			// Good.
		} else {
			throw new OrmException(sprintf(
				self::ERROR_TYPE_MISMATCH,
				static::class,
				$class->name,
				get_class($argument)
			));
		}
	}
}
