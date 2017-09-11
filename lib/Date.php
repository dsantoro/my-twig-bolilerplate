<?php

namespace Lib;

class Date {
	public $timestamp;
	public function __construct($data=null) {
		if (is_null($data)) {
			$this->timestamp = time();
		} else if (is_float($data)) {
			$this->timestamp = (int)round($data);
		} else if (is_integer($data)) {
			$this->timestamp = $data;
		} else if (preg_match('/^[0-9]{4}\-[0-1][0-9]\-[0-3][0-9]$/', $data)) {
			$this->timestamp = strtotime($data.' 00:00:00');
		} else if (preg_match('/^[0-9]{4}\-[0-1][0-9]\-[0-3][0-9]\s[0-2][0-9]\:[0-5][0-9]$/', $data)) {
			$this->timestamp = strtotime($data.':00');
		} else if (preg_match('/^[0-9]{4}\-[0-1][0-9]\-[0-3][0-9]\s[0-2][0-9]\:[0-5][0-9]\:[0-5][0-9]$/', $data)) {
			$this->timestamp = strtotime($data);
		} else if (preg_match('/^[0-3][0-9]\/[0-1][0-9]\/[0-9]{4}$/',$data)) {
			$d = join('-',array_reverse(explode('/',$data)));
			$this->timestamp = strtotime($d);
		} else if (preg_match('/^[0-3][0-9]\/[0-1][0-9]\/[0-9]{4}\s[0-2][0-9]\:[0-5][0-9]$/',$data)) {
			list($d,$h) = explode(' ',$data);
			$d = join('-',array_reverse(explode('/',$d)));
			$this->timestamp = strtotime($d.' '.$h.':00');
		} else if (preg_match('/^[0-3][0-9]\/[0-1][0-9]\/[0-9]{4}\s[0-2][0-9]\:[0-5][0-9]\:[0-5][0-9]$/',$data)) {
			list($d,$h) = explode(' ',$data);
			$d = join('-',array_reverse(explode('/',$d)));
			$this->timestamp = strtotime($d.' '.$h);
		}
	}
	public function getYear() {
		return date('Y',$this->timestamp);
	}
	public function getMonth() {
		return date('m',$this->timestamp);
	}
	public function getDay() {
		return date('d',$this->timestamp);
	}
	public function getHours() {
		return date('H',$this->timestamp);
	}
	public function getMinutes() {
		return date('i',$this->timestamp);
	}
	public function getSeconds(){
		return date('s',$this->timestamp);
	}
	public function getTime(){
		return $this->timestamp;
	}
	public function format($format) {
		return date($format,$this->timestamp);
	}
	public function getMonthAbrev() {
		$application = Config::get('application');

		$monthr = $application['data']['monthr'];
		return $monthr[$this->getMonth()*1];
	}
	public function getMonthName() {
		$application = Config::get('application');

		$month = $application['data']['month'];
		return $month[$this->getMonth()*1];
	}
}

