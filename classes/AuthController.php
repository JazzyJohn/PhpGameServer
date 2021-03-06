<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 28.11.14
 * Time: 13:34
 */

class AuthController extends BaseController{
    public function before(){

        Logger::instance()->write("SESSION" .print_r($_SESSION,true));
        $headers =  getallheaders ();

        if(!isset($headers["X-Digest"])){
            Logger::instance()->write("NO DIGEST ");

            return true;
        }
        $digest = $headers["X-Digest"];
        $data = self::getRawPost();
        Logger::instance()->write("EXPECTED this ".md5($data.UNITY_KEY) ." got this".$digest);

        if(md5($data.UNITY_KEY) == $digest){
            return true;
        }else{
           // Logger::instance()->write("EXPECTED this ".md5($data.UNITY_KEY) ." got this".$digest);
            return true;
        }
    }
    static function getRawPost()
    {
        return isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents("php://input");
    }
}