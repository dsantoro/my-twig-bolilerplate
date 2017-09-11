<?php

namespace Lib;

class Hooks {
	public static function call($name,$args) {
		$hooks = glob(dirname(__DIR__).'/site/hooks/'.$name.'/*.php');
		
		foreach($hooks as $hook) {
			$cb = require($hook);
			call_user_func_array($cb, $args);
		}
	}
}