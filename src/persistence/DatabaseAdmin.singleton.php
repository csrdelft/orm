<?php
namespace CsrDelft\Orm\Persistence;

use CsrDelft\Orm\Entity\PersistentAttribute;
use PDO;
use PDOStatement;

/**
 * DatabaseAdmin.singleton.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 */
class DatabaseAdmin extends Database {

	/**
	 * Singleton instance
	 * @var DatabaseAdmin
	 */
	private static $instance;

	public static function init($host, $db, $user, $pass) {
		assert('!isset(self::$instance)');
		$dsn = 'mysql:host=' . $host . ';dbname=' . $db;
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
		self::$instance = new DatabaseAdmin($dsn, $user, $pass, $options);
	}

	/**
	 * Get singleton DatabaseAdmin instance.
	 *
	 * @return DatabaseAdmin
	 */
	public static function instance() {
		assert('isset(self::$instance)');
		return self::$instance;
	}

	/**
	 * Array of SQL statements for file.sql
	 * @var array
	 */
	private static $queries = array();

	/**
	 * Get array of SQL statements for file.sql
	 * @return array
	 */
	public function getQueries() {
		return self::$queries;
	}

	/**
	 * Backup table structure and data.
	 *
	 * @param string $name
	 */
	public function sqlBackupTable($name) {
		$filename = 'backup-' . $name . '_' . date('d-m-Y_H-i-s') . '.sql.gz';
		header('Content-Type: application/x-gzip');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		$cred = parse_ini_file(ETC_PATH . 'mysql.ini');
		$cmd = 'mysqldump --user=' . $cred['user'] . ' --password=' . $cred['pass'] . ' --host=' . $cred['host'] . ' ' . $cred['db'] . ' ' . $name . ' | gzip --best';
		passthru($cmd);
	}

	/**
	 * Get all tables.
	 *
	 * @return PDOStatement
	 */
	public function sqlShowTables() {
		$sql = 'SHOW TABLES';
		$query = $this->prepare($sql);
		$query->execute();
		return $query;
	}

	/**
	 * Get table attributes.
	 *
	 * @param string $name
	 * @return PDOStatement
	 */
	public function sqlDescribeTable($name) {
		$sql = 'DESCRIBE ' . $name;
		$query = $this->prepare($sql);
		$this->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); // Force column names to lower case.
		$query->execute();
		$this->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); // Leave column names as returned by the database driver.
		$query->setFetchMode(PDO::FETCH_CLASS, 'CsrDelft\Orm\Entity\PersistentAttribute');
		return $query;
	}

	/**
	 * Get query to (re-)create existing table.
	 *
	 * @param string $name
	 * @return string SQL query
	 */
	public function sqlShowCreateTable($name) {
		$sql = 'SHOW CREATE TABLE ' . $name;
		$query = $this->prepare($sql);
		$query->execute();
		return $query->fetchColumn(1);
	}

	public function sqlCreateTable($name, array $attributes, array $primary_key) {
		$sql = 'CREATE TABLE ' . $name . ' (';
		foreach ($attributes as $name => $attribute) {
			$sql .= $attribute->toSQL() . ', ';
		}
		if (empty($primary_key)) {
			$sql = substr($sql, 0, -2); // remove last ,
		} else {
			$sql .= 'PRIMARY KEY (' . implode(', ', $primary_key) . ')';
		}
		$sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1';
		$query = $this->prepare($sql);
		if (DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlDropTable($name) {
		$sql = 'DROP TABLE ' . $name;
		$query = $this->prepare($sql);
		$esc = '-- ';
		if (DB_MODIFY AND DB_DROP === true) {
			$query->execute();
			$esc = '';
		}
		self::$queries[] = $esc . $query->queryString;
	}

	public function sqlAddAttribute($table, PersistentAttribute $attribute, $after_attribute = null) {
		$sql = 'ALTER TABLE ' . $table . ' ADD ' . $attribute->toSQL();
		$sql .= ($after_attribute === null ? ' FIRST' : ' AFTER ' . $after_attribute);
		$query = $this->prepare($sql);
		if (DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlChangeAttribute($table, PersistentAttribute $attribute, $old_name = null) {
		$sql = 'ALTER TABLE ' . $table . ' CHANGE ' . ($old_name === null ? $attribute->field : $old_name) . ' ' . $attribute->toSQL();
		$query = $this->prepare($sql);
		if (DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlDeleteAttribute($table, PersistentAttribute $attribute) {
		$sql = 'ALTER TABLE ' . $table . ' DROP ' . $attribute->field;
		$query = $this->prepare($sql);
		$esc = '-- ';
		if (DB_MODIFY AND DB_DROP === true) {
			$query->execute();
			$esc = '';
		}
		self::$queries[] = $esc . $query->queryString;
	}

}
