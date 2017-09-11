<?php

namespace Lib;

class ResultSetRow {
	private $_data;
	private $conn;
	private $table;
	private $language;
	public function __construct($data = array(),$table,$conn,$language) {
		$this->_data = (array)$data;
		$this->table = $table;
		$this->conn = $conn;
		$this->language = $language;
	}
	public function __get($name) {
		
		if (preg_match('/^own(.+)/',$name,$m)){			
			$subtable = strtolower($m[1]);
			$result = new ResultSet($subtable,$this->conn,$this->language);
			return $result->Where("`{$subtable}`.`{$this->table}_id` = ?",array((int)$this->_data['id']));
			
		}

			
		if (!isset($this->_data[$name]) && isset($this->_data[$name."_id"])) {
			$result = new ResultSet(strtolower($name),$this->conn,$this->language);
			return $result->Where("id = ?",array((int)$this->_data[$name.'_id']))->current();
		}

		if ($this->language) {
			if (isset($this->_data[$name.'_'.$this->language])) {
				$name = $name.'_'.$this->language;
			}
		}

		return $this->_data[$name];
	}
	public function __set($name,$value) {
		return $this->_data[$name] = $value;
	}
	public function __isset($name) {
		if ($this->language) {
			if (isset($this->_data[$name.'_'.$this->language])) {
				$name = $name.'_'.$this->language;
			}
		}
		
		if (isset($this->_data[$name]) || preg_match('/^own(.+)/',$name,$m)) {
			return true;
		}
		if (!isset($this->_data[$name]) && isset($this->_data[$name."_id"])) {
			return true;
		}
		
		
		if (!isset($this->_data[$name]) && isset($this->_data[$name."_id"])) {
			return true;
		}

		return false;
	}
	public function export() {
		return (object)$this->_data;
	}
	public function linkfy($field = 'linkamigavel') {
		

		if ($field == 'linkamigavel' && isset($this->linkamigavel)) {
			return $this->linkamigavel;
		} else {
			$field = "titulo";
		}
		$str = $this->{$field};

		return Util::linkfy($str).'-'.$this->id;
	}

	public function getArquivos($galeria='imagens',$limit=null) {
	  $type = $this->table;
	  $table = $type.'arquivo';

	

	  $rs = new ResultSet($table,$this->conn,$this->language);
	  $rs->Where("deleted=0")
	  	->Where("galeria=?",array($galeria))
	  	->Where("{$type}_id=?",array((int)$this->id))
	  	->sort("destaque","desc")
	  	->sort("ordenamento","desc");
	  if ($limit) {
	  	$rs->limit($limit);
	  	
	  }
	  return $rs;

	}

	public function tokenize() {
		$token = md5($this->id).$this->id;
		$token = $token.md5($token);
		return $token;
	}

	public function url($property='link') {
		$link = $this->{$property};
		if ($link != '') {
			if (!preg_match('/^http[s]?|tel|callto|mailto|skype|javascript/',$link,$m)) {
				$link = "http://{$link}";
			}
		}
		return $link;
	}
}