<?php
/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 18.09.14
 * Time: 19:40
 */

class ItemControllerOld extends BaseController{
    public function loaditems(){
        $data =$_REQUEST;
        header('Content-type: text/xml');
        $db = DBHolder::GetDB();
        $sql = 'DELETE FROM `player_opened_gameitem` WHERE uid="'.$data["uid"].'" AND   timeend!=-1 AND timeend <'.time().'';
        $db->query($sql);
        $sql = 'SELECT `ingamekey`,`class`,`guiimage`,`defaultforclass` FROM `game_item`INNER JOIN `game_item_to_class` ON game_item_to_class.id=game_item.id WHERE game_item.id IN( SELECT itid FROM `player_opened_gameitem` WHERE uid="'.$data["uid"].'") OR game_item.free=1';

        $sqldata =$db->fletch_assoc($db->query($sql));
        $xmlitems = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items></items>');

        $domitems = dom_import_simplexml($xmlitems);

        foreach($sqldata as $element){
            switch($element["type"]){
                case 0:
                    $wepOne   = new SimpleXMLElement('<weapon></weapon>');
                    $wepOne->addChild("gameClass",$element["class"]);
                    $wepOne->addChild("weaponId",$element["ingamekey"]);
                    $wepOne->addChild("textureGUIName",$element["guiimage"]);

                    $wepOne->addChild("default",$element["defaultforclass"]==1?"true":"false");

                    break;


            }

            $domone  = dom_import_simplexml($wepOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }

        $sql = 'SELECT * FROM `player_game_items_amount` WHERE uid="'.$data["uid"].'"';
        $amount =array();
        $sqldata =$db->fletch_assoc($db->query($sql));
        foreach($sqldata as $element){
            $amount[$element["id"]]  = $element['amount'];

        }
        $sql = 'SELECT * FROM `game_item` WHERE game_item.type = 1';

        $stims = array();
        $sqldata =$db->fletch_assoc($db->query($sql));
        foreach($sqldata as $element){

            $stims[$element["id"]]["name"] =$element["name"];
            $stims[$element["id"]]["normalPrice"] =intval($element["cash_cost"]);
            $stims[$element["id"]]["goldPrice"] =intval($element["gold_cost"]);
            $stims[$element["id"]]["amount"] =isset( $amount[$element["id"]] )?$amount[$element["id"]]:0;
            $stims[$element["id"]]["textureGUIName"] = $element["guiimage"] ;
            $stims[$element["id"]]["buffId"] = $element["ingamekey"] ;

        }

        foreach($stims as $key=>$stim){

            $stimOne   = new SimpleXMLElement('<stim></stim>');
            $stimOne->addChild("amount",$stim["amount"]);
            $stimOne->addChild("textureGUIName",$stim["textureGUIName"]);
            $stimOne->addChild("name",$stim["name"]);
            $stimOne->addChild("normalPrice",$stim["normalPrice"]);
            $stimOne->addChild("goldPrice",$stim["goldPrice"]);
            $stimOne->addChild("mysqlId",$key);
            $stimOne->addChild("buffId",$key);
            $domone  = dom_import_simplexml($stimOne);


            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);



        }
        $sql =  "SELECT * FROM `buff` ";
        $sqldata =$db->fletch_assoc($db->query($sql));
        foreach($sqldata as $effect){
            $effectOne   = new SimpleXMLElement('<buff></buff>');
            $effectOne->addChild("characteristic",$effect["property"]);
            $effectOne->addChild("type",$effect["type"]);

            $effectOne->addChild("effecttype",$effect["effecttype"]);
            $effectOne->addChild("value",$effect["value"]);
            $effectOne->addChild("buffId",$effect["id"]);
            $domone  = dom_import_simplexml($effectOne);


            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);

        }
        $sql = ' SELECT * FROM `player_setting` WHERE uid ="'.$data["uid"].'"';

