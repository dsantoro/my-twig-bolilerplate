<?php

class ErrorHandler {
	public static function register() {
		set_error_handler(function($numero,$msg,$file,$line,$ctx){
			$error = new stdclass();
			$error->numero = $numero;
			$error->msg = $msg;
			$error->file = $file;
			$error->line = $line;
			$error->context = $ctx;
			echo "<pre>";
			var_dump($error);
			echo "</pre>";

			return FALSE;
		});

		set_exception_handler(function($exception){
			$error = new stdclass();
			$error->numero = $exception->getCode();
			$error->msg = $exception->getMessage();
			$error->file = $exception->getFile();
			$error->line = $exception->getLine();
			$error->context = $exception->getTrace();
			echo "<pre>";
			var_dump($error);
			echo "</pre>";
			return FALSE;
		});
	}
}