<?php

namespace Test\Orm\Model;

use CsrDelft\Orm\CachedPersistenceModel;
use Test\Orm\Model\Entity\Bike;

class BikeModel extends CachedPersistenceModel
{
	const ORM = Bike::class;

	protected static $instance;
}
