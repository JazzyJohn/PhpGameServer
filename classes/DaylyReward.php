<?php
/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 18.03.15
 * Time: 14:20
 */


class DaylyReward{

    public static $array_date = array(
        array(
            "type"=>"HOLIDAY",
            "date"=>"07.05.2015",
            "dateEnd"=>"10.05.2015",
            "text"=> "Комрад С Днем Победы!\n\n В Честь этого великого праздника, командования снабдило тебя особым вооружением.\nС 9 Мая!",
            "reward"=>array(

                array(
                    "type"=>"ITEM",
                    "item"=>75,
                    "itemtype"=>"FOR_KP"
                )
            )
        )/*,
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
    private function isInPeriod($date1,$date2,$actualDate){
       $time_start =  strtotime("00:00:00 ".$date1);
       $time_end =  strtotime("23:59:59 ".$date2);
        $time =  strtotime("12:00:00 ".$actualDate);
       return $time_start<$time&&$time<$time_end;
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
                    if((!$this->isInPeriod($element["date"],$element["dateEnd"],$lastvisit)||$this->user==false)&&$this->isInPeriod($element["date"],$element["dateEnd"],$today)){
                           $item  = $this->resolve_reward($element['reward']);
                            if($item==null){
                                $notify[] = array("type"=>"HOLIDAY","ass_params"=>array("text"=>$element["text"]));
                            }else{
                                $notify[] = array("type"=>"HOLIDAY","ass_params"=>array("text"=>$element["text"],"item"=>$item));
                            }

                    }
                    break;
                case  'DAYENTER':
                    if($this->yesturday()&&$this->user["dayEnter"]+1==$element["dayEnter"]){

                        $notify[] = array("type"=>"DAYREWARD","params"=>$element["notify"]);
                        $this->resolve_reward($element['reward']);
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
        $item = null;
        foreach($reward as $element){
            switch($element["type"]){
                case 'MONEY':
                    $sql =  "UPDATE statistic SET `cash` = `cash` + ".$element["cash"]." , `gold` = `gold` + ".$element["gold"]." WHERE uid = '". $this->uid."'";
                    $db->query($sql);
                    break;
                case 'ITEM':
                    if($this->user==false){
                        $sql = ' INSERT INTO `game_items_players`  (`uid`,`item_id`,`buytype`) VALUES ("'.$this->uid.'","'.NEWBIE_PISTOL.'","FOR_KP")';
                        $db->query($sql);

                    }
                    $sql = ' INSERT INTO `game_items_players`  (`uid`,`item_id`,`buytype`) VALUES ("'.$this->uid.'","'.$element["item"].'","'.$element["itemtype"].'")';
                    $item=$element["item"];
                    $db->query($sql);
                    break;
            }
        }
        return $item;
    }




}