<?php

namespace CsrDelft\Orm\Schema;

use CsrDelft\Orm\Configuration;

/**
 * Class EntityParserXml.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class XmlTableDefinition implements TableDefinition
{
	/**
	 * @var \DOMDocument
	 */
	private $doc;

	/**
	 * @var \DOMXPath
	 */
	private $xpath;

	/**
	 * XmlTableDefinition constructor.
	 *
	 * @param Configuration $configuration
	 * @param string $entity Class name for entity
	 */
	public function __construct(Configuration $configuration, $entity)
	{
		$configPath = $configuration->getConfigPath();
		$configPrefix = $configuration->getConfigPrefix();

		if ($configPrefix !== '') {
			$className = str_replace($configPrefix, '', $entity);
		} else {
			$className = $entity;
		}

		$entityFile = str_replace('\\', DIRECTORY_SEPARATOR, $className);

		$configFile = $configPath . DIRECTORY_SEPARATOR . $entityFile . '.xml';

		$this->doc = new \DOMDocument();
		$this->doc->load($configFile);
		$this->doc->schemaValidate(__DIR__ . '/../../orm.xsd');

		$this->xpath = new \DOMXPath($this->doc);
	}

	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->doc->documentElement->attributes->getNamedItem('tableName')->nodeValue;
	}

	/**
	 * @return string[]
	 */
	public function getPrimaryKey() {
		$primaryKey = [];
		$keys = $this->xpath->query('PrimaryKey/Key/@name');

		foreach ($keys as $key) {
			$primaryKey[] = $key->nodeValue;
		}

		return $primaryKey;
	}

	/**
	 * @return string[]
	 */
	public function getColumnNames() {
		return array_keys($this->getColumnDefinitions());
	}

	/**
	 * @param $attributeName
	 *
	 * @return array
	 */
	public function getAttributeDefinition($attributeName) {
		return $this->getColumnDefinitions()[$attributeName];
	}

	/**
	 * @return array
	 */
	public function getColumnDefinitions() {
		$columnDefinitions = [];

		$definitions = $this->xpath->query('ColumnDefinition/Definition');

		foreach ($definitions as $definition) {
			/** @var \DOMElement $definition */
			$attributes = $definition->attributes;
			$name = $attributes->getNamedItem('name')->nodeValue;
			$type = $attributes->getNamedItem('type')->nodeValue;

			if ($definition->hasAttribute('null')) {
				$null = (bool) $attributes->getNamedItem('null')->nodeValue;
			} else {
				$null = false;
			}

			if ($definition->hasAttribute('extra')) {
				$extra = $attributes->getNamedItem('extra')->nodeValue;
			} else {
				$extra = null;
			}

			$columnDefinitions[$name] = [$type, $null, $extra];
		}

		return $columnDefinitions;
	}
}
