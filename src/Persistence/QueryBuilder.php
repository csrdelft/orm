<?php

namespace CsrDelft\Orm\Persistence;

use CsrDelft\Orm\Entity\PersistentAttribute;

/**
 * Class QueryBuilder
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class QueryBuilder {

	/**
	 * @param string[] $attributes
	 * @param string $from
	 * @param string|null $where
	 * @param string|null $group_by
	 * @param string|null $order_by
	 * @param int|string $limit
	 * @param int $start
	 * @return string
	 */
	public function buildSelect(
		array $attributes,
		$from,
		$where = null,
		$group_by = null,
		$order_by = null,
		$limit = null,
		$start = 0
	) {
		$whereSql = '';
		if ($where !== null) {
			$whereSql = ' WHERE ' . $where;
		}
		$groupBySql = '';
		if ($group_by !== null) {
			$groupBySql = ' GROUP BY ' . $group_by;
		}
		$orderBySql = '';
		if ($order_by !== null) {
			$orderBySql = ' ORDER BY ' . $order_by;
		}
		$limitSql = '';
		if ((int)$limit > 0) {
			$limitSql = ' LIMIT ' . (int)$start . ', ' . (int)$limit;
		}
		return sprintf(
			'SELECT %s FROM %s%s%s%s%s',
			implode(', ', $attributes),
			$from,
			$whereSql,
			$groupBySql,
			$orderBySql,
			$limitSql
		);
	}

	/**
	 * @param string $table
	 * @param string|null $where
	 * @return string
	 */
	public function buildExists($table, $where = null) {
		$whereSql = '';
		if ($where !== null) {
			$whereSql = ' WHERE ' . $where;
		}
		return sprintf(
			'SELECT EXISTS (SELECT 1 FROM %s%s)',
			$table,
			$whereSql
		);
	}

	/**
	 * @param string $table
	 * @param string[] $properties
	 * @param string[] $insert_params
	 * @return string
	 */
	public function buildInsert($table, $properties, $insert_params) {
		return sprintf(
			'INSERT INTO %s (%s) VALUES (%s)',
			$table,
			implode(', ', array_keys($properties)),
			implode(', ', array_keys($insert_params))
		);
	}

	/**
	 * @param string $table
	 * @param string[] $attributes
	 * @param string $where
	 * @param int $limit
	 * @return string
	 */
	public function buildUpdate($table, $attributes, $where, $limit = 0) {
		$sql = 'UPDATE ' . $table . ' SET ';
		$sql .= implode(', ', $attributes);
		$sql .= ' WHERE ' . $where;
		if ((int)$limit > 0) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $sql;
	}

	/**
	 * @param string $table
	 * @param string $where
	 * @param int $limit
	 * @return string
	 */
	public function buildDelete($table, $where, $limit = 0) {
		$limitSql = '';
		if ((int)$limit > 0) {
			$limitSql = ' LIMIT ' . (int)$limit;
		}

		return sprintf(
			'DELETE FROM %s WHERE %s%s',
			$table,
			$where,
			$limitSql
		);
	}

	/**
	 * @return string
	 */
	public function buildShowTable() {
		return 'SHOW TABLES';
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function buildDescribeTable($name) {
		return sprintf('DESCRIBE %s', $name);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function buildExistsTable($name) {
		return sprintf('SHOW TABLES LIKE \'%s\'', $name);
	}

	/**
	 * @param string $name
	 * @param PersistentAttribute[] $attributes
	 * @param string[] $primary_key
	 * @return string
	 */
	public function buildCreateTable($name, array $attributes, array $primary_key) {
		$attributeSql = '';
		foreach ($attributes as $attribute) {
			$attributeSql .= $attribute->toSQL() . ', ';
		}
		if (empty($primary_key)) {
			$attributeSql = substr($attributeSql, 0, -2); // remove last ,
		} else {
			$attributeSql .= 'PRIMARY KEY (' . implode(', ', $primary_key) . ')';
		}
		return sprintf(
			'CREATE TABLE %s (%s) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1',
			$name,
			$attributeSql
		);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function buildDropTable($name) {
		return sprintf(
			'DROP TABLE %s',
			$name
		);
	}

	/**
	 * @param string $table
	 * @param PersistentAttribute $attribute
	 * @param string|null $after_attribute
	 * @return string
	 */
	public function buildAddAttribute($table, PersistentAttribute $attribute, $after_attribute = null) {
		if (is_null($after_attribute)) {
			$location = 'FIRST';
		} else {
			$location = 'AFTER ' . $after_attribute;
		}
		return sprintf(
			'ALTER TABLE %s ADD %s %s',
			$table,
			$attribute->toSQL(),
			$location
		);
	}

	/**
	 * @param string $table
	 * @param PersistentAttribute $attribute
	 * @return string
	 */
	public function buildDeleteAttribute($table, PersistentAttribute $attribute) {
		return sprintf(
			'ALTER TABLE %s DROP %s',
			$table,
			$attribute->field
		);
	}

	/**
	 * @param string $table
	 * @param PersistentAttribute $attribute
	 * @param string|null $old_name
	 * @return string
	 */
	public function buildChangeAttribute($table, PersistentAttribute $attribute, $old_name = null) {
		if ($old_name === null) {
			$old_name = $attribute->field;
		}

		return sprintf(
			'ALTER TABLE %s CHANGE %s %s',
			$table,
			$old_name,
			$attribute->toSQL()
		);
	}

	/**
	 * @param string[] $options
	 * @return string
	 */
	public function buildEnum($options) {
		return sprintf(
			'enum(\'%s\')',
			implode('\',\'', $options)
		);
	}

	/**
	 * @source http://stackoverflow.com/a/1376838
	 *
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from
	 * $params are are in the same order as specified in $query
	 *
	 * @param string $query The sql query with parameter placeholders
	 * @param array $params The array of substitution parameters
	 * @return string The interpolated query
	 */
	public function interpolateQuery($query, $params) {
		$attributes = [];
		// build a regular expression for each parameter
		foreach ($params as $attribute => $value) {
			if (is_string($attribute)) {
				$attributes[] = '/:' . $attribute . '/';
			} else {
				$attributes[] = '/[?]/';
			}
			if (is_string($value)) {
				$params[$attribute] = '"' . $value . '"'; // quotes
			} elseif (is_bool($value) AND ($value === true OR $value === false)) {
				$params[$attribute] = $value ? 'TRUE' : 'FALSE';
			} else {
				$params[$attribute] = $value;
			}
		}
		return preg_replace($attributes, $params, $query, 1);
	}
}
