<?php
use CsrDelft\Orm\Persistence\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers CsrDelft\Orm\Persistence\QueryBuilder
 */
final class QueryBuilderTest extends TestCase {
	public function testCreateSelectQuery() {
		$querybuilder = new QueryBuilder();
		$this->assertEquals(
			"SELECT one FROM two",
			$querybuilder->buildSelect(array('one'), 'two')
		);
	}
	public function testCreateSelectWhereQuery() {
		$querybuilder = new QueryBuilder();
		$this->assertEquals(
			"SELECT one, two FROM three WHERE one = ?",
			$querybuilder->buildSelect(array('one', 'two'), 'three', 'one = ?')
		);
	}
	public function testSelectGroupQuery() {
		$querybuilder = new QueryBuilder();
		$this->assertEquals(
			"SELECT one FROM two GROUP BY three",
			$querybuilder->buildSelect(array('one'), 'two', null, 'three')
		);
	}

}