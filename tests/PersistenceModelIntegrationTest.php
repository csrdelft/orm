<?php
require_once 'Persistence/MySqlDatabaseTestCase.php';

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\PersistenceModel;

class Car extends PersistentEntity {
	public $id;
	public $num_wheels;
	public $brand;

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'num_wheels' => [T::Integer],
		'brand' => [T::String]
	];
	protected static $table_name = 'car';
	protected static $primary_key = ['id'];
}

class CarModel extends PersistenceModel {
	const ORM = Car::class;
}

/**
 * PersistenceModelTest.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 */
final class PersistenceModelIntegrationTest extends MySqlDatabaseTestCase {
	/**
	 * @var CarModel
	 */
	private $model;
	/**
	 * Returns the test dataset.
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet() {
		return $this->createFlatXMLDataSet('tests/integrationDataset.xml');
	}

	public function setUp() {
		parent::setUp();
		$this->model = CarModel::instance();
	}

	public function testCount() {
		$this->assertEquals(2, $this->model->count());
	}

	public function testFind() {
		$car = $this->model->find('num_wheels = 4')->fetch();
		$this->assertEquals('Opel', $car->brand);
	}

	public function testCreate() {
		$car = new Car();
		$car->num_wheels = 2;
		$car->brand = "Yamaha";

		$this->model->create($car);

		/** @var Car $car */
		$car = $this->model->query()
			->filterBy('num_wheels', 2)
			->getOne();

		$this->assertEquals('Yamaha', $car->brand);

		$this->assertEquals(3, $this->model->count());
	}

	public function testCountUnfiltered() {

		$query = $this->model->query()->filterBy('num_wheels', 5);

		$countBefore = $query->countUnfiltered();


		$car = new Car();
		$car->num_wheels = 5;
		$car->brand = "Opel";

		$this->model->create($car);

		$car2 = new Car();
		$car->num_wheels = 4;
		$car->brand = "Toyota";

		$this->model->create($car2);


		$this->assertEquals($countBefore + 2, $query->countUnfiltered());
		$this->assertEquals(1, $query->count());
	}

	public function testRetrieve() {
		$car = new Car();
		$car->id = 1;

		$this->model->retrieve($car);

		$this->assertEquals('Opel', $car->brand);
	}

	public function testRetrieveUUID() {
		/** @var Car $car */
		$car = $this->model->retrieveByUUID('1@car.csrdelft.nl');

		$this->assertEquals('Opel', $car->brand);
	}

	public function testUpdate() {
		$car = $this->model->find('id = 1')->fetch();
		$car->brand = "Mercedes";

		$this->model->update($car);

		$newcar = $this->model->find('id = 1')->fetch();

		$this->assertEquals('Mercedes', $newcar->brand);
	}

	public function testDelete() {
		$car = $this->model->find('brand = "Opel"')->fetch();
		$this->model->delete($car);

		$this->assertEquals(1, $this->model->count());
	}

	public function testExists() {
		$this->assertTrue($this->model->exists($this->model->find()->fetch()));
	}
}