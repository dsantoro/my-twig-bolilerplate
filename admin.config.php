<?php


return array(	
	'db' => require('db.config.php'),
	'languages' => array(
		'br'
	),
	'application' => array(
		'path' => 'admin'
	),
	'acl' => array(
		'rules' => array(
			'/.*/' => 'authenticated',
			'/^user\/login/' => 'public',
			'/^user\/post-login/' => 'public',
			'/^static/' => 'public'
		),
		'redirect' => 'user/login'
		
	),
	'cache' => array(
		'rules' => array(
			'/.*/' => 0
		),
		'config' => array(
			'storage' => 'files',
			'path' => __DIR__.'/admin/_cache/'
		)
	)

);