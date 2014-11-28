<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 28.11.14
 * Time: 13:34
 */

class AuthController extends BaseController{
    public function before(){
        $headers =  getallheaders ();

        if(!isset($headers["X-Digest"])){
            return false;
        }
        $digest = $headers["X-Digest"];
        $data = self::getRawPost();
        if(md5($data.UNITY_KEY) == $digest){
            return true;
        }else{
            Logger::instance()->write("EXPECTED this ".md5($data.UNITY_KEY) ." got this".$digest);
            return false;
        }
    }
    static function getRawPost()
    {
        return isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents("php://input");
    }
}