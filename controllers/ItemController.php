<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 21.05.14
 * Time: 15:58
 */

class ItemController extends BaseController{

    public function loaditemsnew(){
        $data =$_REQUEST;
        header('Content-type: text/xml');
        $db = DBHolder::GetDB();

        $sql = 'DELETE FROM `player_inventory` WHERE uid="'.$data["uid"].'" AND ((personal =0 AND  charge=0 AND time_end=0 ) OR (time_end <'.time().' AND time_end>0))';

        $db->query($sql);
        $sql = 'SELECT `player_inventory` . * , `game_item`.ingamekey,`game_item`.ingametype, `inventory_item_dictionary`.class, `inventory_item_dictionary`.type, `inventory_item_dictionary`.charge AS maxcharge,
        `inventory_item_dictionary`.shopicon,
         `inventory_item_dictionary`.description, `inventory_item_dictionary`.name, `inventory_item_dictionary`.model
                    FROM `player_inventory`
                    LEFT JOIN `inventory_item_dictionary` ON `player_inventory`.game_id = `inventory_item_dictionary`.game_id
                    LEFT JOIN `game_item` ON `player_inventory`.game_id = `game_item`.id WHERE uid="'.$data["uid"].'"';

        $playerInventory =$db->fletch_assoc($db->query($sql));


        $sql = 'SELECT itid FROM `player_opened_gameitem` WHERE uid="'.$data["uid"].'"';
        $openItem =$db->fletch_array_flat($db->query($sql));

        $sql = 'SELECT  `game_item`.id,`ingamekey`,`class`,`guiimage`,`defaultforclass`,`free`,`ingametype` FROM `game_item`INNER JOIN `game_item_to_class` ON game_item_to_class.id=game_item.id  WHERE free<>0'  ;

        $sqldata =$db->fletch_assoc($db->query($sql));
        $xmlitems = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items><inventory></inventory></items>');

        $domitems = dom_import_simplexml($xmlitems);
        function ininventory($id,$playerInventory){
           foreach($playerInventory as $element){
                if($element["game_id"]==$id){

                        return true;

                }
           }
            return false;
        }

        foreach($sqldata as $element){
            if($element["free"]==1||in_array($element["id"],$openItem)){
                switch($element["type"]){
                    case 0:
                        $wepOne   = new SimpleXMLElement('<weapon></weapon>');
                        $wepOne->addChild("gameClass",$element["class"]);
                        $wepOne->addChild("weaponId",$element["ingamekey"]);
                        $wepOne->addChild("id",$element["id"]);
                        $wepOne->addChild("free",$element["free"]);
                        $wepOne->addChild("ingametype",$element["ingametype"]);
                        $wepOne->addChild("textureGUIName",$element["guiimage"]);

                        $wepOne->addChild("default",$element["defaultforclass"]==1?"true":"false");

                        break;


                }

                $domone  = dom_import_simplexml($wepOne);
                $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                $domitems->appendChild($domone);
            }
        }
        $this->loadInventory($xmlitems,$playerInventory);


        $sql = 'SELECT * FROM `game_item` WHERE game_item.type = 1';

        $stims = array();
        $sqldata =$db->fletch_assoc($db->query($sql));
        foreach($sqldata as $element){

            $stims[$element["id"]]["name"] =$element["name"];

            $stims[$element["id"]]["textureGUIName"] = $element["guiimage"] ;
            $stims[$element["id"]]["buffId"] = $element["ingamekey"] ;
            $stims[$element["id"]]["ingametype"] = $element["ingametype"] ;
        }

        foreach($stims as $key=>$stim){

            $stimOne   = new SimpleXMLElement('<stim></stim>');

            $stimOne->addChild("textureGUIName",$stim["textureGUIName"]);
            $stimOne->addChild("name",$stim["name"]);
            $stimOne->addChild("group",$stim["ingametype"]);
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
            $marked = json_decode(stripslashes($sqldata[0]["mark_game_id"]),true);

            foreach($marked as $element){
                $xmlitems->addChild("marked",$element);
            }
        }


        echo $xmlitems->asXml();
    }
    public function markitem(){
        $data =$_REQUEST;
        //print_r($data);
        $sql = ' SELECT * FROM `player_setting` WHERE uid ="'.$data["uid"].'"';
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(isset($sqldata[0])){
             $marked = json_decode(stripslashes($sqldata[0]["mark_game_id"]),true);
        if(!is_array($marked)){
            $marked=array();
        }
        }else{
            $marked=array();
        }
        $marked[]= $data["game_id"];
        $marked = addslashes(json_encode($marked));
        if(!isset($sqldata[0])){
            $sql = ' INSERT INTO `player_setting`  (`uid`,`mark_game_id`) VALUES ("'.$data["uid"].'","'.$marked.'")';
        }else{
            $sql = ' UPDATE `player_setting` SET `mark_game_id` = "'.$marked.'" WHERE uid ="'.$data["uid"].'"';
        }
        $db->query($sql);
    }

