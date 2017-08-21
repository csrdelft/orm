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

	protected static $instance = null;

	protected $configPath;
	protected $configPrefix;

	/**
	 * Initialize the ORM
	 *
	 * @param array $config [
	 *   'cache_path' => '/path/to/data/dir',
	 *   'config' => [
	 *     'path' => '/path/to/config/dir',
	 *     'prefix' => '',
	 *   ],
	 *   'db' => [
	 *     'host' => 'localhost',
	 *     'db' => 'myDatabase',
	 *     'user' => 'myUser',
	 *     'pass' => 'myPass'
	 *   ]
	 * ];
	 */
	public function __construct(array $config) {
		assert(static::$instance == null);
		assert(key_exists('cache_path', $config), "Cache path not set.");
		assert(key_exists('config', $config), "Config path not set.");
		assert(key_exists('db', $config), "Database config not set");

		$db_conf = $config['db'];

		Persistence\OrmMemcache::init($config['cache_path']);

		$dsn = 'mysql:host=' . $db_conf['host'] . ';dbname=' . $db_conf['db'];
		$options = [
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		];
		$pdo = new PDO($dsn, $db_conf['user'], $db_conf['pass'], $options);

		Persistence\Database::init($pdo);
		Persistence\DatabaseAdmin::init($pdo);

		$this->pdo = $pdo;
		$this->configPath = $config['config']['path'];
		$this->configPrefix = $config['config']['prefix'];

		static::$instance = $this;
	}

	/**
	 * @return static
	 */
	public static function instance() {
		assert(static::$instance != null, 'Configuration not initialized.');
		return static::$instance;
	}

	public function getConfigPath() {
		return $this->configPath;
	}

	public function getConfigPrefix() {
		return $this->configPrefix;
	}
}
