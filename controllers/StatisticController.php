<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 09.04.14
 * Time: 15:58
 */
class StatisticController extends BaseController{
    private static $aurh =  'https://oauth.vk.com/access_token?client_id=3925872&client_secret=NomkD4Dt42BP0X3TrLx4&v=5.21&grant_type=client_credentials';

    private static $api_id ="3925872";
    private static $secret_key="NomkD4Dt42BP0X3TrLx4";
    private static $unity_key = "";
    public function before(){
                if(!isset($_REQUEST["authkey"])||$_REQUEST["authkey"]!=self::$unity_key){
                    return false;
                }
    }
    public function killedBy(){
        $data =$_REQUEST;
        $sql = "INSERT INTO statistic (`uid`,`name`,`death`) VALUES(".$data["uid"].",'".$data["name"]."',1)
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
        $sql = "INSERT INTO statistic (`uid`,`name`,`robotdestroy`) VALUES(".$data["uid"].",'".$data["name"]."',1)
        ON DUPLICATE KEY UPDATE robotdestroy = robotdestroy+1   ;";
        $db = DBHolder::GetDB();
        $db->query($sql);


        $db->query($sql);
    }

    public function addUser(){
        $data =$_REQUEST;
		file_put_contents("log.txt",mb_detect_encoding($data["name"]));
        $sql = "INSERT INTO statistic (`uid`,`name`) VALUES (".$data["uid"].",'".$data["name"]."')  ON DUPLICATE KEY UPDATE ingameenter = ingameenter+1   ;";
		
        $db = DBHolder::GetDB();
        $db->query($sql);
    }
    public function returnAllStats(){
        $data =$_REQUEST;
        $sql = "SELECT * FROM statistic WHERE uid = ".$data['uid'];
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $answer = array();
        $answer["uid"] = $data['uid'];
        $answer["name"] = isset($sqldata[0])?$sqldata[0]['name']:"";
        $answer["kill"] = isset($sqldata[0])?$sqldata[0]['killCnt']:"";
        $answer["death"] = isset($sqldata[0])?$sqldata[0]['death']:"";
        $answer["assist"] = isset($sqldata[0])?$sqldata[0]['assist']:"";
        $answer["robotkill"] = isset($sqldata[0])?$sqldata[0]['robotkill']:"";
        $answer["robotdestroy"] = isset($sqldata[0])?$sqldata[0]['robotdestroy']:"";
        $answer["lvl"] = isset($sqldata[0])?$sqldata[0]['lvl']:"";
        echo  json_encode($answer);
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
    public static function getVKAUTH(){
        $token = json_decode(file_get_contents(self::$aurh),true);
        $token =$token["access_token"];
        return $token;
    }



}