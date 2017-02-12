<?php
use CsrDelft\Orm\Persistence\Database;

require_once 'SqliteDatabaseTestCase.php';

/**
 * @covers CsrDelft\Orm\Persistence\Database
 */
final class DatabaseTest extends SqliteDatabaseTestCase {

	/**
	 * Returns the test dataset.
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet() {
		return $this->createFlatXMLDataSet('tests/testDataset.xml');
	}

	public function testSqlSelect() {
		$database = new Database($this->getConnection()->getConnection());

		$this->assertEquals(2, count($database->sqlSelect(['*'], 'guestbook')->fetchAll()));
	}

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

	public function testSqlUpdate() {
		$database = new Database($this->getConnection()->getConnection());
		$dataset = $this->getConnection()->createQueryTable('guestbook', 'SELECT user FROM guestbook WHERE id = 1');

		$database->sqlUpdate('guestbook', ['user' => 'pete'], 'id = :id', [':id' => 1]);

		$this->assertEquals(['user' => 'pete'], $dataset->getRow(0));
	}

	public function testSqlDelete() {
		$database = new Database($this->getConnection()->getConnection());
		$dataset = $this->getConnection()->createQueryTable('guestbook', 'SELECT * FROM guestbook');

		$database->sqlDelete('guestbook', 'id = ?', [1]);

		$this->assertEquals(1, $dataset->getRowCount());
	}
}