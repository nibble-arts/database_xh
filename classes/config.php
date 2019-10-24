<?php

namespace database;

class Config {

	private static $config;


	// init config
	public static function init($data) {
		self::$config = $data["database"];
	}


	// get config parameter
	public static function get($name = false) {

		if (isset(self::$config[$name])) {
			return self::$config[$name];
		}

		elseif ($name === false) {
			return self::$config;
		}

		else {
			return false;
		}
	}


	// user magic method
	public static function __callStatic($name, $attr) {
		return self::get($name);
	}
}

?>