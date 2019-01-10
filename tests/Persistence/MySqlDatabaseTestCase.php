<?php
use CsrDelft\Orm\Configuration;
use CsrDelft\Orm\Persistence\Database;
use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\Framework\TestCase;

abstract class MySqlDatabaseTestCase extends TestCase {
    use TestCaseTrait {
        setUp as setUpDb;
        tearDown as tearDownDb;
    }

	// only instantiate pdo once for test clean-up/fixture load
	static private $pdo = null;

	// only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
	private $conn = null;

	/**
	 * @return \PHPUnit\DbUnit\Database\DefaultConnection|null
	 * @throws \CsrDelft\Orm\Exception\CsrOrmException
	 */
	final public function getConnection() {
		if ($this->conn === null) {
			if (self::$pdo == null) {
				Configuration::load([
					'cache_path' => '.',
					'db' => [
						'host' => '127.0.0.1',
						'user' => 'root',
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

	/**
	 * @throws \CsrDelft\Orm\Exception\CsrOrmException
	 */
	protected function setUp() {
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
				} else if ($col === 'json'){
					$cols[] = "`$col` TEXT";
				} else if (strpos($col, 'num_') === 0) {
					$cols[] = "`$col` INT";
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

		$this->setUpDb(); // Trait calls parent
	}

	/**
	 * @throws \CsrDelft\Orm\Exception\CsrOrmException
	 */
	protected function tearDown() {
		$allTables =
			$this->getDataSet()->getTableNames();
		foreach ($allTables as $table) {
			// drop table
			$conn = $this->getConnection();
			$pdo = $conn->getConnection();
			$pdo->exec("DROP TABLE IF EXISTS `$table`;");
		}

		$this->tearDownDb(); // Trait calls parent
	}
}
