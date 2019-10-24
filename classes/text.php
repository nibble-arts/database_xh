<?php

/* CMSimple_XH config class
 *
 * @ Version 1.0
 * static class to display language text
 * init with text array
 */

namespace database;

class Text {
	
	private static $text;
	
	// init view
	public static function init ($text) {
		self::$text = $text;

	}
	

	// get by magic method
	public static function __callStatic($name, $attr) {

		return self::get($name);
	}


	// get multilingual text
	public static function get ($code) {

		if (isset(self::$text [$code])) {
			return self::$text [$code];
		}
		else {
			return $code;
		}
	}
}

?>