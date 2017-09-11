<?php

include 'lib/Autoloader.php';
include 'vendor/autoload.php';

$loader = new Lib\Autoloader();
$loader->register();
$loader->addNamespace('Lib',__DIR__."/lib");


use Lib\Config;
use Lib\Router;
use Lib\CMS;

Config::load(__DIR__.'/admin.config.php');
$application = Config::get('application');


$loader->addNamespace('Controller',__DIR__.'/'.$application['path']."/controllers");

$db = Config::get('db');
$cms = new CMS($db['host'],$db['user'],$db['pass'],$db['dbname']);
Config::set('cms',$cms);

Router::init();