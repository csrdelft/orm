<?php
use PHPUnit\Framework\TestCase;

class Allowed {
	public $var;
}

class NotAllowed {
	public $var;
}

class JsonSerializerTest extends TestCase  {

	public function testInt() {
		$serializer = new \CsrDelft\Orm\JsonSerializer\SafeJsonSerializer([Allowed::class]);
		$obj = new Allowed();
		$obj->var = 1;
		$serializer->unserialize($serializer->serialize($obj));
		$this->addToAssertionCount(1);
	}

	public function testArray() {
		$serializer = new \CsrDelft\Orm\JsonSerializer\SafeJsonSerializer([Allowed::class]);
		$obj = new Allowed();
		$obj->var = [0,1,2];
		$serializer->unserialize($serializer->serialize($obj));
		$this->addToAssertionCount(1);
	}

	public function testString() {
		$serializer = new \CsrDelft\Orm\JsonSerializer\SafeJsonSerializer([Allowed::class]);
		$obj = new Allowed();
		$obj->var = [0,1,2];
		$serializer->unserialize($serializer->serialize($obj));
		$this->addToAssertionCount(1);
	}

	public function testMap() {
		$serializer = new \CsrDelft\Orm\JsonSerializer\SafeJsonSerializer([Allowed::class]);
		$obj = new Allowed();
		$obj->var = ["key"=>"value"];
		$serializer->unserialize($serializer->serialize($obj));
		$this->addToAssertionCount(1);
	}


	/**
	 * @expectedException \CsrDelft\Orm\JsonSerializer\SafeJsonSerializerException
	 */
	public function testSerializeNotAllowed() {
		$serializer = new \CsrDelft\Orm\JsonSerializer\SafeJsonSerializer([Allowed::class]);
		$obj = new Allowed();
		$obj->var = [new NotAllowed()];
		$serializer->serialize($obj);
	}

	/**
	 * @expectedException \CsrDelft\Orm\JsonSerializer\SafeJsonSerializerException
	 */
	public function testDeserializeNotAllowed() {
		$serializer = new \CsrDelft\Orm\JsonSerializer\SafeJsonSerializer([Allowed::class]);
		$str = '{"@type":"Allowed","var":[{"@type":"NotAllowed","var":null}]}';
		$serializer->unserialize($str);
	}
}