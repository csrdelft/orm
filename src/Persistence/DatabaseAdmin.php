<?php
namespace CsrDelft\Orm\Persistence;

use CsrDelft\Orm\Entity\PersistentAttribute;
use PDO;
use PDOStatement;

/**
 * DatabaseAdmin.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 */
class DatabaseAdmin extends Database {
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
		$cmd = 'mysqldump --user=' . $this->cred['user']
			. ' --password=' . $this->cred['pass']
			. ' --host=' . $this->cred['host']
			. ' ' . $this->cred['db'] . ' ' . $name . ' | gzip --best';
		passthru($cmd);
	}

	/**
	 * Get all tables.
	 *
	 * @return PDOStatement
	 */
	public function sqlShowTables() {
		$sql = $this->queryBuilder->buildShowTable();
		$query = $this->prepare($sql);
		$query->execute();
		return $query;
	}

	/**
	 * Get table attributes.
	 *
	 * @param string $name
	 * @return PDOStatement|PersistentAttribute[]
	 */
	public function sqlDescribeTable($name) {
		$sql = $this->queryBuilder->buildDescribeTable($name);
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
		$sql = $this->queryBuilder->buildShowCreateTable($name);
		$query = $this->prepare($sql);
		$query->execute();
		return $query->fetchColumn(1);
	}

	public function sqlCreateTable($name, array $attributes, array $primary_key) {
		$sql = $this->queryBuilder->buildCreateTable($name, $attributes, $primary_key);
		$query = $this->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlDropTable($name) {
		$sql = $this->queryBuilder->buildDropTable($name);
		$query = $this->prepare($sql);
		$esc = '-- ';
		if (defined('DB_MODIFY') AND defined('DB_DROP') AND DB_MODIFY AND DB_DROP === true) {
			$query->execute();
			$esc = '';
		}
		self::$queries[] = $esc . $query->queryString;
	}

	public function sqlAddAttribute($table, PersistentAttribute $attribute, $after_attribute = null) {
		$sql = $this->queryBuilder->buildAddAttribute($table, $attribute, $after_attribute);
		$query = $this->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlChangeAttribute($table, PersistentAttribute $attribute, $old_name = null) {
		$sql = $this->queryBuilder->buildChangeAttribute($table, $attribute, $old_name);
		$query = $this->prepare($sql);
		if (defined('DB_MODIFY') AND DB_MODIFY) {
			$query->execute();
		}
		self::$queries[] = $query->queryString;
	}

	public function sqlDeleteAttribute($table, PersistentAttribute $attribute) {
		$sql = $this->queryBuilder->buildDeleteAttribute($table, $attribute);
		$query = $this->prepare($sql);
		$esc = '-- ';
		if (defined('DB_MODIFY') AND defined('DB_DROP') AND DB_MODIFY AND DB_DROP === true) {
			$query->execute();
			$esc = '';
		}
		self::$queries[] = $esc . $query->queryString;
	}

}
