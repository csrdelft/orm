<?php
namespace CsrDelft\Orm\Entity;

use CsrDelft\Orm\Common\OrmException;

/**
 * PersistentAttribute.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Translation of persistent attribute definitions to and from MySQL table structure.
 */
class PersistentAttribute {

	/**
	 * Name
	 * @var string
	 */
	public $field;

	/**
	 * Type definition
	 * @var int
	 */
	public $type;

	/**
	 * Allowed to be NULL: 'YES' or 'NO'
	 * @var string
	 */
	public $null;

	/**
	 * Key type: 'PRI' or 'UNI' or 'MUL' or empty
	 * @var string
	 */
	public $key;

	/**
	 * Default value
	 * @var string
	 */
	public $default;

	/**
	 * Additional properties like 'auto_increment'
	 * @var string
	 */
	public $extra;

	/**
	 * To compare table description of MySQL.
	 *
	 * @unsupported keys
	 *
	 * @param string $name
	 * @param array $definition
	 */
	public function __construct($name = null, array $definition = null) {
		if ($name != null) {
			$this->field = $name;
		}

		if ($definition != null) {
			$this->type = $definition[0];
			$this->default = null;
			if (isset($definition[1]) AND $definition[1]) {
				$this->null = 'YES';
			} else {
				$this->null = 'NO';
			}
			$this->extra = (isset($definition[2]) ? $definition[2] : '');
			if ($this->type === T::Enumeration) {
				$class = $this->extra;
				$this->type = "enum('" . implode("','", $class::getTypeOptions()) . "')";
				$this->extra = '';
			}
		}
	}

	/**
	 * Get sql for this PersistentAttribute.
	 *
	 * @return string
	 */
	public function toSQL() {
		$sql = sprintf('%s %s', $this->field, $this->type);

		if ($this->null === 'YES') {
			$sql .= ' NULL';
			if ($this->default === null) {
				$sql .= ' DEFAULT NULL';
			}
		} else {
			$sql .= ' NOT NULL';
			if ($this->default !== null) {
				$sql .= ' DEFAULT "' . $this->default . '"';
			}
		}
		if (!empty($this->extra)) {
			$sql .= ' ' . $this->extra;
		}
		return $sql;
	}

	/**
	 * To compare table description of MySQL.
	 *
	 * @unsupported keys, enum
	 *
	 * @return array $definition
	 * @throws OrmException
	 */
	public function toDefinition() {
		$definition = [];
		if (strpos($this->type, 'enum') === 0) {
			$start = strpos($this->type, '(') + 1;
			$length = strpos($this->type, ')') - $start;
			$values = explode(',', substr($this->type, $start, $length));
			foreach ($values as $i => $value) {
				$values[$i] = str_replace("'", "", $value);
			}
			return [T::Enumeration, false, $values];
		} else {
			if (
				defined('DB_CHECK')
				AND DB_CHECK
				AND !in_array($this->type, T::getTypeOptions())
			) {
				throw new OrmException('Unknown persistent attribute type: ' . $this->type);
			}
			$definition[] = $this->type;
		}
		if ($this->null === 'YES') {
			$definition[] = true;
		} else {
			$definition[] = false;
		}
		if (!empty($this->extra)) {
			$definition[] = $this->extra;
		}
		return $definition;
	}
}
