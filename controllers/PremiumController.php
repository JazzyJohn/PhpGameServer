<?php
/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 12.11.14
 * Time: 1:11
 */

class PremiumController extends BaseController{

  public function doublereward(){
      $input = $_REQUEST;
      $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
      $db = DBHolder::GetDB();
      $sql = "SELECT * FROM statistic WHERE uid = '".$input['uid']."'";
      $sqldata =$db->fletch_assoc($db->query($sql));
      $user =$sqldata[0];
      if($user["gold"]<  DOUBLE_REWARD_PRICE){
          $xmlresult->addChild("error",2);
          $xmlresult->addChild("errortext","Недостаточно денег ");
          echo $xmlresult->asXML();
          return;
      }
      $sql = "UPDATE statistic SET gold = gold -".DOUBLE_REWARD_PRICE." WHERE uid ='".$input['uid']."'";
      $db->query($sql);
      $xmlresult->addChild("error",0);
      $xmlresult->addChild("errortext","");
      echo $xmlresult->asXML();
  }
    public function lowerstamina(){
        $input = $_REQUEST;
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        $db = DBHolder::GetDB();
        $sql = "UPDATE statistic SET stamina = stamina -1 WHERE uid ='".$input['uid']."'";
        $db->query($sql);
        $xmlresult->addChild("error",0);
        $xmlresult->addChild("errortext","");
        echo $xmlresult->asXML();
    }

}