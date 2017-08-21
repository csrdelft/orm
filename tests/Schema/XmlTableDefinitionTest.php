<?php
/**
 * The XmlTableDefinitionTest file.
 */
use CsrDelft\Orm\Configuration;
use CsrDelft\Orm\Entity\PersistentAttribute;
use CsrDelft\Orm\Schema\XmlTableDefinition;
use PHPUnit\Framework\TestCase;

class MyConfiguration extends Configuration {
	public function __construct(array $config)
	{
		$this->configPrefix = $config['config']['prefix'];
		$this->configPath = $config['config']['path'];
	}
}

/**
 * Class XmlTableDefinitionTest.
 *
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class XmlTableDefinitionTest extends TestCase
{
	private $config;

	public function setUp()
	{
		$this->config = new MyConfiguration([
			'config' => [
				'path' => __DIR__ . '/../src/config',
				'prefix' => 'Test\\Orm\\Model\\Entity\\',
			],
		]);
	}

	public function testGetTableName() {
		$tableDefinition = new XmlTableDefinition($this->config,'Test\\Orm\\Model\\Entity\\Tricycle');

		$this->assertEquals('Tricycle', $tableDefinition->getTableName());
	}

	public function testGetPrimaryKey() {
		$tableDefinition = new XmlTableDefinition($this->config, 'Test\\Orm\\Model\\Entity\\Tricycle');

		$this->assertEquals(['id'], $tableDefinition->getPrimaryKey());
	}

	public function testGetColumnDefinition() {
		$tableDefinition = new XmlTableDefinition($this->config, 'Test\\Orm\\Model\\Entity\\Tricycle');

		$this->assertEquals(
			[
				'id' => new PersistentAttribute('id', ['integer', false, 'auto_increment']),
				'color' => new PersistentAttribute('color', ['string', true, null]),
				'brand' => new PersistentAttribute('brand', ['string', false, null]),
			],
			$tableDefinition->getColumnDefinitions()
		);
	}
}
