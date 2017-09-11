<?php
if (in_array($_SERVER['SERVER_NAME'],array('localhost','prod.studiogt.com.br','192.168.25.113'))) {
	$host = "plesk21.openweb.com.br";
} else {
	$host = "localhost";
}
return array(
		'host' => $host,
		'dbname' => 'modelo',
		'user' => 'modelo',
		'pass' => 'sgt357'
	);