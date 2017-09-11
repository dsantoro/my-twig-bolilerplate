<?php

namespace Lib;

use \PHPMailer;
//use \R;

class Mailer extends PHPMailer {

	public function __construt($exceptions = null) {
		parent::__construct($exceptions);

		$this->CharSet = 'utf-8';
		$app = \Lib\Config::get('app');
		if ($app->debug==true) {
	
			$this->Host = 'mail.openweb.com.br';
			$this->Username = 'fabioteste@openweb.com.br';
			$this->Password = 'sgt357';
			$this->Port = 587;
			$this->SMTPAuth = true;
			$this->Mailer = "smtp";
		
		}
		
	}
	
	public function AddAddress($email, $nome = '') {
		$config = \Lib\Config::get('app');
		if (in_array($_SERVER['SERVER_NAME'],array('localhost','drop.studiogt.com.br')) || $config->debug) {
			$email = $config->debug_email;
		}
		parent::AddAddress($email, $nome);
	}
	
}