<?php
use Test\Orm\Model\Entity\Car;

require_once 'Persistence/MySqlDatabaseTestCase.php';

/**
 * The PersistentXmlEntityTest file.
 */
final class PersistentXmlEntityTest extends MySqlDatabaseTestCase
{
	public function testXmlParser() {
		$car = Car::class;
		$parser = new \CsrDelft\Orm\Parser\EntityParserXml($car);

		$xml = $parser->getXml();

		echo (string) $xml->attributes()->{'tableName'};

		$this->assertTrue(true);
	}

	/**
	 * Returns the test dataset.
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet() {
		return $this->createFlatXMLDataSet('tests/integrationDataset.xml');
	}

}
