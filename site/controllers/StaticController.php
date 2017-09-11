<?php

namespace Controller;

use Lib\Config;

class StaticController extends DefaultController {
	public static function IndexAction($req,$res) {

		$parts = $req->params;
		array_unshift($parts, $req->method);
		array_unshift($parts, $req->controller);

		$path = join('/',$parts);
		$app = Config::get('application');
		$res->redirect(trim($req->original_base_url,'/').'/'.$app['path'].'/'.trim($path,'/'));

		
	}
}