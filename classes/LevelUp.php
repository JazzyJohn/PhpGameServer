<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 26.05.14
 * Time: 14:01
 */

class LevelUp {
    private $uid;
    static public function  init($ups, $uid){
        $ids = array();
        $new_skillpoint= 0;
        foreach($ups as$element){
            $ids[] = '"'.$element["class"]."_".$element["lvl"].'"';
            if($element["class"]!=-1){
                $new_skillpoint++;
            }
        }

        $sql = "SELECT * FROM `level_up` WHERE id IN ( ".implode(",",$ids).")";

        if($new_skillpoint>0){
            $sql = "INSERT INTO player_skill (`uid`,`skillpoint`) VALUES('".$uid."',".$new_skillpoint.")
                 ON DUPLICATE KEY UPDATE skillpoint  = skillpoint +".$new_skillpoint."  ;";
            $db = DBHolder::GetDB();
            $db->query($sql);
        }

        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $lvlup = new LevelUp();
        $lvlup->uid = $uid;
        foreach($sqldata as $element){
            $params  =explode(",",$element["params"]);
            call_user_func_array(array($lvlup, $element["funcname"]), $params);
        }


    }

    public function openItem($itemId){
        $db = DBHolder::GetDB();
        $sql = "INSERT INTO `player_opened_gameitem`   (uid,itid) VALUES ('".$this->uid."','".$itemId."')";
        $db->query($sql);
    }
} 