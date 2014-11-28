<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 17.07.14
 * Time: 15:37
 */



class RewardController extends AuthController{

    public function loadmoneyreward(){


        $xmlleveling= new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
						<reward>

						</reward>');

        $domaevel = dom_import_simplexml($xmlleveling);
        $sql = "SELECT * FROM `money_dictionary` ";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        foreach($sqldata as $element){

            $classOne   = new SimpleXMLElement('<money></money>');
            $classOne->addChild("value",$element["value"]);
            $classOne->addChild("valueGold",$element["valueGold"]);
            $classOne->addChild("name",$element["name"]);
            $domone  = dom_import_simplexml($classOne);

            $domone  = $domaevel->ownerDocument->importNode($domone, TRUE);
            $domaevel->appendChild($domone);


        }

        header('Content-type: text/xml');
        echo $xmlleveling->asXml();


    }
    public function syncmoneyreward(){
        $uid = $_REQUEST["uid"];
        $upCash = intval($_REQUEST["upCash"]);
        $upGold = intval($_REQUEST["upGold"]);
        $sql = "UPDATE statistic SET cash = cash +".$upCash.", gold = gold+ ".$upGold." WHERE uid ='".$uid."'";
        $db = DBHolder::GetDB();
        $db->query($sql);
        StatisticController::returnAllStats();
    }


}

