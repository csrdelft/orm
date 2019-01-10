<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlResolve */

use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Exception\CsrOrmException;
use PHPUnit\DbUnit\DataSet\IDataSet;

require_once 'Persistence/MySqlDatabaseTestCase.php';

class Bike extends PersistentEntity {
	public $id;
	public $brand;

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'brand' => [T::String]
	];
	protected static $table_name = 'bike';
	protected static $primary_key = ['id'];
}

class BikeModel extends CachedPersistenceModel {
	const ORM = Bike::class;
}

/**
 * CachedPersistenceModelIntegrationTest.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 31/03/2017
 */
class CachedPersistenceModelIntegrationTest extends MySqlDatabaseTestCase {
	/** @var  CachedPersistenceModel */
	private $model;
	/**
	 * Returns the test dataset.
	 *
	 * @return IDataSet
	 */
	protected function getDataSet() {
		return $this->createFlatXMLDataSet(__DIR__ . '/resources/CachedPersistenceModelIntegrationTest.xml');
	}

	public function setUp() {
		parent::setUp();
		$this->model = BikeModel::instance();
	}

	/**
	 * @throws CsrOrmException
	 */
	public function testPrefetch() {
		$bikes = $this->model->prefetch();
		// CachedPersistenceModel assumes nobody else touches the db in this request
		$this->getConnection()->getConnection()->query('DELETE FROM bike WHERE id = 1;')->execute();
		$this->assertTrue($this->model->exists($bikes[0]));

	}
}
