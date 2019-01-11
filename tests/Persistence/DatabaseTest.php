<?php /** @noinspection SqlNoDataSourceInspection */

use CsrDelft\Orm\Exception\CsrOrmException;
use CsrDelft\Orm\Persistence\Database;

require_once 'MySqlDatabaseTestCase.php';

class MyException extends Exception {}

/**
 * @covers CsrDelft\Orm\Persistence\Database
 */
final class DatabaseTest extends MySqlDatabaseTestCase {
	/**
	 * Returns the test dataset.
	 *
	 * @return \PHPUnit\DbUnit\DataSet\IDataSet
	 */
	protected function getDataSet() {
		return $this->createFlatXMLDataSet(__DIR__ . '/../resources/DatabaseTest.xml');
	}

	/**
	 * @throws CsrOrmException
	 */
	public function testSqlSelect() {
		$database = new Database($this->getConnection()->getConnection());

		$this->assertEquals(2, count($database->sqlSelect(['*'], 'guestbook')->fetchAll()));
	}

	/**
	 * @throws Exception
	 */
	public function testSqlInsert() {
		$database = new Database($this->getConnection()->getConnection());
		$dataset = $this->getConnection()->createQueryTable('guestbook', 'SELECT user FROM guestbook');

		$database->sqlInsert('guestbook', [
			'id' => 3,
			'content' => 'Number three',
			'user' => 'John Doe'
		]);

		$this->assertEquals(3, $dataset->getRowCount());
		$this->assertEquals(['user' => 'John Doe'], $dataset->getRow($dataset->getRowCount() - 1));
	}

	/**
	 * @throws CsrOrmException
	 */
	public function testSqlExists() {
		$database = new Database($this->getConnection()->getConnection());

		$this->assertEquals(
			true,
			$database->sqlExists('guestbook', 'user = ?', ['joe'])
		);

		$this->assertEquals(
			false,
			$database->sqlExists('guestbook', 'user = ?', ['pete'])
		);
	}

	/**
	 * @throws Exception
	 */
	public function testSqlUpdate() {
		$database = new Database($this->getConnection()->getConnection());
		$dataset = $this->getConnection()->createQueryTable('guestbook', 'SELECT user FROM guestbook WHERE id = 1');

		$database->sqlUpdate('guestbook', ['user' => 'pete'], 'id = :id', [':id' => 1]);

		$this->assertEquals(['user' => 'pete'], $dataset->getRow(0));
	}

	/**
	 * @throws CsrOrmException
	 * @throws Exception
	 */
	public function testInsertMultiple() {
		$database = new Database($this->getConnection()->getConnection());
		$dataset = $this->getConnection()->createQueryTable('guestbook', 'SELECT user FROM guestbook');

		$database->sqlInsertMultiple('guestbook', [['user', 'id'], ['pete', 3], ['jan', 4]]);

		$this->assertEquals(4, $dataset->getRowCount());
	}

	/**
	 * @throws CsrOrmException
	 * @throws Exception
	 */
	public function testInsertMultipleReplace() {
		$database = new Database($this->getConnection()->getConnection());
		$dataset = $this->getConnection()->createQueryTable('guestbook', 'SELECT user FROM guestbook');

		$database->sqlInsertMultiple('guestbook', [['user', 'id'], ['pete', 1], ['jan', 4]], true);

		$this->assertEquals(3, $dataset->getRowCount());
	}

	/**
	 * @throws CsrOrmException
	 */
	public function testSqlDelete() {
		$database = new Database($this->getConnection()->getConnection());
		$dataset = $this->getConnection()->createQueryTable('guestbook', 'SELECT * FROM guestbook');

		$database->sqlDelete('guestbook', 'id = ?', [1]);

		$this->assertEquals(1, $dataset->getRowCount());
	}

	/**
	 * @throws Exception
	 */
	public function testTransaction() {
		$database = new Database($this->getConnection()->getConnection());

		$dataset = $this->getConnection()->createQueryTable('guestbook', 'SELECT * FROM guestbook');


		$return = $database->_transaction(function() use ($database) {
			$this->assertTrue($database->getDatabase()->inTransaction(), 'Database is in a transaction');

			$database->sqlDelete('guestbook', 'id = ?', [1]);

			return "testValue";
		});

		$this->assertEquals(1, $dataset->getRowCount());

		$this->assertEquals("testValue", $return, 'transaction returns value of function');

		$this->assertFalse($database->getDatabase()->inTransaction(), 'Database is not in a transaction');
	}

	/**
	 * @throws Exception
	 */
	public function testTransactionRollback() {
		$database = new Database($this->getConnection()->getConnection());

		$dataset = $this->getConnection()->createQueryTable('guestbook', 'SELECT * FROM guestbook');

		try {
			$database->_transaction(function() use ($database) {
				$database->sqlDelete('guestbook', 'id = ?', [1]);

				throw new MyException("testException");
			});
			$this->fail("Exception expected");
		} /** @noinspection PhpRedundantCatchClauseInspection */ catch (MyException $ex) {
			$this->assertEquals("testException", $ex->getMessage(), 'Exception is rethrown');
		}

		$this->assertEquals(2, $dataset->getRowCount(), 'No guestbook entry is deleted');

	}
}
