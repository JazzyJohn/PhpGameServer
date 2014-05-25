<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 09.04.14
 * Time: 15:58
 */
class StatisticController extends BaseController{
  
    public function before(){
                if(!isset($_REQUEST["authkey"])||$_REQUEST["authkey"]!=self::$unity_key){
                    return false;
                }
    }
    public function killedBy(){
        $data =$_REQUEST;
        $sql = "INSERT INTO statistic (`uid`,`name`,`death`) VALUES('".$data["uid"]."','".$data["name"]."',1)
        ON DUPLICATE KEY UPDATE death = death+1   ;";
        $db = DBHolder::GetDB();
        $db->query($sql);
        if(isset($data["inbot"])&&$data["inbot"]==true){
            $sql = "INSERT INTO statistic (`uid`,`name`,`robotkill`) VALUES(".$data["killeruid"].",'".$data["killername"]."',1)
            ON DUPLICATE KEY UPDATE robotkill = robotkill+1   ;";
        }else{
            $sql = "INSERT INTO statistic (`uid`,`name`,`killCnt`) VALUES(".$data["killeruid"].",'".$data["killername"]."',1)
            ON DUPLICATE KEY UPDATE `killCnt` =  `killCnt` + 1   ;";
        }
        $db->query($sql);
        if(isset($data["assistuid"])){
            $sql = "INSERT INTO statistic (`uid`,`name`,`assist`) VALUES(".$data["assistuid"].",'".$data["assistname"]."',1)
                ON DUPLICATE KEY UPDATE assist = assist+1   ;";
            $db->query($sql);
        }
    }

    public function robotKilled(){
        $data =$_REQUEST;
        $sql = "INSERT INTO statistic (`uid`,`name`,`robotdestroy`) VALUES('".$data["uid"]."','".$data["name"]."',1)
        ON DUPLICATE KEY UPDATE robotdestroy = robotdestroy+1   ;";
        $db = DBHolder::GetDB();
        $db->query($sql);


        $db->query($sql);
    }

    public function addUser(){
        $data =$_REQUEST;
		file_put_contents("log.txt",mb_detect_encoding($data["name"]));
        $sql = "INSERT INTO statistic (`uid`,`name`) VALUES ('".$data["uid"]."','".$data["name"]."')  ON DUPLICATE KEY UPDATE ingameenter = ingameenter+1   ;";
		
        $db = DBHolder::GetDB();
        $db->query($sql);
       $this->returnAllStats();
    }
    public function returnAllStats(){
        $data =$_REQUEST;
        $sql = "SELECT * FROM statistic WHERE uid = '".$data['uid']."'";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $sqldata = $sqldata[0];
        $xmlprofile = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <player>
							</player>');
        $xmlprofile->addChild('uid',$sqldata['uid']);
        $xmlprofile->addChild('name',$sqldata['name']);
        $xmlprofile->addChild('kill',$sqldata['kill']);
        $xmlprofile->addChild('death',$sqldata['death']);
        $xmlprofile->addChild('assist',$sqldata['assist']);
        $xmlprofile->addChild('robotkill',$sqldata['robotkill']);
        $xmlprofile->addChild('robotdestroy',$sqldata['robotdestroy']);
        $xmlprofile->addChild('gold',$sqldata['gold']);
        $xmlprofile->addChild('cash',$sqldata['cash']);
         echo $xmlprofile->asXML();
    }


    public function notifyUsers(){
        $data =$_REQUEST;
		if(!isset($data["message"])){
		 include "static/form.php";
		 return;
		}
        $sql = "SELECT uid FROM statistic";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $token = self::getVKAUTH();
		  Logger::instance()->write($token );
        $VK = new vkapi(self::$api_id, self::$secret_key);
        $i=0;
        while($i<count($sqldata)){
            $uids =array();
            for(;$i<count($sqldata);$i++){
				if($sqldata[$i]['uid']>0){
					$uids[] = $sqldata[$i]['uid'];
					if(count($uids)>=99){
						break;
					}
				}
            }


            $resp = $VK->api('secure.sendNotification', array('user_ids'=>implode(",",$uids),'message'=>$data["message"],"client_secret"=>$token));

			Logger::instance()->write(print_r($resp,true) );
		print_r($resp);
        }



    }
	 

}