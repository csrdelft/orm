<?php
use CsrDelft\Orm\Entity\PersistentAttribute;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Persistence\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers CsrDelft\Orm\Persistence\QueryBuilder
 */
final class QueryBuilderTest extends TestCase {
	public function testBuildSelect() {
		$query_builder = new QueryBuilder();
		$this->assertEquals(
			"SELECT one FROM two",
			$query_builder->buildSelect(array('one'), 'two')
		);

		$this->assertEquals(
			"SELECT one, two FROM three WHERE one = ?",
			$query_builder->buildSelect(array('one', 'two'), 'three', 'one = ?')
		);

		$this->assertEquals(
			"SELECT one FROM two GROUP BY three",
			$query_builder->buildSelect(array('one'), 'two', null, 'three')
		);

		$this->assertEquals(
			"SELECT one FROM two ORDER BY three",
			$query_builder->buildSelect(array('one'), 'two', null, null, 'three')
		);

		$this->assertEquals(
			"SELECT one FROM two LIMIT 0, 1",
			$query_builder->buildSelect(array('one'), 'two', null, null, null, 1)
		);

		$this->assertEquals(
			"SELECT one FROM two LIMIT 5, 1",
			$query_builder->buildSelect(array('one'), 'two', null, null, null, 1, 5)
		);
	}

	public function testBuildExists() {
		$query_builder = new QueryBuilder();
		$this->assertEquals(
			"SELECT EXISTS (SELECT 1 FROM one)",
			$query_builder->buildExists('one')
		);

		$this->assertEquals(
			"SELECT EXISTS (SELECT 1 FROM one WHERE two = ?)",
			$query_builder->buildExists('one', 'two = ?')
		);
	}

	public function testBuildUpdate() {
		$query_builder = new QueryBuilder();
		$this->assertEquals(
			"UPDATE one SET two, three WHERE four = ?",
			$query_builder->buildUpdate('one', array('two', 'three'), 'four = ?')
		);

		$this->assertEquals(
			"UPDATE one SET two, three WHERE four = ? LIMIT 10",
			$query_builder->buildUpdate('one', array('two', 'three'), 'four = ?', 10)
		);
	}

	public function testBuildInsert() {
		$query_builder = new QueryBuilder();
		$this->assertEquals(
			"INSERT INTO one (two, three) VALUES (four, five)",
			$query_builder->buildInsert('one', array('two' => '', 'three' => ''), array('four' => '', 'five' => ''))
		);
	}

	public function testBuildDelete() {
		$query_builder = new QueryBuilder();
		$this->assertEquals(
			"DELETE FROM one WHERE two = ?",
			$query_builder->buildDelete('one', 'two = ?')
		);

		$this->assertEquals(
			"DELETE FROM one WHERE two = ? LIMIT 10",
			$query_builder->buildDelete('one', 'two = ?', 10)
		);
	}

	public function testBuildShowTable() {
		$query_builder = new QueryBuilder();
		$this->assertEquals(
			"SHOW TABLES",
			$query_builder->buildShowTable()
		);
	}

	public function testBuildDescribeTable() {
		$query_builder = new QueryBuilder();
		$this->assertEquals(
			"DESCRIBE one",
			$query_builder->buildDescribeTable('one')
		);
	}

	public function testBuildShowCreateTable() {
		$query_builder = new QueryBuilder();
		$this->assertEquals(
			"SHOW CREATE TABLE one",
			$query_builder->buildShowCreateTable('one')
		);
	}

	public function testBuildCreateTable() {
		$query_builder = new QueryBuilder();
		$attribute_two = new PersistentAttribute('two', array(T::Integer));
		$attribute_three = new PersistentAttribute('three', array(T::Text, true));

		$this->assertEquals(
			"CREATE TABLE one (two int(11) NOT NULL, three text NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1",
			$query_builder->buildCreateTable('one', array($attribute_two, $attribute_three), array())
		);

		$this->assertEquals(
			"CREATE TABLE one (two int(11) NOT NULL, three text NULL DEFAULT NULL, PRIMARY KEY (two)) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1",
			$query_builder->buildCreateTable('one', array($attribute_two, $attribute_three), array('two'))
		);

	}

	public function testBuildDropTable() {
		$query_builder = new QueryBuilder();
		$this->assertEquals(
			"DROP TABLE one",
			$query_builder->buildDropTable('one')
		);
	}

	public function testBuildAddAttribute() {
		$query_builder = new QueryBuilder();
		$attribute = new PersistentAttribute('two', array(T::Integer));
		$this->assertEquals(
			"ALTER TABLE one ADD two int(11) NOT NULL FIRST",
			$query_builder->buildAddAttribute('one', $attribute)
		);

		$this->assertEquals(
			"ALTER TABLE one ADD two int(11) NOT NULL AFTER three",
			$query_builder->buildAddAttribute('one', $attribute, 'three')
		);
	}

	public function testBuildDeleteAttribute() {
		$query_builder = new QueryBuilder();
		$attribute = new PersistentAttribute('two', array(T::Integer));
		$this->assertEquals(
			"ALTER TABLE one DROP two",
			$query_builder->buildDeleteAttribute('one', $attribute)
		);
	}

	public function testBuildChangeAttribute() {
		$query_builder = new QueryBuilder();
		$attribute = new PersistentAttribute('two', array(T::Integer));
		$this->assertEquals(
			"ALTER TABLE one CHANGE two two int(11) NOT NULL",
			$query_builder->buildChangeAttribute('one', $attribute)
		);

		$this->assertEquals(
			"ALTER TABLE one CHANGE three two int(11) NOT NULL",
			$query_builder->buildChangeAttribute('one', $attribute, 'three')
		);
	}


}
