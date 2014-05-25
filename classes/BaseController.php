<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 09.04.14
 * Time: 15:57
 */

class BaseController{

	 public static $aurh =  'https://oauth.vk.com/access_token?client_id=3925872&client_secret=NomkD4Dt42BP0X3TrLx4&v=5.21&grant_type=client_credentials';

    public static $api_id ="3925872";
    public static $secret_key="NomkD4Dt42BP0X3TrLx4";
    public static $unity_key = "";
    public static function getVKAUTH(){
        $token = json_decode(file_get_contents(self::$aurh),true);
        $token =$token["access_token"];
        return $token;
    }



}