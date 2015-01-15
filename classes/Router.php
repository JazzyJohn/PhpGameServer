<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 09.04.14
 * Time: 15:38
 */

class Router{
    //сиглетон
    private static $sigleton= null;

    public static $isdebug=false;
    public static function instance(){
        if(self::$sigleton==null){

            self::$sigleton= new Router();
        }
        return self::$sigleton;
    }
    public $map = array();
    public static function addRoute($url,$controller){
        $route =self::instance();
        $route->map[$url] = $controller;

    }
    public function run($url){
		Logger::instance()->write($url);
        $url = explode("?",$url);
        $url = $url[0];
        //echo $url;
        if(isset($this->map[$url])){
            $classname = $this->map[$url];
            $controller = new $classname();
        }else{
            echo $url." 404";
            return;
        }
        
        Logger::instance()->write($classname);
        Logger::instance()->write(print_r($_REQUEST,true));



        if(preg_match("/editor\d/i",$_REQUEST["uid"])){
           self::$isdebug = true;
        }


        $func = get_class_methods($controller);
        if(in_array("before",$func)){
            if(!$controller->before()){
                Logger::instance()->write("Bad Before");
                return;
            }
        }
		$url =explode("/",$url);$url=$url[count($url)-1];

        if(in_array($url,$func)){

            $controller->{$url}();
            return;
        }
        if(in_array("index",$func)){
            $controller->index();
            return;
        }
        echo "404";

    }



}