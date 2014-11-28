<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 28.11.14
 * Time: 14:06
 */

class VKAuth{
    public static function AUTHME(){
       if($_REQUEST["api_id"]."_". $_REQUEST["viewer_id "]."_".SECRET_KEY==$_REQUEST["auth_key"]){
           return true;
       }else{
           return false;
       }

    }

}