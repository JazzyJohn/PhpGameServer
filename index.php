<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 09.04.14
 * Time: 15:19
 */

require_once("bootstrap.php");

require_once("route.php");

require_once("conf/conf.php");

$uri = $_SERVER["REQUEST_URI"];

$_REQUEST = DBHolder::secure_request($_REQUEST);
if(file_exists($uri)){
    require_once($uri);
}else{
    $instance = Router::instance();

    $instance->run($uri);


 }
