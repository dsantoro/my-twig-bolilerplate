<?php


class Request {
	public static function post($name='',$default=null) {
		return isset($_POST[$name])?$_POST[$name]:$default;
	}
	public static function get($name='',$default=null) {
		return isset($_GET[$name])?$_GET[$name]:$default;
	}
	public static function getCookie($name='',$default=null) {
		return isset($_COOKIE[$name])?$_COOKIE[$name]:$default;
	}
	public static function setCookie($name='',$value=null) {
		setcookie($name,$value,time()+36500000,'/');
		$_COOKIE[$name] = $value;
	}
}
