<?php

namespace DB;
use PDO;

class Db
{
	static private $errorInfo = array();

	static private $_instance = null;
	final private function __construct() {}
	final private function __clone() {}

	public static function instance()
	{
		if (self::$_instance === null)
		{
			$opt  = array(
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => TRUE
			);
			self::$_instance = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS,$opt);
		}
		return self::$_instance;
	}

	public static function __callStatic($method, $args) {
		return call_user_func_array(array(self::instance(), $method), $args);
	}
	public static function run($sql, $args = [])
	{
		$stmt = self::instance()->prepare($sql);
		$stmt->execute($args);

		self::$errorInfo = $stmt->errorInfo();

		return $stmt;
	}

	public static function getErrorInfo()
	{
		return self::$errorInfo;
	}
	public static function init() {
		self::instance();
	}
}