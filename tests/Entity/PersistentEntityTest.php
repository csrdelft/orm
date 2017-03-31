<?php
use CsrDelft\Orm\Entity\PersistentAttribute;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\PersistentEnum;
use CsrDelft\Orm\Entity\T;
use PHPUnit\Framework\TestCase;

class MyEntity extends PersistentEntity {
	public $id;
	public $name;
	public $access;

	public static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'name' => array(T::String),
		'access' => array(T::Boolean)
	);
	public static $table_name = 'my_entity';
	public static $primary_key = array('id');
}

/**
 * @covers CsrDelft\Orm\Entity\PersistentEntity
 */
final class PersistentEntityTest extends TestCase {
	public function testGetTableName() {
		$entity = new MyEntity();
		$this->assertEquals('my_entity', $entity->getTableName());
	}

	public function testGetAttributes() {
		$entity = new MyEntity();
		$this->assertEquals(array('id', 'name', 'access'), $entity->getAttributes());
	}

	public function testGetAttributeDefinition() {
		$entity = new MyEntity();
		$this->assertEquals(array(T::String), $entity->getAttributeDefinition('name'));
	}

	public function testGetPrimaryKey() {
		$entity = new MyEntity();
		$this->assertEquals(array('id'), $entity->getPrimaryKey());
	}

	public function testGetUUID() {
		$entity = new MyEntity();
		$entity->id = 3;

		$this->assertEquals("3@myentity.csrdelft.nl", $entity->getUUID());
	}

	public function testJsonSerialize() {
		$entity = new MyEntity();
		$entity->id = 3;
		$entity->name = "thing";
		$entity->access = false;

		$this->assertEquals(array(
			'UUID' => '3@myentity.csrdelft.nl',
			'id' => 3,
			'name' => 'thing',
			'access' => false,
			'attributes_retrieved' => null
		), $entity->jsonSerialize());
	}
}