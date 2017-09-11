<?php



return function($req,$res) {
	$page = (int)$req->Get('page',0);
	$rows = (int)$req->Get('rows',0);
	
	if ($page < 1) $page = 1;
	if ($rows < 1) $rows = 1;
	$offset = ($page - 1) * $rows;
	
	$req->pagination = new stdclass();
	$req->pagination->page = $page;
	$req->pagination->rows = $rows;
	
};