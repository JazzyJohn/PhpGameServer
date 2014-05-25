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
       $db = DBHolder::GetDB();
       $sql = 'DELETE FROM `player_opened_gameitem` WHERE uid="'.$data["uid"].'" AND   timeend!=0 AND timeend <'.time().'';
       $db->query($sql);
       $sql = 'SELECT `ingamekey`,`class` FROM `game_item`INNER JOIN `game_item_to_class` ON game_item_to_class.id=game_item.id WHERE game_item.id IN( SELECT itid FROM `player_opened_gameitem` WHERE uid="'.$data["uid"].'") OR game_item.free=1';

        $sqldata =$db->fletch_assoc($db->query($sql));
        $xmlitems = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items></items>');

        $domitems = dom_import_simplexml($xmlitems);
        foreach($sqldata as $element){
            switch($element["type"]){
                case 0:
                    $wepOne   = new SimpleXMLElement('<weapon></weapon>');
                    $wepOne->addChild("gameClass",$element["class"]);
                    $wepOne->addChild("weaponId",$element["ingamekey"]);
                    break;

            }

            $domone  = dom_import_simplexml($wepOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }

        echo $xmlitems->asXml();
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