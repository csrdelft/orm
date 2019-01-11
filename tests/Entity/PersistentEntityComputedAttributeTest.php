<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use PHPUnit\Framework\TestCase;

/**
 * @property-read integer $my_val
 */
class MyComputedAttributeEntity extends PersistentEntity {
	public $id;

	protected function getMyVal() {
		return 1;
	}

	protected static $primary_key = ['id'];

	protected static $persistent_attributes = [
		'id' => [T::Integer]
	];

	protected static $computed_attributes = [
		'my_val' => [T::Integer],
	];
}


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 09/01/2019
 */
class PersistentEntityComputedAttributeTest extends TestCase {
	public function testCallComputedAttribute() {
		$entity = new MyComputedAttributeEntity();

		$this->assertEquals(1, $entity->my_val);
	}

	public function testComputedJsonSerialize() {
		$entity = new MyComputedAttributeEntity();
		$entity->id = 3;

		$this->assertEquals(['id' => 3, 'my_val' => 1, 'UUID' => '3@mycomputedattributeentity.csrdelft.nl'], $entity->jsonSerialize());
	}

	public function testWrongComputedAttribute() {
		$entity = new MyComputedAttributeEntity();

		$this->assertEquals(null, $entity->wrong_attribute);
	}

	public function testGetComputedAttributes() {
		$entity = new MyComputedAttributeEntity();

		$this->assertEquals(['my_val'], $entity->getComputedAttributes());
	}

	public function testGetComputedAttribute() {
		$entity = new MyComputedAttributeEntity();

		$this->assertEquals([T::Integer], $entity->getComputedAttributeDefinition('my_val'));
	}
}
