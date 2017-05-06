<?php
use CsrDelft\Orm\Entity\T;

require_once 'Persistence/MySqlDatabaseTestCase.php';

class Bike extends \CsrDelft\Orm\Entity\PersistentEntity {
	public $id;
	public $brand;

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'brand' => [T::String]
	];
	protected static $table_name = 'bike';
	protected static $primary_key = ['id'];
}

class BikeModel extends \CsrDelft\Orm\CachedPersistenceModel {
	const ORM = Bike::class;

	protected static $instance;
}

/**
 * CachedPersistenceModelIntegrationTest.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 31/03/2017
 */
class CachedPersistenceModelIntegrationTest extends MySqlDatabaseTestCase {
	/** @var  \CsrDelft\Orm\CachedPersistenceModel */
	private $model;
	/**
	 * Returns the test dataset.
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet() {
		return $this->createFlatXMLDataSet('tests/CachedPersistenceModelIntegrationTest.xml');
	}

	public function setUp() {
		parent::setUp();
		$this->model = BikeModel::instance();
	}

	public function testPrefetch() {
		$bikes = $this->model->prefetch();
		// CachedPersistenceModel assumes nobody else touches the db in this request
		$this->getConnection()->getConnection()->query('DELETE FROM bike WHERE id = 1;')->execute();
		$this->assertTrue($this->model->exists($bikes[0]));

	}
}