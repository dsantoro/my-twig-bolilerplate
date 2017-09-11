<?php

namespace Lib;

class Config {
	public static $data = array();

	public static function get($key) {
		return isset(static::$data[$key])?static::$data[$key]:null;
	}

	public static function set($key,$value) {
		static::$data[$key] = $value;
	}

	public static function load($filename) {
		static::$data = require($filename);
	}
}