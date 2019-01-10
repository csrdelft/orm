<?php
require_once 'Persistence/MySqlDatabaseTestCase.php';

use CsrDelft\Orm\DynamicEntityModel;

/**
 * DynamicEntityModelIntegrationTest.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 */
final class DynamicEntityModelIntegrationTest extends MySqlDatabaseTestCase {
	/**
	 * @var CarModel
	 */
	private $model;
	/**
	 * Returns the test dataset.
	 *
	 * @return \PHPUnit\DbUnit\DataSet\IDataSet
	 */
	protected function getDataSet() {
		return $this->createFlatXMLDataSet(__DIR__ . '/resources/integrationDataset.xml');
	}

	public function setUp() {
		parent::setUp();
		$this->model = DynamicEntityModel::makeModel('car');
	}

	public function testCount() {
		$this->assertEquals(2, $this->model->count());
	}

	public function testFind() {
		$car = $this->model->find('brand = "Opel"')->fetch();

		$this->assertEquals(4, $car->num_wheels);
	}

	public function testRetrieveUUID() {
		/** @var \CsrDelft\Orm\Entity\DynamicEntity $car */
		$car = $this->model->retrieveByUUID('1@car.csrdelft.nl');

		$this->assertEquals('Opel', $car->brand);
		$this->assertEquals('car', $car->getTableName());
		$this->assertEquals(['id'], $car->getPrimaryKey());
	}
}
