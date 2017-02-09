<?php
use CsrDelft\Orm\Persistence\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers CsrDelft\Orm\Persistence\QueryBuilder
 */
final class QueryBuilderTest extends TestCase {
	public function testBuildSelect() {
		$querybuilder = new QueryBuilder();
		$this->assertEquals(
			"SELECT one FROM two",
			$querybuilder->buildSelect(array('one'), 'two')
		);

		$this->assertEquals(
			"SELECT one, two FROM three WHERE one = ?",
			$querybuilder->buildSelect(array('one', 'two'), 'three', 'one = ?')
		);

		$this->assertEquals(
			"SELECT one FROM two GROUP BY three",
			$querybuilder->buildSelect(array('one'), 'two', null, 'three')
		);

		$this->assertEquals(
			"SELECT one FROM two ORDER BY three",
			$querybuilder->buildSelect(array('one'), 'two', null, null, 'three')
		);

		$this->assertEquals(
			"SELECT one FROM two LIMIT 0, 1",
			$querybuilder->buildSelect(array('one'), 'two', null, null, null, 1)
		);

		$this->assertEquals(
			"SELECT one FROM two LIMIT 5, 1",
			$querybuilder->buildSelect(array('one'), 'two', null, null, null, 1, 5)
		);
	}

	public function testBuildExists() {
		$querybuilder = new QueryBuilder();
		$this->assertEquals(
			"SELECT EXISTS (SELECT 1 FROM one)",
			$querybuilder->buildExists('one')
		);

		$this->assertEquals(
			"SELECT EXISTS (SELECT 1 FROM one WHERE two = ?)",
			$querybuilder->buildExists('one', 'two = ?')
		);
	}

	public function testBuildUpdate() {
		$querybuilder = new QueryBuilder();
		$this->assertEquals(
			"UPDATE one SET two, three WHERE four = ?",
			$querybuilder->buildUpdate('one', array('two', 'three'), 'four = ?')
		);

		$this->assertEquals(
			"UPDATE one SET two, three WHERE four = ? LIMIT 10",
			$querybuilder->buildUpdate('one', array('two', 'three'), 'four = ?', 10)
		);
	}

	public function testBuildInsert() {
		$querybuilder = new QueryBuilder();
		$this->assertEquals(
			"INSERT INTO one (two, three) VALUES (four, five)",
			$querybuilder->buildInsert('one', array('two' => '', 'three' => ''), array('four' => '', 'five' => ''))
		);
	}

	public function testBuildDelete() {
		$querybuilder = new QueryBuilder();
		$this->assertEquals(
			"DELETE FROM one WHERE two = ?",
			$querybuilder->buildDelete('one', 'two = ?')
		);

		$this->assertEquals(
			"DELETE FROM one WHERE two = ? LIMIT 10",
			$querybuilder->buildDelete('one', 'two = ?', 10)
		);
	}

}
