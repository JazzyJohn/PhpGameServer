<?php
/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 22.05.15
 * Time: 15:56
 */

class Lottery{
  public static $array =
      array(
          "0"=>array(
              "breakStrike"=>false,
              "type"=>"GOLD",
              "data"=>30,

          ),
          "1"=>array(
              "breakStrike"=>false,
              "type"=>"CASH",
              "data"=>1500,

          ),
          "2"=>array(
              "breakStrike"=>false,
              "type"=>"CASH",
              "data"=>15000,
          ),
          "3"=>array(
              "breakStrike"=>false,
              "type"=>"PREMIUM",
              "data"=>array(
                  1,3,5,6,7,8,9,10
              )
          ),
          "4"=>array(
              "breakStrike"=>false,
              "type"=>"ITEM",
              "data"=>array(
                  "1"=>66,
                  "2"=>66
              )
          ),
          "5"=>array(
              "breakStrike"=>false,
              "type"=>"CASH",
              "data"=>7000,

          ),
          "6"=>array(
              "breakStrike"=>true,
              "type"=>"ITEM",
              "data"=>array(
                  "1"=>76,
                  "2"=>77

              ),

          ),
          "7"=>array(
              "breakStrike"=>false,
              "type"=>"REPLAY",


          ),
          "8"=>array(
              "breakStrike"=>false,
              "type"=>"GOLD",
              "data"=>100,
          ),
          "9"=>array(
              "breakStrike"=>false,
              "type"=>"CASH",
              "data"=>10000,

          ),
          "10"=>array(
              "breakStrike"=>false,
              "type"=>"KIT",
              "data"=>1,

          ),
          "11"=>array(
              "breakStrike"=>false,
              "type"=>"GOLD",
              "data"=>15,
          ),


      );



    public static function getLottery($uid){

        if( isset( $_SESSION["lottery"])){
            $lottery= $_SESSION["lottery"];
        }else{
            $db = DBHolder::GetDB();
            $sql = "SELECT * FROM lottery WHERE uid = '".$uid."'";
            $sqldata =$db->fletch_assoc($db->query($sql));
            if(isset($sqldata[0])){
                $lottery = $sqldata[0];
            }else{
                $lottery = array(
                    "boughtReplays"=>0,
                    "totalBoughtReplays"=>0,
                    "freeReplays"=>0,
                    "lastEnterDate"=>0
                );


            }


        }
        if(date("d.m.Y")!=date("d.m.Y",$lottery["lastEnterDate"])){
            $lottery["lastEnterDate"]  = time();
            $lottery["freeReplays"] =LOTERY_FREE;
            self::save($uid,$lottery);
        }
        $_SESSION["lottery"]=$lottery;;
        return   $lottery;

  }

    public static function save($uid,$lottery){

        $db = DBHolder::GetDB();

        $sql = "INSERT INTO `lottery` (`uid`, `totalBoughtReplays`, `boughtReplays`, `freeReplays`, `lastEnterDate`) VALUES ('".$uid."', '".$lottery["totalBoughtReplays"]."', '".$lottery["boughtReplays"]."', '".$lottery["freeReplays"]."', '".$lottery["lastEnterDate"]."')
              ON DUPLICATE KEY UPDATE totalBoughtReplays = '".$lottery["totalBoughtReplays"]."', boughtReplays = '".$lottery["boughtReplays"]."', freeReplays = '".$lottery["freeReplays"]."', lastEnterDate = '".$lottery["lastEnterDate"]."' ;";
        $db->query($sql);
        $_SESSION["lottery"]=$lottery;;
    }

    public static function add($uid, $amount)
    {
        $lottery =  self::getLottery($uid);

        $lottery["totalBoughtReplays"]+=$amount;
        $lottery["boughtReplays"]+=$amount;
        self::save($uid,$lottery);
    }

