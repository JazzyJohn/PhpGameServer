<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 21.05.14
 * Time: 15:58
 */

class ItemController extends BaseController{

    public function loaditems(){
        $data =$_REQUEST;
        header('Content-type: text/xml');
        $db = DBHolder::GetDB();
        $sql = 'DELETE FROM `player_inventory` WHERE uid="'.$data["uid"].'" AND personal =0 ( charge==0 OR time_end <'.time().')';
        $db->query($sql);
        $sql = 'SELECT * FROM `player_inventory` WHERE uid="'.$data["uid"].'"';

        $playerInventory =$db->fletch_assoc($db->query($sql));


        $sql = 'SELECT itid FROM `player_opened_gameitem` WHERE uid="'.$data["uid"].'"';
        $openItem =$db->fletch_array_flat($db->query($sql));

        $sql = 'SELECT `ingamekey`,`class`,`guiimage`,`defaultforclass` FROM `game_item`INNER JOIN `game_item_to_class` ON game_item_to_class.id=game_item.id';

        $sqldata =$db->fletch_assoc($db->query($sql));
        $xmlitems = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items><inventory></inventory></items>');

        $domitems = dom_import_simplexml($xmlitems);
        function ininventory($id) use ($playerInventory){
           foreach($playerInventory as $element){
                if($element["game_id"]==$id){
                    if($element["personal"]==1){
                          return $element["charge"]>0;
                    } else{
                        return true;
                    }
                }
           }
            return false;
        }
        foreach($sqldata as $element){
            if(in_array($element["id"],$openItem)||ininventory($element["id"])){
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
        }

        $sql = 'SELECT * FROM `player_game_items_amount` WHERE uid="'.$data["uid"].'"';
        $amount =array();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $dominv = dom_import_simplexml($xmlitems->inventory);
        foreach($playerInventory as $element){
            $amount[$element["id"]]  = $element['charge'];
            $itemOne   = new SimpleXMLElement('<item></item>');
            $itemOne->addChild("game_id",$element['game_id']);
            $itemOne->addChild("personal",$element['personal']);
            $itemOne->addChild("charge",$element['charge']);
            $itemOne->addChild("time_end",$element['time_end']);
            $itemOne->addChild("modslot",$element['modslot']);
            $itemOne->addChild("mods",$element['mods']);
            $domone  = dom_import_simplexml($itemOne);

            $domone  = $dominv->ownerDocument->importNode($domone, TRUE);
            $dominv->appendChild($domone);



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


        $db = DBHolder::GetDB();
        $sql = 'SELECT * FROM `shop_items` ';
        $shops = $db->fletch_assoc($db->query($sql));
        $shopsSort = array();
        foreach($shops as $element){
             $shopsSort[$element["inv_id"]][$element["pricetype"]] = $element;

        }
        $sql = 'SELECT * FROM `inventory_item_dictionary` ';
        $invdictionary = $db->fletch_assoc($db->query($sql));
        $xmlitems = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items></items>');

        $domitems = dom_import_simplexml($xmlitems);
        foreach($invdictionary as $element){
            $slotOne   = new SimpleXMLElement('<slot></slot>');
            $slotOne->addChild("game_id",$element["game_id"]);
            $slotOne->addChild("class",$element["class"]);
            $slotOne->addChild("type",$element["type"]);
            $slotOne->addChild("charge",$element["charge"]);
            $slotOne->addChild("shopicon",$element["shopicon"]);
            $slotOne->addChild("description",$element["description"]);
            $slotOne->addChild("name",$element["name"]);
            $slotOne->addChild("model",$element["model"]);
            if(isset(  $shopsSort[$element["id"]]["KP"])){
                $slotOne->addChild("kpprice",$shopsSort[$element["id"]]["KP"]);
            }else{
                $slotOne->addChild("kpprice",0);
            }
            if(isset(  $shopsSort[$element["id"]]["GITP"])){
                $slotOne->addChild("gitpprice",$shopsSort[$element["id"]]["GITP"]);
            }else{
                $slotOne->addChild("gitpprice",0);
            }
            $domone  = dom_import_simplexml($slotOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }

        echo $xmlitems->asXml();
    }

}