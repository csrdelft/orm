<?php
/**
 * The SchemaDefinition file.
 */

namespace CsrDelft\Orm\Schema;

use CsrDelft\Orm\Entity\PersistentAttribute;


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
	 * @return PersistentAttribute[]
	 */
	public function getColumnDefinitions();

	/**
	 * Columns of the table.
	 *
	 * @return string[]
	 */
	public function getColumnNames();
}
