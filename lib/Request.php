<?php

namespace Lib;

class Request {
	public function __construct($data) {
		foreach((array)$data as $key=>$value) {
			$this->{$key} = $value;
		}
	}
	public static function Post($name='',$default=null) {
		return isset($_POST[$name])?$_POST[$name]:$default;
	}
	public static function Get($name='',$default=null) {
		return isset($_GET[$name])?$_GET[$name]:$default;
	}
	public static function Cookie($name='',$default=null) {
		return isset($_COOKIE[$name])?$_COOKIE[$name]:$default;
	}

}
