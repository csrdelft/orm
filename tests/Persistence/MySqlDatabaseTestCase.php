<?php
use CsrDelft\Orm\Configuration;
use CsrDelft\Orm\Persistence\Database;

abstract class MySqlDatabaseTestCase extends PHPUnit_Extensions_Database_TestCase {
	// only instantiate pdo once for test clean-up/fixture load
	static private $pdo = null;

	// only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
	private $conn = null;

	final public function getConnection() {
		if ($this->conn === null) {
			if (self::$pdo == null) {
				Configuration::load([
					'cache_path' => '.',
					'db' => [
						'host' => '127.0.0.1',
						'user' => 'travis',
						'db' => 'orm_test',
						'pass' => ''
					]
				]);

				self::$pdo = Database::instance()->getDatabase();
			}
			$this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
		}

		return $this->conn;
	}

	public function setUp() {


		$conn = $this->getConnection();
		$pdo = $conn->getConnection();

		// set up tables
		$fixtureDataSet = $this->getDataSet();
		foreach ($fixtureDataSet->getTableNames() as $table) {
			// drop table
			$pdo->exec("DROP TABLE IF EXISTS `$table`;");
			// recreate table
			$meta = $fixtureDataSet->getTableMetaData($table);
			$create = "CREATE TABLE IF NOT EXISTS `$table` ";
			$cols = [];
			foreach ($meta->getColumns() as $col) {
				if ($col == 'id') {
					$cols[] = "`$col` INT NOT NULL auto_increment";
				} else {
					$cols[] = "`$col` VARCHAR(200)";
				}
			}
			if (in_array("`id` INT NOT NULL auto_increment", $cols)) {
				$cols[] = "PRIMARY KEY (`id`)";
			}
			$create .= '(' . implode(',', $cols) . ');';
			$pdo->exec($create);
		}

		parent::setUp();
	}

	public function tearDown() {
		$allTables =
			$this->getDataSet()->getTableNames();
		foreach ($allTables as $table) {
			// drop table
			$conn = $this->getConnection();
			$pdo = $conn->getConnection();
			$pdo->exec("DROP TABLE IF EXISTS `$table`;");
		}

		parent::tearDown();
	}
}
