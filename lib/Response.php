<?php

namespace Lib;

use \phpQuery;

class Response {
	
	private $content = null;
	private $redirect_url = null;
	private $headers = array('X-UA-Compatible'=>'IE=11,chrome=1');
	private $cookies = array();	

	private $req = null;

	public $view = null;

	public $events = array();

	public function __construct($req) {
		$this->req = $req;
	}

	public function getView() {
		if ($this->view !== null) return $this->view;
		$this->view = new View();
		return $this->view;
	}




	public function json($obj) {
		$this->header('Content-type','application/json');
		$this->content = json_encode($obj,JSON_HEX_QUOT);
	}

	public function send($content) {
		$this->content = $content;
	}

	public function getContent() {
		return $this->content;
	}

	public function getHeders() {
		return $this->headers;
	}

	public function setHeaders($headers) {
		$this->headers = $headers;
	}

	public function header($key,$value) {
		$this->headers[$key] = $value;
	}

	public function cookie($key,$value) {
		$this->cookies[$key] = $value;
	}

	public function getCookies() {
		return $this->cookies;
	}

	public function setCookies($cookies) {
		$this->cookies = $cookies;
	}

	public function redirect($url) {
		if (!preg_match('/^http[s]?/',$url)) {
			$url = trim($this->req->base_url,'/').'/'.trim($url,'/');
		}
		$this->redirect_url = $url;
	}

	public function render($tpl,$vars = array()) {		
		$cms = Config::get('cms');
		if ($vars['req']) {
			$req = $vars['req'];
			$cms->setLanguage($req->language);


		}
		$vars['cms'] = $cms;		

		
		//$vars['i18n'] = new I18n($req->language);
		I18n::setup($req->language);

		$view = $this->getView();
		$this->content = $view->render($tpl,$vars);

		//$this->header('Link','<static/css/style.css>; rel=preload; as=style');
		//$doc = phpQuery::newDocument($this->content);
		//phpQuery::selectDocument($doc);

		/*
		$links = pq('link[rel=stylesheet]');
		foreach($links as $link) {
			$url = pq($link)->attr('href');
			header("Link: <{$url}>; rel=preload; as=style",false);
		}

		$links = pq('script[src]');
		foreach($links as $link) {
			$url = pq($link)->attr('src');
			header("Link: <{$url}>; rel=preload; as=script",false);
		}

		$links = pq('img[src]');
		foreach($links as $link) {
			$url = pq($link)->attr('src');
			header("Link: <{$url}>; rel=preload; as=image",false);
		}
		*/
		//flush();

	}

	public function on($event,$handler,$params=array()) {
		if (!isset($this->events[$event])) {
			$this->events[$event] = array();			
		}
		$this->events[$event][] = (object) array(
			'handler' => $handler,
			'params' => $params
		);
	}

	public function trigger($event) {
		if (!isset($this->events[$event])) return;

		foreach($this->events[$event] as $handler) {
			call_user_func_array($handler->handler, $handler->params);
		}
	}

	public function end() {
		session_write_close();
		ignore_user_abort();
		set_time_limit(0);

		$length = strlen($this->content);
		$this->header("Content-Length",$length);
		$this->header("Content-Encoding","none");
		$this->header("Connection","close");

		if ($this->redirect_url !== null) {
			$this->header("Location",$this->redirect_url);			
		}

		
		foreach($this->headers as $key=>$value) {
			header("{$key}:{$value}",false);
		}
		foreach ($this->cookies as $key => $value) {
			setcookie($key,$value,time()+3650000000,'/');
		}

		echo $this->content;
		while (ob_get_level() > 0) {
		    @ob_end_flush();
		}
		@flush();
		$this->trigger('background');
	}

	public function export() {
		return (object)array(
			'content' => $this->content,
			'headers' => $this->headers,
			'cookies' => $this->cookies
		);
	}

	public function import($data) {
		$this->content = $data->content;
		$this->headers = $data->headers;
		$this->cookies = $data->cookies;
	}
}