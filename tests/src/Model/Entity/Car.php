<?php
namespace Test\Orm\Model\Entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class Car extends PersistentEntity
{
	public $id;
	public $num_wheels;
	public $brand;
}