        $sqldata =$db->fletch_assoc($db->query($sql));
        if(isset($sqldata[0])){
            $settings = json_decode(stripslashes($sqldata[0]["default_weapon"]),true);
            //print_r($settings);
            foreach($settings as $class){

                foreach($class as $element){
                    $wepOne   = new SimpleXMLElement('<default></default>');
                    $wepOne->addChild("gameClass",$element["class"]);
                    $wepOne->addChild("weaponId",$element["weaponId"]);
                    $domone  = dom_import_simplexml($wepOne);
                    $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                    $domitems->appendChild($domone);
                }
            }


        }


        echo $xmlitems->asXml();
    }
    public function saveitem(){
        $data =$_REQUEST;
        //print_r($data);
        $sql = ' SELECT * FROM `player_setting` WHERE uid ="'.$data["uid"].'"';
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(isset($sqldata[0])){
            $iscreate = false;
            $settings = json_decode($sqldata[0]["default_weapon"],true);
        }else{
            $iscreate = true;
            $settings = array();
        }
        if( isset($settings[$data["class"]])){
            unset($settings[$data["class"]]) ;
            $settings[$data["class"]]= array();
        }
        foreach($data["default"] as $element){
            if($element==-1){
                continue;
            }
            $settings[$data["class"]][] = array("class"=>$data["class"],"weaponId"=>$element);
        }
        $data["robotclass"]+=5;
        if( isset($settings[$data["robotclass"]])){
            unset($settings[$data["robotclass"]]) ;
            $settings[$data["robotclass"]]= array();
        }
        foreach($data["defaultrobot"] as $element){
            if($element==-1){
                continue;
            }
            $settings[$data["robotclass"]][] = array("class"=>$data["robotclass"],"weaponId"=>$element);
        }
        //	print_r($data);
        //print_r($settings);
        $settings = addslashes(json_encode($settings));
        if($iscreate){
            $sql = ' INSERT INTO `player_setting`  (`uid`,`default_weapon`) VALUES ("'.$data["uid"].'","'.$settings.'")';
        }else{
            $sql = ' UPDATE `player_setting` SET `default_weapon` = "'.$settings.'" WHERE uid ="'.$data["uid"].'"';
        }
        $db->query($sql);

    }
    public function loadshop(){

        $data =$_REQUEST;
        $db = DBHolder::GetDB();
        if(isset($data["main"])&&$data["main"]==true){
            $sql = 'SELECT * FROM `game_item` WHERE free=0 AND special_offer =1';
            $items =$db->fletch_assoc($db->query($sql));
            $sql = 'SELECT * FROM `game_item_to_class` WHERE id IN (SELECT id FROM `game_item` WHERE free=0 AND special_offer =1)';
            $items_class = $db->fletch_assoc($db->query($sql));
        }else{
            $sql = 'SELECT * FROM `game_item` WHERE free=0 AND type ='.$data["type"].'';
            $items =$db->fletch_assoc($db->query($sql));
            $sql = 'SELECT * FROM `game_item_to_class` WHERE id IN (SELECT id FROM `game_item` WHERE free=0 AND type ='.$data["type"].')';
            $items_class = $db->fletch_assoc($db->query($sql));

        }
        $indexes = array();
        foreach($items_class as $element){
            $indexes[$element["id"]][] = $element['class'];

        }

        $xmlitems = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items></items>');

        $domitems = dom_import_simplexml($xmlitems);
        foreach($items as $element){
            switch($element["type"]){
                case 0:
                    $wepOne   = new SimpleXMLElement('<item></item>');
                    $wepOne->addChild("id",$element["id"]);
                    $wepOne->addChild("name",$element["name"]);
                    $wepOne->addChild("cashcost",$element["cash_cost"]);
                    $wepOne->addChild("goldcost",$element["gold_cost"]);
                    $wepOne->addChild("imageurl",$element["imageurl"]);
                    $wepOne->addChild("description",$element["description"]);
                    if(isset($indexes[$element["id"]])){
                        foreach($indexes[$element["id"]] as $class){
                            $wepOne->addChild("class",$class);
                        }
                    }
                    break;

            }
            $domone  = dom_import_simplexml($wepOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }

        echo $xmlitems->asXml();
    }

}