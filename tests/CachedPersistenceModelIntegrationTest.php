<?php
use CsrDelft\Orm\CachedPersistenceModel;
use Test\Orm\Model\BikeModel;

require_once 'Persistence/MySqlDatabaseTestCase.php';

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
		$this->getConnection()->getConnection()->query('DELETE FROM Bike WHERE id = 1;')->execute();
		$this->assertTrue($this->model->exists($bikes[0]));

	}
}
