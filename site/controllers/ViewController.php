<?php

namespace Controller;

use Lib\Config;

class ViewController extends DefaultController {
	public static function IndexAction($req,$res) {

		/*
		$parts = $req->params;
		array_unshift($parts, $req->method);
		array_unshift($parts, $req->controller);

		$path = join('/',$parts);
		$app = Config::get('application');
		$res->redirect(trim($req->original_base_url,'/').'/'.$app['path'].'/'.trim($path,'/'));
		*/
		
		$parts = $req->params;
		array_unshift($parts, $req->method);
		//array_unshift($parts, $req->controller);

		$path = join('/',$parts);


		$res->redirect(trim($req->original_base_url,'/').'/_files/view.php/'.trim($path,'/'));

		//$res->json($req->params);

		
	}
}