# Installing

```sh
$ composer install
```

# Running tests

To run the tests you will need a mysql server running on `localhost` with a user `travis` without password. You also need a database `orm_test`

To run the tests:
```sh
$ vendor/bin/phpunit
```

Make sure all tests pass before submitting a Pull Request.