    public function unmarkitem(){
        $data =$_REQUEST;
        $sql = ' SELECT * FROM `player_setting` WHERE uid ="'.$data["uid"].'"';
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(isset($sqldata[0])){
            $marked = json_decode(stripslashes($sqldata[0]["mark_game_id"]),true);
            if(!is_array($marked)){
                $marked=array();
            }
        }else{
            $marked=array();
        }
        if(($key = array_search($data['game_id'], $marked)) !== false) {
            unset($marked[$key]);
            $marked = addslashes(json_encode($marked));

            $sql = ' UPDATE `player_setting` SET `mark_game_id` = "'.$marked.'" WHERE uid ="'.$data["uid"].'"';

            $db->query($sql);


        }
    }
    public function saveitemnew(){
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
        if(isset($data["usedInvItem"])){
            $sql = "UPDATE `player_inventory` SET `charge` =\n"
                . "CASE \n"
                . "WHEN`personal` = 1 THEN `charge`-1\n"
                . "ELSE `charge`\n"
                . "END,\n"
                . "`time_end` = \n"
                . "CASE\n"
                . "WHEN (`time_end`=-1 AND `personal`=0) THEN ".time()." + 86400*(SELECT `time_end` FROM `inventory_item_dictionary` WHERE `inventory_item_dictionary`.`game_id` = `player_inventory`.`game_id`)\n"
                . "ELSE `time_end`\n"
                . "END   WHERE id IN (".implode(",",$data["usedInvItem"]).")";
            $db->query($sql);
        }


    }
    public function loadshopnew(){
        $data =$_REQUEST;
        header('Content-type: text/xml');
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM level_player WHERE class=-1 AND uid = '".$data['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $lvl = 0;
        if(isset($sqldata[0])){
            $lvl  = $sqldata[0]["lvl"];
        }else{
            $lvl =0;
        }
        $sql = 'SELECT * FROM `shop_items` ';
        $shops = $db->fletch_assoc($db->query($sql));
        $shopsSort = array();
        foreach($shops as $element){
             $shopsSort[$element["inv_id"]][$element["pricetype"]] = $element;

        }

        $sql = 'SELECT * FROM `inventory_item_dictionary` WHERE `lvl_cap` =0 OR `lvl_cap` < '.$lvl;
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
                $slotOne->addChild("kp_price",$shopsSort[$element["id"]]["KP"]["price"]);
                $slotOne->addChild("kp_shop_id",$shopsSort[$element["id"]]["KP"]["id"]);
            }else{
                $slotOne->addChild("kp_price",0);
            }
            if(isset(  $shopsSort[$element["id"]]["GITP"])){
                $slotOne->addChild("gitp_price",$shopsSort[$element["id"]]["GITP"]["price"]);
                $slotOne->addChild("gitp_shop_id",$shopsSort[$element["id"]]["GITP"]["id"]);
            }else{
                $slotOne->addChild("gitp_price",0);
            }
            $domone  = dom_import_simplexml($slotOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }

        echo $xmlitems->asXml();
    }
    public function loadInventory(&$xml,$playerInventory){


        $dominv = dom_import_simplexml($xml->inventory);
        foreach($playerInventory as $element){


            $itemOne   = new SimpleXMLElement('<item></item>');
            $itemOne->addChild("id",$element['id']);
            $itemOne->addChild("game_id",$element['game_id']);
            $itemOne->addChild("personal",$element['personal']==1?"true":"false");
            $itemOne->addChild("charge",$element['charge']);

            $itemOne->addChild("time_end",$element['time_end']<=0?"":date("c",$element['time_end']));
            $itemOne->addChild("modslot",$element['modslot']);
            $itemOne->addChild("mods",$element['mods']);
            $itemOne->addChild("ingamekey",$element['ingamekey']."");
            $itemOne->addChild("ingametype",$element['ingametype']."");
            $itemOne->addChild("class",$element['class']);
            $itemOne->addChild("type",$element['type']);
            $itemOne->addChild("maxcharge",$element['maxcharge']);
            $itemOne->addChild("shopicon",$element['shopicon']);
            $itemOne->addChild("description",$element['description']);
            $itemOne->addChild("name",$element['name']);
            $itemOne->addChild("model",$element['model']);
            $domone  = dom_import_simplexml($itemOne);

            $domone  = $dominv->ownerDocument->importNode($domone, TRUE);
            $dominv->appendChild($domone);



        }

    }
}