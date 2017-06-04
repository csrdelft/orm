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
				if (getenv('TRAVIS')) {
					$environment = 'travis';
				} elseif (getenv('DOCKER')) {
					$environment = 'docker';
				} else {
					$environment = 'local';
				}
				$config = parse_ini_file(__DIR__ . '/../database.ini', true)[$environment];

				Configuration::load([
					'cache_path' => '.',
					'db' => $config,
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
