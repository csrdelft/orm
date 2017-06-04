<?php
namespace Test\Orm\Model\Entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class Car extends PersistentEntity
{
	public $id;
	public $num_wheels;
	public $brand;

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'num_wheels' => [T::Integer],
		'brand' => [T::String]
	];
	protected static $table_name = 'Car';
	protected static $primary_key = ['id'];
}
