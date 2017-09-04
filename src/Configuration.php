<?php
namespace CsrDelft\Orm;

use PDO;


/**
 * Configuration.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Configuration for ORM
 */
class Configuration {

	/**
	 * Initialize the ORM
	 *
	 * @param array $config [
	 *   'cache_path' => '/path/to/data/dir',
	 *   'db' => [
	 *     'host' => 'localhost',
	 *     'db' => 'myDatabase',
	 *     'user' => 'myUser',
	 *     'pass' => 'myPass'
	 *   ]
	 * ];
	 */
	public static function load(array $config) {
		assert(key_exists("cache_path", $config), "Cache path not set.");
		assert(key_exists("db", $config), "Database config not set");

		$db_conf = $config['db'];

		$ormMemcache = Persistence\OrmMemcache::init($config['cache_path']);

		$dsn = 'mysql:host=' . $db_conf['host'] . ';dbname=' . $db_conf['db'];
		$options = [
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		];
		$pdo = new PDO($dsn, $db_conf['user'], $db_conf['pass'], $options);

		$database = Persistence\Database::init($pdo);
		$databaseAdmin = Persistence\DatabaseAdmin::init($pdo);

		DependencyManager::addDependency($ormMemcache);
		DependencyManager::addDependency($database);
		DependencyManager::addDependency($databaseAdmin);
	}

}
