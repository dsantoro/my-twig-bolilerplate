<?php


namespace Lib;

use \Twig_Loader_Filesystem;
use \Twig_SimpleFilter;
use \Twig_Environment;
use \Twig_SimpleFunction;

class View {	
	public $twig;

	public function __construct($loader = null,$options=array()) {		
		if ($loader === null) {
			$app = Config::get('application');
			$loader = new Twig_Loader_Filesystem(dirname(__DIR__).'/'.$app['path'].'/views');
		}

		$twig = new Twig_Environment($loader,$options);
		$filter = new Twig_SimpleFilter('sortby',function($obj,$sort='ordenamento',$order='asc') {	
			return $obj->sort($sort,$order);
		});
		$twig->addFilter($filter);

		$filter = new Twig_SimpleFilter('groupby',function($obj,$groupby='id') {	
			return $obj->groupby($groupby);
		});
		$twig->addFilter($filter);


		$filter = new Twig_SimpleFilter('fetchAs',function($obj,$table) {	
			return $obj->fetchAs($table);
		});
		$twig->addFilter($filter);

		$filter = new Twig_SimpleFilter('limit',function($obj,$offset=0,$rows=null) {	
			return $obj->limit($offset,$rows);
		});
		$twig->addFilter($filter);

		$filter = new Twig_SimpleFilter('where',function($obj,$where='',$values=array()) {	
			return $obj->Where($where,$values);
		});
		$twig->addFilter($filter);

		$filter = new Twig_SimpleFilter('innerjoin',function($obj,$table='',$condition='',$values=array()) {	
			return $obj->join($table,$condition,$values,'INNER');
		});
		$twig->addFilter($filter);

		$filter = new Twig_SimpleFilter('leftjoin',function($obj,$table='',$condition='',$values=array()) {	
			return $obj->join($table,$condition,$values,'LEFT');
		});
		$twig->addFilter($filter);

		$filter = new Twig_SimpleFilter('select',function($obj,$field='') {	
			return $obj->select($field);
		});
		$twig->addFilter($filter);

		$filter = new Twig_SimpleFilter('debug',function($obj,$field='') {	
			$obj->setDebug(true);
			return $obj;
		});
		$twig->addFilter($filter);

		$filter = new Twig_SimpleFilter('text_break',function($text='',$limit) {	
			return Util::text_break($text,$limit);
		});
		$twig->addFilter($filter);
		
		$filter = new Twig_SimpleFilter('text_break2',function($text='',$limit) {	
			return Util::text_break2($text,$limit);
		});
		$twig->addFilter($filter);


		$filter = new Twig_SimpleFilter('linkfy',function($obj,$field = 'titulo') {	
			return Util::linkfy($obj->{$field}).'-'.$obj->id;
		});

		$twig->addFilter($filter);
		
		
		$filter = new Twig_SimpleFilter('buildQuery',function($obj) {	
			return http_build_query((array)$obj);
		});

		$twig->addFilter($filter);
		
		
		$function = new Twig_SimpleFunction('_', function ($key, $default = '') {
		    return I18n::get($key, $default);
		});
		$twig->addFunction($function);

		
		$function = new Twig_SimpleFunction('getMeta', function ($uri) {
		    $cms = Config::get('cms');
		    do {
		    	$meta = $cms->meta->Where("pagina=?",array($uri))->current();
		    	if (!isset($meta->id)) {						
		    		$uri = explode('/',$uri);
		    		array_pop($uri);
		    		$uri = join('/',$uri);
		    	}
		    } while (!isset($meta->id) && $uri!="");
		    if (!isset($meta->id)) {
		    	$meta = $cms->meta->Where("pagina=?",array('home'))->current();
		    }
		    return $meta;
		});
		$twig->addFunction($function);
		

		$function = new Twig_SimpleFunction('getPagination', function ($url, $max_pages, $page = 1, $pages = 5) {
		    $pagination = new \stdclass();
		    $pagination->pages = array();
		    $pagination->prev = false;
		    $pagination->next = false;
			
			if ($page < 1) $page = 1;

		    $fp = $page - $pages;
		    if ($fp < 1) $fp = 1;
		    $lp = $fp + $pages*2;
		    if ($lp > $max_pages) {
		    	$lp = $max_pages;
		    	$fp = $lp - $pages*2;
		    	if ($fp < 1) {
		    		$fp = 1;
		    	}
		    }
		    
		    if ($page > 1) {
		    	$get = $_GET;
		    	$get['page'] = $page - 1;
		    	$link = new \stdclass();
		    	$link->title = '&laquo;';
		    	$link->url = $url.'?'.http_build_query($get);
		    	$pagination->prev = $link;
		    }

		    for ($i = $fp; $i<=$lp; $i++ ) {
		    	$get = $_GET;
		    	$get['page'] = $i;
		    	$link = new \stdclass();
		    	$link->title = $i;
		    	$link->active = $page == $i;
		    	$link->url = $url.'?'.http_build_query($get);
		    	$pagination->pages[] = $link;
		    }

		    if ($page < $max_pages) {
		    	$get = $_GET;
		    	$get['page'] = $page + 1;
		    	$link = new \stdclass();
		    	$link->title = '&raquo;';
		    	$link->url = $url.'?'.http_build_query($get);
		    	$pagination->next = $link;
		    }


		    return $pagination;

		});
		$twig->addFunction($function);
	



		$twig->addFilter($filter);
		$this->twig = $twig;
	}
	public function render($tpl,$vars) {
		return $this->twig->render($tpl,$vars);
	}
}