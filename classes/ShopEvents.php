<?php

/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 28.04.15
 * Time: 14:33
 */

class ShopEvents{

    static $array = array(
        array(
            "days"=>array("Wednesday","Saturday"),
            "timeStart"=>"18",
            "timeLast"=>"2",
            "type"=>"ITEMTAKE",
            "itemId"=>37,
            "text"=>"Добрый день, камрад!\n\nТоварищ коммисар достал образец новейшего оружия.\nИспользуй его с умом!"
        ),
        array(
            "days"=>array("Wednesday","Saturday"),
            "timeStart"=>"20",
            "timeLast"=>"24",
            "type"=>"ITEMDISCOUNT",
            "amount"=>0.5,
            "itemId"=>37,
            "text"=>"Камрад!\n\nРодина благодарит тебя за успешные бои!\nЗа верную службу - ты можешь оставить оружие себе на 3 дня за полцены:"
        )
    );
    static $sesssionPrice = array(
        "newUser"=> array(
            array(

                "timeLast"=>"2",
                "type"=>"ITEMTAKE",
                "itemId"=>36,
                "text"=>"Добрый день, камрад!\n\nТоварищ коммисар достал образец новейшего оружия.\nИспользуй его с умом!"
            ),
            array(
                "startDelay"=>"2",
                "timeLast"=>"2",
                "type"=>"ITEMDISCOUNT",
                "amount"=>0.5,
                "itemId"=>36,
                "text"=>"Камрад!\n\nРодина благодарит тебя за успешные бои!\nЗа верную службу - ты можешь оставить оружие себе на 3 дня за полцены:"
            )
        ),
        "special"=>array(
            "lvl4"=>
        array(

            "timeLast"=>"4",
            "type"=>"KITDISCOUNT",
            "amount"=>0.5,
            "itemId"=>3,
            "text"=>"Добрый день, камрад!\n\nВремя взрывать и резать. Присоединяйся!"
        )
        ),


    );

    private $hasEvents;
    private $events= array();
    public $shoudUpdate;
    public $notify;
    public function isActive(&$event){
        $time = time();
        $day = date("l");

        foreach($event["days"]  as$activeDay){
           // ech
            if($activeDay==$day){
                $start = strtotime("today +".$event["timeStart"]." hour ");
                $end = strtotime("today +".($event["timeStart"]+$event["timeLast"])." hour");
              //  echo $event["type"].$start."\n". time()."\n";
                if(($this->shoudUpdate==0||$this->shoudUpdate>$start)&&$start>$time){

                    $this->shoudUpdate= $start;
                }

                if($end>$time&&$start<$time){
                    $event["end"] = $end;
                    $event["start"] = $start;
                    $this->notify[] = array("type"=>$event["type"],"ass_params"=>array("end"=>$end,"start"=>$start,"text"=>$event["text"],"item"=>$event["itemId"]));
                    return true;

                }

            }else{
                $start = strtotime("previous  ".$activeDay." +".$event["timeStart"]." hour");
                $end = strtotime("previous  ".$activeDay." +".($event["timeStart"]+$event["timeLast"])." hour");

              //  echo $end.">".$time."  ".$start."<".$time."<br>";
                if($end>$time&&$start<$time){
                    $event["end"] = $end;
                    $event["start"] = $start;
                    $this->notify[] = array("type"=>$event["type"],"ass_params"=>array("end"=>$end,"start"=>$start,"text"=>$event["text"],"item"=>$event["itemId"]));
                    return true;

                }
            }
        }
        return false;
    }
    public function isSessionActive($event){

        $time = time();
        if(($this->shoudUpdate==0||$this->shoudUpdate>$event["start"])&&$event["start"]>$time){

            $this->shoudUpdate= $event["start"];
        }
        //echo $time." ".$event["start"]." ".$event["end"];
        return $time>=$event["start"]&&$time<$event["end"];

    }
    public function __construct(){
        $this->hasEvents = false;
        foreach(self::$array  as $event){
            if($this->isActive($event)){
                $this->events[] = $event;
                $this->hasEvents = true;
            }
        }
       // print_r($_SESSION);
        foreach($_SESSION["events"] as $event){
            if($this->isSessionActive($event)){
                $this->events[] = $event;
                $this->notify[] = array("type"=>$event["type"],"ass_params"=>array("start"=>$event["start"],"end"=>$event["end"],"text"=>$event["text"],"item"=>$event["itemId"]));
                $this->hasEvents = true;
            }
        }

    }
    public function getDiscount($item){
        if($this->hasEvents){
            foreach($this->events as $element){
                if($element["itemId"]==$item){
                    switch($element["type"]){

                        case "ITEMDISCOUNT":
                            return $element["amount"];
                            break;
                    }
                }
            }
        }
        return 1;
    }
    public function getKitDiscount($id, &$end){
        if($this->hasEvents){
            foreach($this->events as $element){
                if($element["itemId"]==$id){
                    switch($element["type"]){

                        case "KITDISCOUNT":
                            $end= $element["end"];
                            return $element["amount"];
                            break;
                    }
                }
            }
        }
        return 1;
    }
    public function checkItem(&$item,$prices){
        if($this->hasEvents){
            foreach($this->events as $element){

                if($element["itemId"]==$item["id"]){
                  // print_r($element);
                    switch($element["type"]){
                        case "ITEMTAKE":
                            if($item["time_end"]< $element["end"]){
                                $item["time_end"]=   $element["end"];
                                $item["isEvented"]= "true";
                                $item["buytype"]= "FOR_GOLD_TIME";
                                $item['charge']=0;
                                $item['modslot']=0;
                                $item['mods']=0;
                            }
                            break;
                        case "ITEMDISCOUNT":
                            if($element["amount"]!=0){
                                if(isset($prices["single"])){
                                    foreach($prices["single"] as $key=>$single){
                                        $single["discount"]= $element["amount"];
                                        $single["discountEnd"]= $element["end"];
                                        $prices["single"] [$key]=$single;
                                    }
                                }
                                if(isset($prices["group"])){
                                    foreach($prices["group"] as $key=>$single){
                                        $single["discount"]= $element["amount"];
                                        $single["discountEnd"]= $element["end"];
                                        $prices["single"] [$key]=$single;
                                    }
                                }
                            }
                            break;
                    }

                }
            }

        }
        return $prices;
    }
    public static  function new_user(){
        $events= array();
        foreach(self::$sesssionPrice["newUser"] as $element){
            $element["start"] = time() + 3600*$element["startDelay"];
            $element["end"] =$element["start"] + 3600*$element["timeLast"];
            $events[] = $element;
        }
        $_SESSION["events"]=$events;
       // print_r($_SESSION);
   }
    public function  openspecial($event,&$notify){
        $events=  $_SESSION["events"];
            $element=self::$sesssionPrice["special"][$event];
            $element["start"] = time() + 3600*$element["startDelay"];
            $element["end"] =$element["start"] + 3600*$element["timeLast"];
            $events[] = $element;
        $notify[] = array("type"=>$element["type"],"ass_params"=>array("start"=>$element["start"],"end"=>$element["end"],"text"=>$element["text"],"item"=>$element["itemId"]));
        $_SESSION["events"]=$events;
    }
}