<?php
namespace CsrDelft\Orm\Persistence;

use CsrDelft\Orm\Entity\PersistentAttribute;

/**
 * Class QueryBuilder
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class QueryBuilder {

	public function buildSelect(array $attributes, $from, $where = null, $group_by = null, $order_by = null, $limit = null, $start = 0) {
		$sql = 'SELECT ' . implode(', ', $attributes) . ' FROM ' . $from;
		if ($where !== null) {
			$sql .= ' WHERE ' . $where;
		}
		if ($group_by !== null) {
			$sql .= ' GROUP BY ' . $group_by;
		}
		if ($order_by !== null) {
			$sql .= ' ORDER BY ' . $order_by;
		}
		if ((int)$limit > 0) {
			$sql .= ' LIMIT ' . (int)$start . ', ' . (int)$limit;
		}
		return $sql;
	}

	public function buildExists($table, $where = null) {
		$sql = 'SELECT EXISTS (SELECT 1 FROM ' . $table;
		if ($where !== null) {
			$sql .= ' WHERE ' . $where;
		}
		$sql .= ')';
		return $sql;
	}

	public function buildInsert($table, $properties, $insert_params) {
		$sql = 'INSERT';
		$sql .= ' INTO ' . $table;
		$sql .= ' (' . implode(', ', array_keys($properties)) . ')';
		$sql .= ' VALUES (' . implode(', ', array_keys($insert_params)) . ')'; // named params

		return $sql;
	}

	public function buildUpdate($table, $attributes, $where, $limit = 0) {
		$sql = 'UPDATE ' . $table . ' SET ';
		$sql .= implode(', ', $attributes);
		$sql .= ' WHERE ' . $where;
		if ((int)$limit > 0) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $sql;
	}

	public function buildDelete($table, $where, $limit = 0) {
		$sql = 'DELETE FROM ' . $table;
		$sql .= ' WHERE ' . $where;
		if ((int)$limit > 0) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $sql;
	}

	public function buildShowTable() {
		return 'SHOW TABLES';
	}

	public function buildDescribeTable($name) {
		return 'DESCRIBE ' . $name;
	}

	public function buildShowCreateTable($name) {
		return 'SHOW CREATE TABLE ' . $name;
	}

	/**
	 * @param $name
	 * @param PersistentAttribute[] $attributes
	 * @param array $primary_key
	 * @return string
	 */
	public function buildCreateTable($name, array $attributes, array $primary_key) {
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
		return $sql;
	}

	public function buildDropTable($name) {
		return 'DROP TABLE ' . $name;
	}

	public function buildAddAttribute($table, PersistentAttribute $attribute, $after_attribute = null) {
		$sql = 'ALTER TABLE ' . $table . ' ADD ' . $attribute->toSQL();
		$sql .= ($after_attribute === null ? ' FIRST' : ' AFTER ' . $after_attribute);
		return $sql;
	}

	public function buildDeleteAttribute($table, PersistentAttribute $attribute) {
		$sql = 'ALTER TABLE ' . $table . ' DROP ' . $attribute->field;
		return $sql;
	}

	public function buildChangeAttribute($table, PersistentAttribute $attribute, $old_name = null) {
		$sql = 'ALTER TABLE ' . $table . ' CHANGE ' . ($old_name === null ? $attribute->field : $old_name) . ' ' . $attribute->toSQL();
		return $sql;
	}

}