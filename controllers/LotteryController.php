<?php
/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 21.05.15
 * Time: 12:42
 */

class LotteryController extends BaseController{

    public function buyLotteryPlay(){
        header('Content-type: text/xml');
        $data =$_REQUEST;
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM statistic WHERE uid = '".$data['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        // echo SKIP_TASK_COST;

        $cost =  LOTERY_COST*$data["amount"];


        if($cost>$user["gold"]){
            $xmlresult->addChild("error",2);
            $xmlresult->addChild("errortext","Недостаточно денег");
            echo $xmlresult->asXML();
            return;
        }

        $sql = "UPDATE statistic SET gold = gold -".$cost." WHERE uid ='".$data['uid']."'";
        $db->query($sql);
        $xmlresult->addChild("error",0);
        $xmlresult->addChild("errortext","");
        Lottery::add($data['uid'],$data["amount"]);
        echo $xmlresult->asXML();
    }

    public function loteryResult(){
        header('Content-type: text/xml');
        $data =$_REQUEST;
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result><lottery></lottery></result>');
        Lottery::result($data['uid'],$data["result"],$xmlresult);
        $xmlresult->addChild("error",0);
        $xmlresult->addChild("errortext","");
        echo $xmlresult->asXML();

    }
}