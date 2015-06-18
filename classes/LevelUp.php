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
                            <result></result>');
        $ids = array();
        $new_skillpoint= 0;
        $db = DBHolder::GetDB();
        $posttwit= false;
        $lvl = 0;
        foreach($ups as$element){
            if($element["class"]==-1){
                $sql = "SELECT * FROM `level_count` WHERE classed='0' AND lvl ='".$element["lvl"]."' ";
            }else{
                $sql = "SELECT * FROM `level_count` WHERE classed='1' AND lvl ='".$element["lvl"]."' ";
            }

            $sqldata =$db->fletch_assoc($db->query($sql));
            $ids[] = '"'.$element["class"]."_".$element["lvl"].'"';
            if($element["class"]==-1){

                $posttwit= true;
                $lvl=$element["lvl"];
                $new_skillpoint+=$sqldata[0]["skillpoint"];
            }
            $lvlup->xmlresult->addChild("lvlup",$element);
        }



        if($new_skillpoint>0){
            $sql = "INSERT INTO player_skill (`uid`,`skillpoint`) VALUES('".$uid."',".$new_skillpoint.")
                 ON DUPLICATE KEY UPDATE skillpoint  = skillpoint +".$new_skillpoint."  ;";
           ;
            $db->query($sql);
        }


        $sql = "SELECT * FROM `level_up` WHERE id IN ( ".implode(",",$ids).")";
        $sqldata =$db->fletch_assoc($db->query($sql));

        $lvlup->uid = $uid;
        foreach($sqldata as $element){
            $params  =explode(",",$element["params"]);
            call_user_func_array(array($lvlup, $element["funcname"]), $params);
        }
        header('Content-type: text/xml');
        StatisticController::returnSmallData($lvlup->xmlresult,$uid);
        echo   $lvlup->xmlresult->asXML();
     /* if($posttwit){
            $sql = ' SELECT * FROM `statistic` WHERE uid ="'.$uid.'"';

            $sqldata =$db->fletch_assoc($db->query($sql));
            TwitterApi::postLevel($sqldata[0]["NAME"],$lvl);
        }
*/

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
        $sql = "UPDATE `statistic` SET   open_sid ='".$itemId."' WHERE uid = '".$this->uid."'";
        $db->query($sql);
    }
    public function openEvent($itemId){
        $this->xmlresult->addChild("prize","");
        $domitems = dom_import_simplexml( $this->xmlresult->prize);
        $events = new ShopEvents();
        $notify = array();
        $events->openspecial($itemId,$notify);
        foreach($notify as $element){
            $notOne   = new SimpleXMLElement('<notify></notify>');
            $notOne->addChild("type",$element["type"]);
            foreach($element["params"] as $param){
                $notOne->addChild("param",$param);
            }
            foreach($element["ass_params"] as $key=> $param){
                $notOne->addChild( $key,$param);
            }
            $domone  = dom_import_simplexml($notOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }

    }
} 