    public static function result($uid, $result,&$xml)
    {
        $lottery =  self::getLottery($uid);

        $db = DBHolder::GetDB();
        $prize = self::$array[$result];
        if($prize["breakStrike"]){
            $lottery["totalBoughtReplays"]=0;
        }
        switch($prize["type"]){
            case "GOLD":
                $xml->addChild("state","MONEY");
                $xml->addChild("gold",$prize["data"]);
                $xml->addChild("cash",0);
                $xml->addChild("skill",0);
                $sql = "UPDATE statistic SET gold = gold +".$prize["data"]." WHERE uid ='".$uid."'";
                $db->query($sql);
                break;
            case "CASH":
                $xml->addChild("state","MONEY");
                $xml->addChild("gold",0);
                $xml->addChild("cash",$prize["data"]);
                $xml->addChild("skill",0);
                $sql = "UPDATE statistic SET cash = cash +".$prize["data"]." WHERE uid ='".$uid."'";
                $db->query($sql);
                break;
            case "SKILL":
                $xml->addChild("state","MONEY");
                $xml->addChild("gold",0);
                $xml->addChild("cash",0);
                $xml->addChild("skill",$prize["data"]);
                $sql = "INSERT INTO player_skill (`uid`,`skillpoint`) VALUES('".$uid."',".$prize["data"].")
                 ON DUPLICATE KEY UPDATE skillpoint  = skillpoint +".$prize["data"]."  ;";

                $db->query($sql);
                break;
            case "ITEM":
                $xml->addChild("state","ITEM");
                $sql = "SELECT * FROM `level_player` WHERE uid = '".$uid."' AND  class=-1";

                $sqldata =$db->fletch_assoc($db->query($sql));
                $lvl = $sqldata[0]["lvl"];
                $lvl =(int)($lvl/10);
                $xml->addChild("item",$prize["data"][$lvl]);
                $sql ="INSERT INTO game_items_players (`uid`,`item_id`,`buytype`) VALUES('".$uid."','".$prize["data"][$lvl]."','FOR_GOLD_FOREVER')
                  ON DUPLICATE KEY UPDATE `buytype` = 'FOR_GOLD_FOREVER'
                ";
                $db->query($sql);
                break;
            case "PREMIUM":
                $xml->addChild("state","PREMIUM");
                $sql = "SELECT * FROM `premium_players` WHERE uid = '".$uid."'";
                $data =$db->fletch_assoc($db->query($sql));
                if(isset($data[0]["data"])){
                    $data = json_decode($data[0]['data'],true);
                }else{
                    $data= array();
                }
                $skill = $prize["data"][array_rand($prize["data"])];

                if(isset($data[$skill])&&$data[$skill]>time()){

                    $newTime = $data[$skill]+86400;
                }else{
                    $newTime = time()+86400;
                }

                $data[$skill]=$newTime;

                $xml->addChild("skill",$skill);
                $data = json_encode($data);
                $sql = "INSERT INTO premium_players (`uid`,`data`) VALUES ('".$uid."','".$data."')  ON DUPLICATE KEY UPDATE data ='".$data."' ;";
                $db->query($sql);


                break;
            case "KIT":
                $xml->addChild("state","KIT");
                $sql ="SELECT * FROM `game_items_kit` WHERE id=".$prize["data"];
                $sqldata =$db->fletch_assoc($db->query($sql));

                $xml->addChild("kit",$prize["data"]);
                $kit = $sqldata[0];
                OrderController::resolveKit($kit,$uid,0);
                break;
            case "REPLAY":
                $xml->addChild("state","REPLAY");
                $lottery["freeReplays"]++;
                break;
            case "NONE":
                $xml->addChild("state","NONE");
                break;

        }
        if($lottery["boughtReplays"] > 0)
        {
            $lottery["boughtReplays"]--;
        }
        else if(  $lottery["freeReplays"]  > 0)
        {
            $lottery["freeReplays"] --;
        }
        self::save($uid,$lottery);
        foreach($lottery as$key=>$element){
            $xml->lottery->addChild($key,$element);
        }
    }


}