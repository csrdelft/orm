<?php
/**
 * The EntityParserXml file.
 */

namespace CsrDelft\Orm\Parser;

use CsrDelft\Orm\Configuration;

/**
 * Class EntityParserXml.
 *
 * @author Gerben Oolbekkink <gerben@bunq.com>
 * @since 20170604 Initial creation.
 */
class EntityParserXml
{
	public function __construct(string $entity)
	{
		$configPath = Configuration::instance()->getConfigPath();
		$configPrefix = Configuration::instance()->getConfigPrefix();

		if ($configPrefix !== '') {
			$className = str_replace($configPrefix, '', $entity);
		} else {
			$className = $entity;
		}

		$entityFile = str_replace('\\', DIRECTORY_SEPARATOR, $className);

		$configFile = $configPath . DIRECTORY_SEPARATOR . $entityFile . '.xml';

		$this->xml = simplexml_load_file($configFile);
	}

	public  function getXml() {
		return $this->xml;
	}

	public function getTableName() {
		return $this->xml->getName();
	}

	public function getPrimaryKey() {
		$primaryKey = [];

		$keys = $this->xml->{'PrimaryKey'}->children();

		foreach($keys as $key) {
			$primaryKey[] = (string) $key->attributes()->{'name'};
		}

		return $primaryKey;
	}

	public function getAttributes() {
		return array_keys($this->getPersistentAttributes());
	}

	public function getAttributeDefinition($attributeName) {
		return $this->getPersistentAttributes()[$attributeName];
	}

	public function getPersistentAttributes() {
		$persistentAttributes = [];

		$attributes = $this->xml->{'PersistentAttributes'}->children();

		foreach ($attributes as $attribute) {
			$attributeAttributes = $attribute->attributes();

			$name = (string) $attributeAttributes->{'name'};
			$type = (string) $attributeAttributes->{'type'};
			$extra = (string) $attributeAttributes->{'extra'};

			$persistentAttributes[$name] = [$type, false, $extra];
		}

		return $persistentAttributes;
	}
}
