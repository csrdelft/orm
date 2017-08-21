<?php
namespace Test\Orm\Model;

use CsrDelft\Orm\PersistenceModel;
use Test\Orm\Model\Entity\Car;

class CarModel extends PersistenceModel
{
	const ORM = Car::class;
	protected static $instance;
}
