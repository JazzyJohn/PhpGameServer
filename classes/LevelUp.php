<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 26.05.14
 * Time: 14:01
 */

class LevelUp {
    private $uid;
    private $xmlresult;
    static public function  init($ups, $uid){
        $lvlup = new LevelUp();
        $lvlup->xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        $ids = array();
        $new_skillpoint= 0;
        foreach($ups as$element){
            $ids[] = '"'.$element["class"]."_".$element["lvl"].'"';
            if($element["class"]==-1){
                $new_skillpoint++;
            }
            $lvlup->xmlresult->addChild("lvlup",$element);
        }



        if($new_skillpoint>0){
            $sql = "INSERT INTO player_skill (`uid`,`skillpoint`) VALUES('".$uid."',".$new_skillpoint.")
                 ON DUPLICATE KEY UPDATE skillpoint  = skillpoint +".$new_skillpoint."  ;";
            $db = DBHolder::GetDB();
            $db->query($sql);
        }

        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM `level_up` WHERE id IN ( ".implode(",",$ids).")";
        $sqldata =$db->fletch_assoc($db->query($sql));

        $lvlup->uid = $uid;
        foreach($sqldata as $element){
            $params  =explode(",",$element["params"]);
            call_user_func_array(array($lvlup, $element["funcname"]), $params);
        }
        header('Content-type: text/xml');
        echo   $lvlup->xmlresult->asXML();

    }

    public function openItem($itemId){
        $db = DBHolder::GetDB();
        $this->xmlresult->addChild("item_reward",$itemId);
        $sql = "INSERT INTO `player_inventory`   (uid,game_id,personal,time_end,modslot) VALUES ('".$this->uid."','".$itemId."','0','".(time()+60*60*HOUR_COUNT_FOR_LVL)."','0')";
          $db->query($sql);
    }
    public function openSet($itemId){
        $db = DBHolder::GetDB();
        $this->xmlresult->addChild("open_set",$itemId);
        $sql = "UPDATE `statistic` SET   open_set ='".$itemId."' WHERE uid = '".$this->uid."'";
        $db->query($sql);
    }
} 