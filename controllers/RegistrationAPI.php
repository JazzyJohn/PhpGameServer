<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 17.09.14
 * Time: 11:32
 */

class RegistrationAPI extends BaseController{


    public function login(){
        header('Content-type: text/xml');
        $xmlprofile = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <login></login>');
        $data = $_REQUEST;
        $login =   strtolower($data["email"]);
        $password =  $data["password"];
        $sql = "SELECT * FROM  `authtable`  WHERE email='".$login."'";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(count($sqldata)==0){
            $xmlprofile->addChild("status","false");
            $xmlprofile->addChild("error","Не правильный  email или пароль");
            echo $xmlprofile->asXML();
            exit;
        }
       /* echo md5($password);
        echo "_____";
        echo $sqldata[0]["password"];*/
        if(md5($password)==$sqldata[0]["password"]){
            $xmlprofile->addChild("status","true");
            $xmlprofile->addChild("uid","INNER".$sqldata[0]["uid"]);
            $xmlprofile->addChild("nick",$sqldata[0]["nick"]);
            $xmlprofile->addChild("login",$sqldata[0]["email"]);
            echo $xmlprofile->asXML();
            exit;

        }else{
            $xmlprofile->addChild("status","false");
            $xmlprofile->addChild("error","Не правильный  email или пароль");
            echo $xmlprofile->asXML();
        }
    }
    public function registration(){
        header('Content-type: text/xml');
        $xmlprofile = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <registration></registration>');
        $data = $_REQUEST;
        $login = strtolower( $data["email"]);
        $password =  $data["password"];
        $sql = "SELECT * FROM  `authtable`  WHERE email='".$login."'";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(count($sqldata)>0){
            $xmlprofile->addChild("status","false");
            $xmlprofile->addChild("error","Email уже используеться");
            echo $xmlprofile->asXML();
            exit;
        }
        $sql = "INSERT INTO `authtable`(`password`,`email`,`nick`) VALUES ('".md5($password)."','".$login."','". $data["nick"]."');";
        $db->query($sql);

        $sql = "SELECT * FROM  `authtable`  WHERE email='".$login."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(count($sqldata)==0){
            $xmlprofile->addChild("status","false");
            $xmlprofile->addChild("error","Ошибка попробуйте позже");
            echo $xmlprofile->asXML();
            exit;
        }
        $xmlprofile->addChild("status","true");
        $xmlprofile->addChild("login",$sqldata[0]["email"]);
        $xmlprofile->addChild("uid","INNER".$sqldata[0]["uid"]);
        $xmlprofile->addChild("nick",$sqldata[0]["nick"]);
        echo $xmlprofile->asXML();

    }

}