[![Codacy Badge](https://api.codacy.com/project/badge/Grade/109c2ffa39c846559a9bb8441a5b8dc2)](https://www.codacy.com/app/C-S-R-Delft/orm?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=csrdelft/orm&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/109c2ffa39c846559a9bb8441a5b8dc2)](https://www.codacy.com/app/C-S-R-Delft/orm?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=csrdelft/orm&amp;utm_campaign=Badge_Coverage)
[![Build Status](https://travis-ci.org/csrdelft/orm.svg?branch=master)](https://travis-ci.org/csrdelft/orm)

# C.S.R. Delft ORM

A simple object-relational mapper for PHP. We currently use this library in production
on [csrdelft.nl](https://csrdelft.nl). 

## Installation

Install with composer

```
composer require csrdelft/orm
```

Before the ORM can be used the cache and database must be initialized. The memcache needs
a writable path and the database and database admin need a host, database, username and
password. After this any model has access to the database.

```php
CsrDelft\Orm\Configuration::load([
  'cache_path' => '/path/to/data/dir',
  'db' => [
    'host' => 'localhost',
    'db' => 'myDatabase',
    'user' => 'myUser',
    'pass' => 'myPass'
  ]
]);
```

## Usage

The ORM relies on models and entities. Models are classes which interface with the database. Entities
are data classes which contain a definition for the database tables. This document will give a brief
overview of the basic things you need to know in order to get started.

### Entity

An entity is an object containing data, like for instance a car, person, etc.

When you want to save an entity to the database, you'll have to extend the class 
`PersistentEntity`. An entity must contain a few variables, which are discussed
below. An entity must only contain logic about itself, not logic about other classes
or about other instances of the same entity. This should be in the Model (or the controller which is
not part of this library).

Entities are placed in the folder `model/entity/` and are named `EntityName.php`.

#### Variables in an entity

For each attribute of an entity there must be a public variable. These will be used by the model
when loading from a database.

```php
public $id;
public $num_wheels;
public $color;
```

##### `$table_name`

The name of the table in the database.

```php
protected static $table_name = 'cars';
```

##### `$persistent_attributes`

An array of attributes of the entity, mapped to a type.

A Type is an array, with the following values.

* 0: Type from `T` (`PersistentAttributeType.enum`)
* 1: Is this variable nullable?
* 2: If 0 is `T::Enumeration`, the enum class (extends `PersistentEnum`). Else 'extra', 
for instance `auto_increment` or comment.

```php
protected static $persistent_attributes = [
  'id' => [T::Integer, false, 'auto_increment'],
  'num_wheels' => [T::Integer],
  'color' => [T::Enumeration, false, 'ColorEnum']
);
```

##### `$primary_key`

An array with the full primary key.

```php
protected static $primary_key = ['id'];
```

#### Example

**`model/entities/Car.php`**

```php
class Car extends PersistentEntity {
  public $id;
  public $num_wheels;
  public $color;

  public function carType() {
    if ($this->num_wheels == 4) {
      return "Normal car";
    } else {
      return "Weird car";
    }
  }

  protected static $table_name = 'cars';
  protected static $persistent_attributes = [
    'id' => [T::Integer, false, 'auto_increment'],
    'num_wheels' => [T::Integer],
    'color' => [T::Enumeration, false, 'ColorEnum']
  ];
  protected static $primary_key = ['id'];
}
```

### Model

A model has to extend the `PersistenceModel` class. A model is the owner of a 
specific entity. A model can be accessed everywhere with the public static 
`instance()` method. This should however be avoided where possible.

Models should be placed in `model/`.

#### Variables in a model

A model has a few static variables which must be defined.

##### `ORM`

The constant `ORM` defines which entity this model is the owner of. This is a string or class.

```php
const ORM = 'Car';
```

```php
const ORM = Car::class;
```

##### `$default_order`

This is the default value to use for the order when selecting from the database.

```php
protected static $default_order = 'num_wheels DESC';
```

#### Functions in a model

The following functions can be used on a model

### `find($criteria, $criteria_params, ...) : PersistentEntity[]`

Find entities in the database filtered on criteria. The syntax for this should be familiar if you 
ever worked with PDO in PHP. The `$criteria` is the `WHERE` clause of the underlying select statement, you can
put `?`'s here where variables are. The criteria params are where you fill these variables. Criteria 
params are automatically filtered and safe for user input.

```php
CarModel::instance()->find('num_wheels = ? AND color = ?', [$normal_car_wheels, $car_color]);
```

### `count($criteria, $criteria_params) : int`

Count the number of entities which pass the criteria, same as `find(..)`. Creates statements like
`SELECT COUNT(*) ...` which are faster than counting in PHP.

### `exists($entity) : boolean`

Check whether or not an entity exists in the database. 

### `create($entity) : string`

Save a new entity into the database. Returns the id of the inserted entity.

### `update($entity) : int`

Store an entity in the database, replacing the entity with the same primary key.

### `delete($entity) : int`

Delete an entity from the database.

#### Example

**`model/CarModel.php`**

```php
require_once 'model/entity/Car.php';

class CarModel extends PersistenceModel {
  const ORM = 'Car';
  
  public function findByColor($color) {
    return $this->find('color = ?', [$color]);
  }
}
```

**`index.php`**

```php
require_once 'model/CarModel.php';

$model = CarModel::instance();
$cars = $model->find();
$actual_cars = $model->find('num_wheels = 4');
$yellow_cars = $model->findByColor('yellow');
```

## Database update

This orm can check the models for you. To enable this feature you need to define
the global constant `DB_CHECK` as `true`. After that you can check the DatabaseAdmin for
any updated tables. This only checks tables which are in use and must be done
after all models are used. It is also possible to enable automatically updating
the database by defining the global constant `DB_MODIFY` as `true`. This will
update tables according to your models. This will not try to migrate data, so
be careful. `DB_MODIFY` will not drop tables. for this you will need to define
`DB_DROP` as `true`.

```php
if (DB_CHECK) {
    $queries = DatabaseAdmin::instance()->getQueries();
    if (!empty($queries)) {
        if (DB_MODIFY) {
            header('Content-Type: text/x-sql');
            header('Content-Disposition: attachment;filename=DB_modify_' . time() . '.sql');
            foreach ($queries as $query) {
                echo $query . ";\n";
            }
            exit;
        } else {
            var_dump($queries);
        }
    }
}
```

## Database transactions

To wrap multiple database calls in a transaction you can use `Database::transaction(Closure)`.
This function wraps another function in a database transaction. Any exception thrown causes the
transaction to be rolled back. If the database is in a transaction this function will just call the
`Closure` without trying to create a new transaction.

```php
$car = new Car();
Database::transaction(function () use ($car) {
    CarModel::instance()->create($car);
    CarWheelModel::instance()->create($car->getWheels());
});
```

## Dependency Injection

The orm does a very simple way of dependency injection. When a instance of a model is created is created
it tries to lookup any dependencies which extend `DependencyManager` if they are found they are wired into the 
model and available for use. There can only be one version of a model and this is kept track of in `DependencyManager`.

### Example

```php
class OwnerModel extends PersistenceModel {
  const ORM = 'Owner';
  
  /** @var CarModel */
  protected $carModel;
  
  protected function __construct(CarModel $carModel) {
    $this->carModel = $carModel;
  }
  
  public function getCarsForOwner(Owner $owner) {
    return $this->carModel->find('owner = ?', [$owner->id]);
  }
}

class CarModel extends PersistenceModel {
  const ORM = 'Car';
  
  public function findByColor($color) {
    return $this->find('color = ?', [$color]);
  }
}
```

```php
$owner = OwnerModel::instance()->find('id = 1');

$cars = OwnerModel::instance()->getCarsForOwner($owner);
```
