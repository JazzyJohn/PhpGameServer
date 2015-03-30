<?php
/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 18.03.15
 * Time: 14:20
 */


class DaylyReward{

    public static $array_date = array(
       /* array(
            "type"=>"HOLIDAY",
            "date"=>"09.05.2015",
            "reward"=>array(
                array(
                    "type"=>"MONEY",
                    "cash"=>1000,
                    "gold"=>1
                ),
                array(
                    "type"=>"ITEM",
                    "item"=>33,
                    "itemtype"=>"FOR_KP"
                )
            )
        ),
        array(
            "type"=>"DAYENTER",
            "dayEnter"=>2,
            "reward"=>array(
                array(
                    "type"=>"MONEY",
                    "cash"=>100,
                    "gold"=>0
                )
            )
        ),
        array(
            "type"=>"DAYENTER",
            "dayEnter"=>3,
            "reward"=>array(
                array(
                    "type"=>"MONEY",
                    "cash"=>100,
                    "gold"=>0
                )
            )
        )*/




    );


    public  function __construct($user,$uid) {
        $this->user =$user;
        $this->uid =$uid;
    }

    public function resolved(){
        $today = date("d.m.Y");

        if($this->user!=false){
            $lastvisit = date("d.m.Y",$this->user["lastEnter"]);
        }else{
            $lastvisit =date("d.m.Y");
        }
        $notify = array();
        foreach(self::$array_date as $element){
            switch($element["type"]){
                case "HOLIDAY":
                    if(($lastvisit!=$today||$this->user==false)&&$today==$element["date"]){
                           self::resolve_reward($element['reward']);
                            $notify[] = array("type"=>"dayReward","params"=>$element["notify"]);
                    }
                    break;
                case  'DAYENTER':
                    if($this->yesturday()&&$this->user["dayEnter"]+1==$element["dayEnter"]){

                        $notify[] = array("type"=>"dayReward","params"=>$element["notify"]);
                        self::resolve_reward($element['reward']);
                    }
                    break;

            }
        }
        if($this->user!=false){
            $yesterday  =date('d.m.Y',strtotime("-1 days"));
            $today = date("d.m.Y");
            if($yesterday  ===date('d.m.Y',$this->user["lastEnter"])){
                $sql =  "UPDATE statistic SET `dayEnter` = dayEnter+1 WHERE uid = '". $this->uid."'";
                $db = DBHolder::GetDB();
                $db->query($sql);
            }else{
                if(date('d.m.Y',$this->user["lastEnter"])!=$today){
                    $sql =  "UPDATE statistic SET `dayEnter` = 1 WHERE uid = '". $this->uid."'";
                    $db = DBHolder::GetDB();
                    $db->query($sql);
                }
            }
        }else{
            $sql =  "UPDATE statistic SET `dayEnter` = 1 WHERE uid = '". $this->uid."'";
            $db = DBHolder::GetDB();
            $db->query($sql);
        }
        return $notify;

    }
    private function yesturday(){
        if($this->user==false){
            return false;
        }
        $yesterday  =date('d.m.Y',strtotime("-1 days"));

        if($yesterday  ==date('d.m.Y',$this->user["lastEnter"])){
            return true;
        }else{

            return false;
        }

    }
    private function resolve_reward($reward){
        $db = DBHolder::GetDB();

        foreach($reward as $element){
            switch($element["type"]){
                case 'MONEY':
                    $sql =  "UPDATE statistic SET `cash` = `cash` + ".$element["cash"]." , `gold` = `gold` + ".$element["gold"]." WHERE uid = '". $this->uid."'";
                    $db->query($sql);
                    break;
                case 'ITEM':
                    $sql = ' INSERT INTO `game_items_players`  (`uid`,`item_id`,`buytype`) VALUES ("'.$this->user['uid'].'","'.$element["item"].'","'.$element["itemtype"].'")';
                    $db->query($sql);
                    break;
            }
        }
    }




}