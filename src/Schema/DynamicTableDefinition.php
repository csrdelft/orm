<?php

namespace CsrDelft\Orm\Schema;

/**
 * Class DynamicTableDefinition.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DynamicTableDefinition implements TableDefinition
{
	/**
	 * @var array
	 */
	private $columnDefinitions;

	/**
	 * @var string[]
	 */
	private $primaryKey;

	/**
	 * @var string
	 */
	private $tableName;

	public function __construct($tableName, $primaryKey, $columnDefinitions)
	{
		$this->tableName = $tableName;
		$this->primaryKey = $primaryKey;
		$this->columnDefinitions = $columnDefinitions;
	}

	/**
	 * Name of the table.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * Primary key of the table.
	 *
	 * @return string[]
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	/**
	 * @return array
	 */
	public function getColumnDefinitions()
	{
		return $this->columnDefinitions;
	}

	/**
	 * Columns of the table.
	 *
	 * @return string[]
	 */
	public function getColumnNames()
	{
		return array_keys($this->columnDefinitions);
	}
}
