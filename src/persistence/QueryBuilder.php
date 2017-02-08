<?php
namespace CsrDelft\Orm\Persistence;

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

	public function buildInsert($table, $properties, $insert_params, $replace) {
		if ($replace) {
			$sql = 'REPLACE';
		} else {
			$sql = 'INSERT';
		}
		$sql .= ' INTO ' . $table;
		$sql .= ' (' . implode(', ', array_keys($properties)) . ')';
		$sql .= ' VALUES (' . implode(', ', array_keys($insert_params)) . ')'; // named params

		return $sql;
	}

	public function buildUpdate($table, $attributes, $where, $limit) {
		$sql = 'UPDATE ' . $table . ' SET ';
		$sql .= implode(', ', $attributes);
		$sql .= ' WHERE ' . $where;
		if ((int)$limit > 0) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $sql;
	}

	public function buildDelete($table, $where, $limit) {
		$sql = 'DELETE FROM ' . $table;
		$sql .= ' WHERE ' . $where;
		if ((int)$limit > 0) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $sql;
	}

}