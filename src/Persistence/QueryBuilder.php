<?php
declare(strict_types=1);

namespace CsrDelft\Orm\Persistence;

use CsrDelft\Orm\Common\Object\SqlQuery;
use CsrDelft\Orm\Common\Object\TableName;
use CsrDelft\Orm\Entity\PersistentAttribute;

/**
 * Class QueryBuilder
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class QueryBuilder {

	/**
	 * @param string[] $attributes
	 * @param TableName $table
	 * @param string|null $where
	 * @param string|null $group_by
	 * @param string|null $order_by
	 * @param int $limit
	 * @param int $start
	 * @return string
	 */
	public function buildSelect(
		array $attributes,
		TableName $table,
		string $where = null,
		string $group_by = null,
		string $order_by = null,
		int $limit = -1,
		int $start = 0
	): string {
		$sql = sprintf('SELECT %s FROM %s', implode(', ', $attributes), $table->getTableName());

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

		return $sql . ';';
	}

	/**
	 * @param TableName $table
	 * @param string|null $where
	 * @return string
	 */
	public function buildExists(TableName $table, string $where = null): string {
		$subSql = vsprintf('SELECT 1 FROM %s', $table->getTableName());

		if ($where !== null) {
			$subSql .= ' WHERE ' . $where;
		}

		return sprintf(
			'SELECT EXISTS (%s);',
			$subSql
		);
	}

	/**
	 * @param string $table
	 * @param string[] $properties
	 * @param string[] $insert_params
	 * @return string
	 */
	public function buildInsert(string $table, array $properties, array $insert_params): string {
		return sprintf(
			'INSERT INTO %s (%s) VALUES (%s);',
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
	public function buildUpdate(string $table, array $attributes, string $where, int $limit = 0): string {
		$sql = sprintf('UPDATE %s SET %s WHERE %s', $table, implode(', ', $attributes), $where);

		if ((int)$limit > 0) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $sql . ';';
	}

	/**
	 * @param TableName $table
	 * @param string $where
	 * @param int $limit
	 * @return string
	 */
	public function buildDelete(TableName $table, string $where, int $limit = 0): string {
		$sql = sprintf('DELETE FROM %s WHERE %s', $table->getTableName(), $where);

		if ((int)$limit > 0) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $sql . ';';
	}

	/**
	 * @return string
	 */
	public function buildShowTable(): SqlQuery {
		return new SqlQuery('SHOW TABLES;');
	}

	/**
	 * @param TableName $name
	 * @return SqlQuery
	 */
	public function buildDescribeTable(TableName $name): SqlQuery {
		return new SqlQuery(sprintf('DESCRIBE %s;', $name));
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function buildExistsTable(string $name): string {
		return sprintf('SHOW TABLES LIKE \'%s\';', $name);
	}

	/**
	 * @param TableName $name
	 * @param PersistentAttribute[] $attributes
	 * @param string[] $primary_key
	 * @return string
	 */
	public function buildCreateTable(TableName $name, array $attributes, array $primary_key): string {
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
			'CREATE TABLE %s (%s) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1;',
			$name->getTableName(),
			$attributeSql
		);
	}

	/**
	 * @param TableName $name
	 * @return SqlQuery
	 */
	public function buildDropTable(TableName $name): SqlQuery {
		return new SqlQuery(sprintf(
			'DROP TABLE %s;',
			$name
		));
	}

	/**
	 * @param TableName $table
	 * @param PersistentAttribute $attribute
	 * @param string|null $after_attribute
	 * @return SqlQuery
	 */
	public function buildAddAttribute(TableName $table, PersistentAttribute $attribute, $after_attribute = null): SqlQuery {
		$sql = sprintf('ALTER TABLE %s ADD %s', $table->getTableName(), $attribute->toSQL());

		if (is_null($after_attribute)) {
			$sql .= ' FIRST';
		} else {
			$sql .= ' AFTER ' . $after_attribute;
		}
		return new SqlQuery($sql . ';');
	}

	/**
	 * @param TableName $table
	 * @param PersistentAttribute $attribute
	 * @return SqlQuery
	 */
	public function buildDeleteAttribute(TableName $table, PersistentAttribute $attribute): SqlQuery {
		return new SqlQuery(sprintf(
			'ALTER TABLE %s DROP %s;',
			$table,
			$attribute->field
		));
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
			'ALTER TABLE %s CHANGE %s %s;',
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
	 * @param string[] $params The array of substitution parameters
	 * @return string The interpolated query
	 */
	public function interpolateQuery(string $query, array $params): string {
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
