<?php


return array(	
	'db' => require('db.config.php'),
	'languages' => array(
		'br'
	),
	'app' => (object)array(
		'name' => "Modelo Site",
		'debug' => false,
		'debug_email' => 'fabio@studiogt.com.br'
	),
	'application' => array(
		'path' => 'site',
		'data' => array(
			'week' => array("Domingo","Segunda-feira","Terça-feira","Quarta-feira","Quinta-feira","Sexta-feira","Sábado"),
			'month' => array("","Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"),
			'monthr' => array("","Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez")
		)
	),
	'acl' => array(
		'rules' => array(
			'/.*/' => 'public',
			/*'/^cart\/.+/' => 'authenticated',
			'/^cart\/login/' => 'public',
			'/^client/' => 'authenticated',
			'/^client\/login/' => 'public',
			'/^client\/post\-login/' => 'public',
			'/^order/' => 'authenticated',
			'/^order\/status/' => 'public'
			*/
		),
		//'redirect' => 'client/login'
		
	),
	'cache' => array(
		'rules' => array(
			'/.*/' => 0,
			'/^cart/' => 0,
			'/^client/' => 0,
			'/^order/' => 0
		),
		'config' => array(
			'storage' => 'files',
			'path' => __DIR__.'/site/_cache/'
		)
	)

);