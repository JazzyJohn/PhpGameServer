<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 09.04.14
 * Time: 15:57
 */

class BaseController{

	 public static $aurh = AUTH_PATH;

    public static $api_id =API_ID;
    public static $secret_key=SECRET_KEY;
    public static $unity_key =UNITY_KEY;
    public static function getVKAUTH(){
        $token = json_decode(file_get_contents(self::$aurh),true);
        $token =$token["access_token"];
        return $token;
    }

    public function before(){
        session_start();
        Logger::instance()->write("SESSION" .print_r($_SESSION,true));
        if(isset($_SESSION["uid"])){
            Logger::instance()->write("SESSION uid ".$_SESSION["uid"]);
            $_REQUEST["uid"]= $_SESSION["uid"];
        }else{

            if(isset($_REQUEST["uid"])&&is_numeric($_REQUEST["uid"])){
                Logger::instance()->write("FROM VK WITHOUT SESSION");
                die("FROM VK WITHOUT SESSION");
            }
        }
        return true;
    }







}