<?php

use CsrDelft\Orm\DependencyManager;
use PHPUnit\Framework\TestCase;

require_once 'Persistence/MySqlDatabaseTestCase.php';

class OneParameter extends DependencyManager {
	protected function __construct($parameter) {
	}
}

class CircularOne extends DependencyManager {
	protected function __construct(CircularTwo $circularTwo) {
	}
}

class CircularTwo extends DependencyManager {
	protected function __construct(CircularOne $circularOne) {
	}
}

class NoParameter extends DependencyManager {
	protected function __construct() {
	}
}

class NoConstructor extends DependencyManager {

}

class ParameterMismatch extends DependencyManager {
	protected function __construct(EmptyTest $emptyTest) {
	}
}

class PreloadDependency extends DependencyManager {
	/**
	 * @var EmptyTest
	 */
	public $emptyTest;

	protected function __construct(EmptyTest $emptyTest) {
		$this->emptyTest = $emptyTest;
	}
}

class EmptyTest {}

class Normal extends DependencyManager {
	public $noParameter;
	public $data;

	protected function __construct($data, NoParameter $noParameter) {
		$this->data = $data;
		$this->noParameter = $noParameter;
	}
}

class Reversed extends DependencyManager {
	public $noConstructor;
	public $data;

	protected function __construct(NoConstructor $noConstructor, $data) {
		$this->data = $data;
		$this->noConstructor = $noConstructor;
	}
}

/**
 * Class DependencyManagerTest
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 05/09/2017
 */
class DependencyManagerTest extends TestCase {

	public function testCircularDependency() {
		$this->expectExceptionMessage('Circular dependency detected while loading parameter "CircularTwo" from "CircularOne".');
		$this->expectException(Exception::class);

		CircularOne::instance();
	}

	public function testTooManyParameters() {
		$this->expectExceptionMessage('Unexpected amount of parameters.');
		$this->expectException(Exception::class);
		NoParameter::init('parameter');
	}

	public function testTooFewParameters() {
		$this->expectExceptionMessage('Unexpected amount of parameters.');
		$this->expectException(Exception::class);
		OneParameter::init();
	}

	public function testParameterNoConstructor() {
		$this->expectExceptionMessage('Unexpected amount of parameters.');
		$this->expectException(Exception::class);
		NoConstructor::init('parameter');
	}

	public function testDependency() {
		DependencyManager::addDependency(Normal::init('Hello'));

		$this->assertEquals('Hello', Normal::instance()->data);
		$this->assertEquals(NoParameter::instance(), Normal::instance()->noParameter);
	}

	public function testReversedParameter() {
		DependencyManager::addDependency(Reversed::init('Hello'));

		$this->assertEquals('Hello', Reversed::instance()->data);
		$this->assertEquals(NoConstructor::instance(), Reversed::instance()->noConstructor);
	}

	public function testParameterMismatch() {
		$this->expectExceptionMessage('Type mismatch when initializing "ParameterMismatch". Expected parameter of type "EmptyTest", got "NoConstructor".');
		$this->expectException(Exception::class);
		ParameterMismatch::init(new NoConstructor());
	}

	public function testPreloadDependency() {
		$emptyTest = new EmptyTest();
		DependencyManager::addDependency($emptyTest);
		$preloadDependency = PreloadDependency::init();

		$this->assertEquals($emptyTest, $preloadDependency->emptyTest);
	}
}
