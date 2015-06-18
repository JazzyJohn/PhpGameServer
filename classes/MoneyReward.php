<?php
/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 31.03.15
 * Time: 15:12
 */

class MoneyReward{

    public static $array_date = array(
        array(
            "set"=>"total",
            "count"=>"1",
            "reward"=>array(
                array(
                    "type"=>"ITEM",
                    "item"=>67,
                    "itemtype"=>"FOR_KP"
                )
            )
        ),
    );

    public  function __construct($user) {
        $this->user =$user;

    }


    public function userBuy(){
        $sql = "SELECT * FROM jew_events WHERE uid = '". $this->user["UID"]."'";
        $db = DBHolder::GetDB();
        $data =$db->fletch_assoc($db->query($sql));
        if(isset($data[0]["buys"])&&$data[0]["buys"]!=""){
            $this->answer = json_decode($data[0]["buys"],true);
        }else{
            $this->answer =array();
        }

        $this->answer[$this->user["open_sid"]]["buy"]++;
        $this->answer["total"]["buy"]++;
        $this->resolve();
        $sql =  "INSERT INTO jew_events (`uid`,`buys`) VALUES ('".$this->user["UID"]."','".json_encode( $this->answer)."')  ON DUPLICATE KEY UPDATE `buys` ='".json_encode( $this->answer)."' ";
        $db->query($sql);

    }

    private function resolve(){

        foreach(self::$array_date as $element){


            if( $this->answer[$element["set"]]["buy"]==$element["count"]){
                $this->resolve_reward($element["reward"]);
            }
        }
    }
    private function resolve_reward($reward){
        $db = DBHolder::GetDB();
        Logger::instance()->write(print_r($reward,true));
        foreach($reward as $element){
            switch($element["type"]){
                case 'MONEY':
                    $sql =  "UPDATE statistic SET `cash` = `cash` + ".$element["cash"]." , `gold` = `gold` + ".$element["gold"]." WHERE uid = '". $this->user["UID"]."'";
                    $db->query($sql);
                    break;
                case 'ITEM':
                    $sql = ' INSERT INTO `game_items_players`  (`uid`,`item_id`,`buytype`) VALUES ("'.$this->user["UID"].'","'.$element["item"].'","'.$element["itemtype"].'")';
                    $db->query($sql);

                    $sql = ' INSERT INTO `asyncnotifiers`  (`uid`,`type`) VALUES ("'.$this->user["UID"].'","RELOAD_ITEMS")';
                    $db->query($sql);
                    break;
            }
        }
    }

    public static function isDoneSocialReward($uid){
        if( isset( $_SESSION["lastRewardDate"])){
            $lastRewardDate= $_SESSION["lastRewardDate"];
        }else{
            $sql = "SELECT * FROM jew_events WHERE uid = '".$uid."'";
            $db = DBHolder::GetDB();
            $data =$db->fletch_assoc($db->query($sql));
            $_SESSION["lastRewardDate"] = $data[0]["lastdate"];
            $lastRewardDate = $data[0]["lastdate"];
        }
        if(date("d.m.Y")!=date("d.m.Y",$lastRewardDate)){
            return false;

        }else{
            return true;
        }
    }
    public static function setSocialRewardDone($uid)
    {
        $db = DBHolder::GetDB();
        $sql =  "INSERT INTO jew_events (`uid`,`lastdate`) VALUES ('".$uid."','".time()."')  ON DUPLICATE KEY UPDATE `lastdate` ='".time()."'  ";
        $db->query($sql);
        $_SESSION["lastRewardDate"]= time();
    }

}