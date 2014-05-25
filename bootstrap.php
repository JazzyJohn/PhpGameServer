<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 09.04.14
 * Time: 15:14
 */ 
function spl_autoload_register_function($class) {
    static $map;
    if (!$map) {
        $map = include dirname(__FILE__) . '/autoload_classmap.php';
    }

    if (!isset($map[$class])) {
        return false;
    }
    return include $map[$class];
}

spl_autoload_register('spl_autoload_register_function');


