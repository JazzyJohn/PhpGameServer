<?
class StatisticController extends BaseController{
    /*public function before(){
                if(!isset($_REQUEST["authkey"])||$_REQUEST["authkey"]!=self::$unity_key){
                    return false;
                }
    }*/
    public function killedBy(){
        $data =$_REQUEST;
        $sql = "INSERT INTO statistic (`uid`,`name`,`death`) VALUES('".$data["uid"]."','".$data["name"]."',1)
        ON DUPLICATE KEY UPDATE death = death + 1   ;";
        $db = DBHolder::GetDB();
        $db->query($sql);
        if(!isset($data["killeruid"])){

        }
        if(isset($data["inbot"])&&$data["inbot"]==true){
            $sql = "INSERT INTO statistic (`uid`,`name`,`robotkill`) VALUES(".$data["killeruid"].",'".$data["killername"]."',1)
            ON DUPLICATE KEY UPDATE robotkill = robotkill + 1    ;";
        }else{
            $sql = "INSERT INTO statistic (`uid`,`name`,`killCnt`) VALUES(".$data["killeruid"].",'".$data["killername"]."',1)
            ON DUPLICATE KEY UPDATE `killCnt` =  `killCnt` + 1   ;";
        }
        $db->query($sql);
        if(isset($data["assistuid"])){
            $sql = "INSERT INTO statistic (`uid`,`name`,`assist`) VALUES(".$data["assistuid"].",'".$data["assistname"]."',1)
                ON DUPLICATE KEY UPDATE assist = assist + 1   ;";
            $db->query($sql);
        }
    }
    public function killNpc(){
        $data =$_REQUEST;
        $sql = "INSERT INTO statistic (`uid`,`name`,`death`) VALUES('".$data["uid"]."','".$data["name"]."',0)
        ON DUPLICATE KEY UPDATE killAi = killAi + 1   ;";
        $db = DBHolder::GetDB();
        $db->query($sql);
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

		///file_put_contents("log.txt",mb_detect_encoding($data["name"]));
        $sql = "INSERT INTO statistic (`uid`,`name`) VALUES ('".$data["uid"]."','".$data["name"]."')  ON DUPLICATE KEY UPDATE ingameenter = ingameenter+1   ;";
		
        $db = DBHolder::GetDB();
        $db->query($sql);
        self::returnAllStats();
    }
    public static function returnAllStats(){
        $data =$_REQUEST;
        $sql = "SELECT * FROM statistic WHERE uid = '".$data['uid']."'";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $sqldata = $sqldata[0];
       // print_r($sqldata);
        header('Content-type: text/xml');
        $xmlprofile = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <player></player>');
        $xmlprofile->addChild('uid',$sqldata['UID']);
        $xmlprofile->addChild('name',$sqldata['NAME']);
        $xmlprofile->addChild('kill',$sqldata['killCnt']);
        $xmlprofile->addChild('death',$sqldata['death']);
        $xmlprofile->addChild('assist',$sqldata['assist']);
        $xmlprofile->addChild('robotkill',$sqldata['robotkill']);
        $xmlprofile->addChild('robotdestroy',$sqldata['robotdestroy']);
        $xmlprofile->addChild('gold',$sqldata['gold']);
        $xmlprofile->addChild('cash',$sqldata['cash']);
        $xmlprofile->addChild('stamina',$sqldata['stamina']);
        $xmlprofile->addChild('premium',$sqldata['premium']);
        $xmlprofile->addChild('premiumEnd',date("c",$sqldata['premiumEnd']));

        $sql = "SELECT * FROM asyncnotifiers WHERE uid = '".$data['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $sql = "DELETE FROM asyncnotifiers WHERE uid = '".$data['uid']."'";
        $db->query($sql);

        $domitems = dom_import_simplexml($xmlprofile);
        foreach($sqldata as $element){
            $notOne   = new SimpleXMLElement('<notify></notify>');
            $notOne->addChild("type",$element["type"]);
            $tar  =explode(',',$element["params"]);
            foreach($tar as $param){
                $notOne->addChild("param",$param);
            }

            $domone  = dom_import_simplexml($notOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }

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

    public function globalerrorlog(){
        $data =$_REQUEST;
        $sql = "INSERT INTO errorlog (`uid`,`time`,`logString`,`stackTrace`) VALUES ('".$data["uid"]."','".$data["time"]."','".$data["logString"]."','".$data["stackTrace"]."');";


        $db = DBHolder::GetDB();
        $db->query($sql);
    }
	 

}