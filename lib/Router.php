<?php

namespace Lib;

use \stdclass;
use phpFastCache\CacheManager;


class Router {
	public static function init() {
		$req = new Request(array());
		$req->start_time = microtime(true);

		if (isset($_SERVER['HTTP_HOST'])) {
			$base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
			$base_url .= '://' . $_SERVER['HTTP_HOST'];	
			$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
		} else {
			$base_url = 'http://localhost/';
		}

		$req->original_base_url = $base_url;

		if (!isset($_SERVER['PATH_INFO'])) {
			$_SERVER['PATH_INFO'] = '';
		}
		$path_info = trim($_SERVER['PATH_INFO'],'/');
		$script_name = trim($_SERVER['REQUEST_URI'],'/');
		$script_name = str_replace($path_info,'',$script_name);
		$script_name = preg_replace('/\?.+$/','',$script_name);

		$req->path_info = $path_info;
		$req->post = (object)$_POST;
		$req->get = (object)$_GET;
		$req->cookies = (object)$_COOKIE;


		
		if (preg_match('/^http[s]?:\/\/[^\/]+\/(?P<site_dir>.+)/', $base_url,$m)) {
			$script_name = str_replace(trim($m['site_dir'],'/'),'',$script_name);
		}

		
		$base_url = trim($base_url.trim($script_name,'/'),'/').'/';


		$languages = Config::get('languages');

		$regex = '/^(?P<language>[^\/]+)?(\/(?P<controller>[^\/]+))?(\/(?P<method>[^\/]+))?(\/(?P<params>.+))?$/';
		if (count($languages) < 2) {
			$regex = '/^(?P<controller>[^\/]+)?(\/(?P<method>[^\/]+))?(\/(?P<params>.+))?$/';
		}

		if (preg_match($regex, $path_info,$m)) {
			
			$req->language = isset($m['language'])?$m['language']:null;
			$req->controller = isset($m['controller'])?$m['controller']:null;
			$req->method = isset($m['method'])?$m['method']:null;
			$req->params = isset($m['params'])?$m['params']:array();
			$req->type = $_SERVER['REQUEST_METHOD'];
			$req->ajax = false;

			if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				$req->ajax = true;
			}

			if (!$req->language) {
				$l = array_reverse($languages);
				$l = end($l);
				$req->language = $l;
			}

			if (!in_array($req->language, $languages)) {
				$language =  end(array_reverse($languages));
				header("Location: {$base_url}{$language}/{$path_info}");
				exit;
			}

			if (!$req->controller) {
				$req->controller = 'home';
			}
			if (!$req->method) {
				$req->method = 'index';
			}
			if ($req->params) {
				$req->params = explode('/',trim($req->params,'/'));
			} else {
				$req->params = array();
			}
			

			if (count($languages) > 1) {
				$base_url = $base_url.$req->language.'/';
			}

			$req->base_url = $base_url;




			$classname = preg_replace('/[^\d\w]/',' ',$req->controller);						
			$classname = ucwords($classname);
			$classname = '\\Controller\\'.preg_replace('/\s/', '', $classname).'Controller';
			
			if (!class_exists("$classname")) {
				$classname = "\\Controller\\DefaultController";
			}

			$method = preg_replace('/[^\d\w]/',' ',$req->method);						
			$method = ucwords($method);
			$method = preg_replace('/\s/', '', $method).'Action';

			if (!method_exists($classname, $method)) {
				$method = "IndexAction";
			}

			$req->uri = trim($req->controller.'/'.$req->method.'/'.join('/',$req->params),'/');


			$res = new Response($req);

			/*
			$res->on('background',function($req,$classname,$method){
				error_log("{$classname}->{$method} : ".($req->end_time - $req->start_time).'s');
			},array($req,$classname,$method));
			*/

			$acl = Config::get('acl');
			$rule = "public";
			foreach($acl['rules'] as $regex=>$r) {
				if (preg_match($regex, "{$req->controller}/{$req->method}")) {
					$rule = $r;
				}
			}
		
			if ($rule != 'public' && !isset($req->cookies->auth_token)) {
				if ($req->ajax) {
					$res->json(array(
						'redirect_url' => $req->base_url.$acl['redirect']
					));
				} else {
					$res->redirect($acl['redirect']);				
				}
			} else {

				$cache = Config::get('cache');
				$cacheTime = 0;
				foreach($cache['rules'] as $regex=>$r) {
					if (preg_match($regex, "{$req->controller}/{$req->method}")) {
						$cacheTime = $r;
					}
				}
				$cached = null;
				if ($cacheTime !== 0 && !isset($req->get->noCache) && in_array($req->type, array('GET'))) {					
					CacheManager::setup($cache['config']);
					$cache = phpFastCache();

					$cache_key = sha1($req->base_url.$req->controller.'/'.$req->method.'/'.join('/',$req->params).'?'.http_build_query((array)$req->get));

					$cached = $cache->get($cache_key);
				}

				if (!$cached) {
					$res->header('X-Cache','miss');
					
					Hooks::call('before-action',array(&$req,&$res));
					call_user_func_array(array($classname,$method), array($req,&$res));				
					Hooks::call('after-action',array(&$req,&$res));
					
					if ($cacheTime !== 0) {						
						$res->on('background',function($cache,$cache_key,$res,$cacheTime){
							$cache->set($cache_key,$res->export(),$cacheTime);
						},array($cache,$cache_key,$res,$cacheTime));					
					}
				} else {					
					$res->import($cached);
					$res->header('X-Cache','hit');
				}
			}

			$req->end_time = microtime(true);
			

			$res->end();

		} else {

		}

		
	}
}