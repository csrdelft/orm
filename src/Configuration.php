<?php
namespace CsrDelft\Orm;

use PDO;


/**
 * Configuration.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Configuration for ORM
 *
 */
class Configuration {

	/**
	 * Initialize the ORM
	 *
	 * @param array $config array(
	 *   'cache_path' => '/path/to/data/dir',
	 *   'db' => array(
	 *     'host' => 'localhost',
	 *     'db' => 'myDatabase',
	 *     'user' => 'myUser',
	 *     'pass' => 'myPass'
	 *   )
	 * );
	 */
	public static function load(array $config) {
		assert('isset($config["cache_path"]);');
		assert('isset($config["db"]);');

		$db_conf = $config['db'];

		Persistence\OrmMemcache::init($config['cache_path']);

		$dsn = 'mysql:host=' . $db_conf['host'] . ';dbname=' . $db_conf['db'];
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
		$pdo = new PDO($dsn, $db_conf['user'], $db_conf['pass'], $options);

		Persistence\Database::init($pdo);
		Persistence\DatabaseAdmin::init($pdo, '');
	}

}