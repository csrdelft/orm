<?php
namespace CsrDelft\Orm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/09/2017
 */
abstract class DependencyManager {
	/** @var static[] */
	private static $instance = [];

	/**
	 * @var bool[]
	 */
	private static $loading = [];

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
			} elseif (isset(self::$loading[$parameterClass->name])) {
				throw new \Exception(sprintf('Circular dependency detected while loading parameter "%s" from "%s".', $parameterClass->name, static::class));
			} elseif ($parameterClass->isSubclassOf(DependencyManager::class)) {
				$parameterDependency = $parameterClass->name;
				self::$loading[$parameterDependency] = true;
				self::$instance[$parameterDependency] = $parameterDependency::init();
				$parameters[] = self::$instance[$parameterClass->name];
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

		self::$loading = [];

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
