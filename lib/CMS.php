<?php

namespace Lib;

use \mysqli;
use \PDO;

class CMS {
	private $_language;
	private $_host;
	private $_user;
	private $_pass;
	private $_dbname;
	private $_conn;

	public function setLanguage($language) {
		$this->_language = $language;
	}
	public function __construct($host,$user,$pass,$dbname) {
		$this->_host = $host;
		$this->_user = $user;
		$this->_pass = $pass;
		$this->_dbname = $dbname;

	}

	public function getConn() {
		if ($this->_conn !== null) return $this->_conn;
		
		/*
		$this->_conn = new mysqli($this->_host,$this->_user,$this->_pass,$this->_dbname);
		$this->_conn->set_charset("utf8");
		*/
		$this->_conn = new PDO("mysql:host={$this->_host};dbname={$this->_dbname}",$this->_user,$this->_pass);
		$this->_conn->exec("set names utf8");
		return $this->_conn;
	}

	public function __call($name,$params) {
		$conn = $this->getConn();
		return new ResultSet($name,$conn,$this->_language);
	}

	public function __get($name) {

		$conn = $this->getConn();

		return new ResultSet($name,$conn,$this->_language);
	}

	public function __isset($name) {
		return true;
	}
}