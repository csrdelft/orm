<?php
/**
 * The SchemaDefinition file.
 */

namespace CsrDelft\Orm\Schema;


/**
 * Interface TableDefinition.
 *
 * Defines a table.
 *
 * @package CsrDelft\Orm\Schema
 */
interface TableDefinition
{
	/**
	 * Name of the table.
	 *
	 * @return string
	 */
	public function getTableName();

	/**
	 * Primary key of the table.
	 *
	 * @return string[]
	 */
	public function getPrimaryKey();

	/**
	 * @return array
	 */
	public function getColumnDefinitions();

	/**
	 * Columns of the table.
	 *
	 * @return string[]
	 */
	public function getColumnNames();
}
