<?php

namespace Controller;

use Lib\Config;
use Lib\View;

class DefaultController {
	
	
	public static function IndexAction($req,$res) {

		$tpl = $req->controller.'/'.$req->method.'.twig';
		$view_dir = dirname(__DIR__).'/views';
		if (!file_exists($view_dir.'/'.$tpl)) {
			$tpl = 'home/index.twig';
			$res->redirect('404');
		}

		$res->render($tpl,array(
				'req'=>$req,
				'equipe' => array('danylo','sergio','gabriel')
			)
		);
		


	}
	
}