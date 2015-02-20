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


        $xmlitems = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items><inventory></inventory></items>');

        $domitems = dom_import_simplexml($xmlitems);
        $this->first_time($data["uid"]);
        $this->loadInventory($xmlitems,$data["uid"]);

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
            $stimOne->addChild("buffId",$stim["buffId"]);
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
                    $wepOne->addChild("set",$element["set"]);
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
    public function first_time($uid){
        $sql = ' SELECT COUNT(*) FROM `game_items_players` WHERE uid ="'.$uid.'"';
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        if($sqldata['COUNT(*)']==0){
            $sql = ' INSERT INTO `game_items_players`  (`uid`,`	item_id`,`buytype`) VALUES ("'.$uid.'","'.NEWBIE_PISTOL.'","FOR_KP")';
            $db->query($sql);
        }
    }

    public function chargedata(){
        $data =$_REQUEST;
        //print_r($data);
        $db = DBHolder::GetDB();
        $ids= array();
        foreach($data["charge"]as $key=>$val){
            $ids[] = $key;
        }
        $sql = "SELECT * FROM game_items_dictionary AS dic JOIN game_items_players AS fact ON dic.id = fact.item_id  WHERE dic.id  IN ('".$ids."') AND fact.uid =  '".$data["uid"]."'";
        $db->query($sql);
        $sqldata =$db->fletch_assoc($db->query($sql));
        foreach($sqldata as $element){
            $add = $data["charge"][$element["id"]];
            if($element["charge"]==$element["maxcharge"]){
                continue;
            }else if($element["charge"]+$add>=$element["maxcharge"]){
                $sql = "UPDATE `game_items_players` SET charge='".$element["maxcharge"]."' WHERE uid=  '".$data["uid"]."' AND item_id =  '".$element["id"]."'";
            }else{
                $sql = "UPDATE `game_items_players` SET charge=chrage + '".$add."' WHERE uid=  '".$data["uid"]."' AND item_id =  '".$element["id"]."'";
            }

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
        foreach($data["default"] as     $element){
            if($element==-1){
                continue;
            }
            $tar = explode("@set",$element);
            $settings[$data["class"]][] = array("class"=>$data["class"],"weaponId"=>$tar[0],"set"=>$tar[1]);
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

    public function loadInventory(&$xml,$uid){
        $db = DBHolder::GetDB();
        $sql = 'SELECT * FROM `game_items_price` ORDER BY `order` ASC, `type` ASC,`amount` ASC';
        $sqldata =$db->fletch_assoc($db->query($sql));
        $prices  = array();
        foreach($sqldata as $element){
            if($element["group"]==0){
                $prices[$element['inv_id']]["single"][]=$element;
            }else{
                $prices[$element['inv_id']]["group"][$element["group"]][]=$element;
            }

        }

        $sql = 'SELECT * FROM `game_items_dictionary` AS dic
                            LEFT JOIN `game_items_players` AS fact ON (dic.id = fact.item_id AND fact.uid= "'.$uid.'")
                            LEFT JOIN `game_items_sets` AS sets ON (dic.set_id =sets.sid)';
        $sqldata =$db->fletch_assoc($db->query($sql));

        $dominv = dom_import_simplexml($xml->inventory);
        foreach($sqldata as $element){


            $itemOne   = new SimpleXMLElement('<item></item>');
            $itemOne->addChild("id",$element['id']);
            $itemOne->addChild("class",$element['class']);
            $itemOne->addChild("type",$element['type']);
            $itemOne->addChild("ingame_type",$element['ingame_type']);
            $itemOne->addChild("ingame_mysqlid",$element['ingame_mysqlid']);
            $itemOne->addChild("maxcharge",$element['maxcharge']);
            $itemOne->addChild("maxmodslot",$element['maxmodslot']);
            $itemOne->addChild("shopicon",$element['shopicon']);
            $itemOne->addChild("description",$element['description']);
            $itemOne->addChild("name",$element['name']);
            $itemOne->addChild("model",$element['model']);
            $itemOne->addChild("set",$element['set_name']);
            $itemOne->addChild("repair_cost",$element['repair_cost']);
            self::parseChar($itemOne,$element['chars']);

            if($element['buytype']==null){
                $element['buytype'] ="NONE";
                $itemOne->addChild("time_end","");
                $itemOne->addChild("charge",0);
                $itemOne->addChild("modslot",0);
                $itemOne->addChild("mods","");
            }else{
                $itemOne->addChild("time_end",  $element['time_end']);
                $itemOne->addChild("charge",$element['charge']);
                $itemOne->addChild("modslot",$element['modslot']);
                $itemOne->addChild("mods",$element['mods']);
            }
            $domone  = dom_import_simplexml($itemOne);
            if(isset($prices[$element['id']]["single"])){
                foreach($prices[$element['id']]["single"] as $price ){
                    $priceXml   = new SimpleXMLElement('<price></price>');
                    $priceXml->addChild("type",$price["type"]);
                    $priceXml->addChild("id",$price["id"]);
                    $priceXml->addChild("amount",$price["amount"]);
                    $dprice  = dom_import_simplexml($priceXml);
                    $dprice  = $domone->ownerDocument->importNode($dprice, TRUE);
                    $domone->appendChild($dprice);
                }
            }
            if(isset($prices[$element['id']]["group"])){
                foreach($prices[$element['id']]["group"] as $group ){
                    $priceXml   = new SimpleXMLElement('<price></price>');
                    foreach($group as $price){
                        $priceXml->addChild("type",$price["type"]);
                        $priceXml->addChild("amount",$price["amount"]);
                        $priceXml->addChild("id",$price["id"]);
                    }
                    $dprice  = dom_import_simplexml($priceXml);
                    $dprice  = $domone->ownerDocument->importNode($dprice, TRUE);
                    $domone->appendChild($dprice);
                }
            }


            $domone  = $dominv->ownerDocument->importNode($domone, TRUE);
            $dominv->appendChild($domone);



        }

    }
    public static function parseChar($xmlitems, $str){

        $chars = explode("/",$str);
        foreach($chars as $char){
            $tar = explode(":",$char);

            $xmlitems->addChild($tar[0],$tar[1]);

        }
    }
}