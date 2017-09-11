<?php

class Config {
	public static $config = array();
	public static function set($name='',$value=null) {
		static::$config[$name] = $value;
	}
	public static function get($name='') {
		if (!isset(static::$config[$name])) return null;
		return static::$config[$name];
	}
}