<?php
namespace CsrDelft\Orm;


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

		Persistence\Database::init($db_conf['host'], $db_conf['db'], $db_conf['user'], $db_conf['pass']);
		Persistence\DatabaseAdmin::init($db_conf['host'], $db_conf['db'], $db_conf['user'], $db_conf['pass']);
	}

}