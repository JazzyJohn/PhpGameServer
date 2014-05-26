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

        foreach($ups as$element){
            $ids[] = '"'.$element["class"]."_".$element["lvl"].'"';

        }

        $sql = "SELECT * FROM `level_up` WHERE id IN ( ".implode(",",$ids).")";

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
        $sql = "INSERT INTO `player_opened_gameitem`   (uid,timeend,itid) VALUES ('".$this->uid."','0','".$itemId."')";
        $db->query($sql);
    }
} 