<?php
class B {
	public static $last_tick = 0;
	public static $last_line = 0;
	public static $start = 0;
	public static function register() {
		register_tick_function(array('B','tick'));
		static::$last_tick = microtime(true);
		static::$start = static::$last_tick;
	}
	public static function tick() {
		$diff = round(microtime(true) - static::$last_tick,3);
		$time = round(microtime(true) - static::$start,3);
		static::$last_tick = microtime(true);
		
		$bt = debug_backtrace();
		$bt = (object)array_shift($bt);
		
		$last_line = static::$last_line;
		static::$last_line = $bt->line;

		if ($diff < 0.1) return;
		
		

		$diff = number_format($diff,3,'.','');
		$time = number_format($time,3,'.','');
		error_log("{$bt->file}[{$last_line} - {$bt->line}]: {$time} ({$diff}s)");

	}
}
//B::register() - index.php;
//declare(ticks=1) - script.php;