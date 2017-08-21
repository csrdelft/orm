<?php
use CsrDelft\Orm\Entity\PersistentAttribute;
use CsrDelft\Orm\Entity\PersistentEnum;
use CsrDelft\Orm\Entity\T;
use PHPUnit\Framework\TestCase;

/**
 * @covers CsrDelft\Orm\Entity\PersistentAttribute
 */
final class PersistentAttributeTest extends TestCase {
	public function testConstructName() {
		$attribute_one = new PersistentAttribute();
		$attribute_two = new PersistentAttribute('two');

		$this->assertNull($attribute_one->field);
		$this->assertEquals('two', $attribute_two->field);
	}

	public function testConstructDefinition() {
		$attribute = new PersistentAttribute('one', [T::Integer]);
		$this->assertEquals(T::Integer, $attribute->type);
		$this->assertEquals('NO', $attribute->null);
		$this->assertEquals('', $attribute->extra);

		$attribute = new PersistentAttribute('one', [T::Integer, true]);
		$this->assertEquals(T::Integer, $attribute->type);
		$this->assertEquals('YES', $attribute->null);
		$this->assertEquals('', $attribute->extra);

		$attribute = new PersistentAttribute('one', [T::Text, false, 'auto_increment']);
		$this->assertEquals(T::Text, $attribute->type);
		$this->assertEquals('NO', $attribute->null);
		$this->assertEquals('auto_increment', $attribute->extra);
	}

	public function testConstructEnum() {
		$attribute = new PersistentAttribute('one', [T::Enumeration, false, 'MyEnum']);
		$this->assertEquals('', $attribute->extra);
		$this->assertEquals("enum('option1','option2','option3')", $attribute->type);
	}

	public function testToSql() {
		$attribute = new PersistentAttribute('one', [T::Integer]);
		$this->assertEquals('one int(11) NOT NULL', $attribute->toSQL());

		$attribute = new PersistentAttribute('one', [T::Integer]);
		$attribute->default = "11";
		$this->assertEquals('one int(11) NOT NULL DEFAULT "11"', $attribute->toSQL());

		$attribute = new PersistentAttribute('one', [T::Text, true, 'auto_increment']);
		$this->assertEquals('one text NULL DEFAULT NULL auto_increment', $attribute->toSQL());

		$attribute = new PersistentAttribute('one', [T::Enumeration, false, 'MyEnum']);
		$this->assertEquals("one enum('option1','option2','option3') NOT NULL", $attribute->toSQL());
	}

	public function testToDefinition() {
		$definition = [T::Integer, false];
		$attribute = new PersistentAttribute('one', $definition);
		$this->assertEquals($definition, $attribute->toDefinition());

		$definition = [T::Text, true];
		$attribute = new PersistentAttribute('one', $definition);
		$this->assertEquals($definition, $attribute->toDefinition());

		$definition = [T::Text, true, 'auto_increment'];
		$attribute = new PersistentAttribute('one', $definition);
		$this->assertEquals($definition, $attribute->toDefinition());
	}

	public function testToDefinitionError() {
		$this->expectException(Exception::class);

		define('DB_CHECK', true);

		$definition = ['MadeUp', true];
		$attribute = new PersistentAttribute('one', $definition);
		$attribute->toDefinition();
	}
}

final class MyEnum implements PersistentEnum {

	const OPT_ONE = 'option1';
	const OPT_TWO = 'option2';
	const OPT_THREE = 'option3';

	public static function getTypeOptions() {
		return [static::OPT_ONE, static::OPT_TWO, static::OPT_THREE];
	}

	public static function getDescription($option) {
	}

	public static function getChar($option) {
	}
}
