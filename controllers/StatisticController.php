<?
class StatisticController extends BaseController{

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
        $sql = "INSERT INTO statistic (`uid`,`name`,`killAi`) VALUES('".$data["uid"]."','".$data["name"]."',1)
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


    }

    public function statisticdata(){
        $data =$_REQUEST;

        $sql = "SELECT statisticData FROM statistic WHERE uid = '".$data['uid']."'";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $statisticData = array();
        if($sqldata[0]["statisticData"]!=""){
            $statisticData = json_decode($sqldata[0]["statisticData"],true);
        }
        Logger::instance()->write(print_r($statisticData,true));
        $killData ="";
        foreach($data["data"] as $key=>$value){
            if(!isset($statisticData[$key])){
                $statisticData[$key]= 0;
            }
            $statisticData[$key]+=$value;
            switch($key){
                case "Kill":
                    $killData= $killData.", `killCnt` =  `killCnt` + 1";
                    break;
                case "KillAI":
                    $killData= $killData.", `killAi` =  `killAi` + 1";
                    break;
            }
        }
        if(isset($statisticData["AmmoSpent"])&&$statisticData["AmmoSpent"]!=0){
            $statisticData["accuracy"]=$statisticData["AmmoHit"]/$statisticData["AmmoSpent"];
        }else{
            $statisticData["accuracy"]=0;
        }
        $sql =  "UPDATE statistic SET `statisticData` = '". json_encode($statisticData)."' ".$killData ." WHERE uid = '".$data['uid']."'";
        $db = DBHolder::GetDB();
        $db->query($sql);
    }

    public function addUser(){

        $data =$_REQUEST;
        $uid = $data["uid"];
        $sql = ' SELECT * FROM `statistic` WHERE uid ="'.$uid.'"';
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));

        $sql = "INSERT INTO statistic (`uid`,`name`,`cash`,`gold`,`lastEnter`) VALUES ('".$data["uid"]."','".$data["name"]."','".START_CASH."','".START_GOLD."','".time()."')  ON DUPLICATE KEY UPDATE ingameenter = ingameenter+1,lastEnter = ".time()." ;";

        $db = DBHolder::GetDB();
        $db->query($sql);
        $new=false;
        if(count($sqldata)==0){
             $sql = ' INSERT INTO `notify`  (`uid`) VALUES ("'.$uid.'")';
            $db->query($sql);
            $reward =new DaylyReward(false,$uid);
            ShopEvents::new_user();
            $new=true;
        }else{
            $reward =new DaylyReward($sqldata[0],$uid);
            $new=false;
        }
        $notifys = $reward->resolved();
		///file_put_contents("log.txt",mb_detect_encoding($data["name"]));

        self::returnAllStats($notifys,$new);
    }
    public static function returnSmallData(&$xml,$uid){
        $sql = "SELECT * FROM statistic LEFT JOIN player_skill ON statistic.UID = player_skill.uid WHERE statistic.UID = '".$uid."'";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $sqldata = $sqldata[0];
        $xml->addChild('skillpoint',$sqldata['skillpoint']==""?0:$sqldata['skillpoint']);
        $xml->addChild('gold',$sqldata['gold']);
        $xml->addChild('cash',$sqldata['cash']);

    }

    public static function returnAllStats($notifys = array(),$new=false){
        $data =$_REQUEST;
        $sql = "SELECT * FROM statistic LEFT JOIN player_skill ON statistic.UID = player_skill.uid WHERE statistic.UID = '".$data['uid']."'";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $sqldata = $sqldata[0];
       // print_r($sqldata);
        header('Content-type: text/xml');
        $xmlprofile = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <player><statistic></statistic><lottery></lottery></player>');
        $xmlprofile->addChild('uid',$sqldata['UID']);
        $xmlprofile->addChild('name',$sqldata['NAME']);
        $xmlprofile->addChild('kill',$sqldata['killCnt']);
        $xmlprofile->addChild('open_set',$sqldata['open_sid']);
        $xmlprofile->addChild('death',$sqldata['death']);
        $xmlprofile->addChild('assist',$sqldata['assist']);
        $xmlprofile->addChild('robotkill',$sqldata['robotkill']);
        $xmlprofile->addChild('skillpoint',$sqldata['skillpoint']==""?0:$sqldata['skillpoint']);
        $xmlprofile->addChild('robotdestroy',$sqldata['robotdestroy']);
        $xmlprofile->addChild('gold',$sqldata['gold']);
        $xmlprofile->addChild('cash',$sqldata['cash']);
        $xmlprofile->addChild('stamina',$sqldata['stamina']);
        $xmlprofile->addChild('premium',$sqldata['premium']==1?"true":"false");
        $xmlprofile->addChild('premiumEnd',date("c",$sqldata['premiumEnd']));
        if($new){
            $xmlprofile->addChild('new',$new?"true":"false");
        }
        if( MoneyReward::isDoneSocialReward($data['uid'])){
            $xmlprofile->addChild('socialDone',"true");
        }else{
            $xmlprofile->addChild('socialDone',"false");
        }
        $data  =Lottery::getLottery($sqldata['UID']);
        foreach($data as$key=>$element){
            $xmlprofile->lottery->addChild($key,$element);
        }
        if($sqldata["statisticData"]!=""){
            $statisticData = json_decode($sqldata["statisticData"],true);
            $domitems = dom_import_simplexml($xmlprofile->statistic);
            foreach($statisticData as $key=>$value){
                $statOne   = new SimpleXMLElement('<entry></entry>');
                if($key=="accuracy"){
                    $statOne->addChild("value",(int)($value*100));
                }else{
                    $statOne->addChild("value",$value);
                }
                $statOne->addChild("key",$key);

                $domone  = dom_import_simplexml($statOne);
                $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                $domitems->appendChild($domone);
            }
            $statOne   = new SimpleXMLElement('<entry></entry>');
            $statOne->addChild("value",$sqldata["daylicCnt"]);
            $statOne->addChild("key","daylicCnt");

            $domone  = dom_import_simplexml($statOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }

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
        $ShopEvents =    new ShopEvents();
        $notifys= array_merge($notifys, $ShopEvents->notify);

        $xmlprofile->addChild('update',$ShopEvents->shoudUpdate+rand(1,50));
        foreach($notifys as $element){
            $notOne   = new SimpleXMLElement('<notify></notify>');
            $notOne->addChild("type",$element["type"]);
            foreach($element["params"] as $param){
                $notOne->addChild("param",$param);
            }
            foreach($element["ass_params"] as $key=> $param){
                $notOne->addChild( $key,$param);
            }
            $domone  = dom_import_simplexml($notOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }


        if(isset($_REQUEST["tournament"])){
            $xmlprofile->addChild("tournament","yes");
            $sql = "SELECT uid, killCnt FROM statistic ORDER BY  killCnt DESC LIMIT 0,10";
            $sqldata =$db->fletch_assoc($db->query($sql));
            foreach($sqldata as $element){
                $notOne   = new SimpleXMLElement('<globalkillers></globalkillers>');
                $notOne->addChild("uid",$element["uid"]);
                $notOne->addChild("score",$element["killCnt"]);
                $domone  = dom_import_simplexml($notOne);
                $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                $domitems->appendChild($domone);
            }
            $sql = "SELECT uid, killAi FROM statistic ORDER BY  killAi DESC LIMIT 0,10";
            $sqldata =$db->fletch_assoc($db->query($sql));
            foreach($sqldata as $element){
                $notOne   = new SimpleXMLElement('<globalaikillers></globalaikillers>');
                $notOne->addChild("uid",$element["uid"]);
                $notOne->addChild("score",$element["killAi"]);
                $domone  = dom_import_simplexml($notOne);
                $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                $domitems->appendChild($domone);
            }
            $sql = "SELECT uid,lvl FROM  `level_player` WHERE class =-1 ORDER BY  `level_player`.`lvl` DESC   LIMIT 0,10";
            $sqldata =$db->fletch_assoc($db->query($sql));
            foreach($sqldata as $element){
                $notOne   = new SimpleXMLElement('<toplvls></toplvls>');
                $notOne->addChild("uid",$element["uid"]);
                $notOne->addChild("score",$element["lvl"]);
                $domone  = dom_import_simplexml($notOne);
                $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                $domitems->appendChild($domone);
            }
            $sql = "SELECT uid, cash FROM statistic ORDER BY  cash DESC LIMIT 0,10";
            $sqldata =$db->fletch_assoc($db->query($sql));
            foreach($sqldata as $element){
                $notOne   = new SimpleXMLElement('<topcash></topcash>');
                $notOne->addChild("uid",$element["uid"]);
                $notOne->addChild("score",$element["cash"]);
                $domone  = dom_import_simplexml($notOne);
                $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                $domitems->appendChild($domone);
            }
            $sql = "SELECT uid, daylicCnt FROM statistic ORDER BY  daylicCnt DESC LIMIT 0,10";
            $sqldata =$db->fletch_assoc($db->query($sql));
            foreach($sqldata as $element){
                $notOne   = new SimpleXMLElement('<daylic></daylic>');
                $notOne->addChild("uid",$element["uid"]);
                $notOne->addChild("score",$element["daylicCnt"]);
                $domone  = dom_import_simplexml($notOne);
                $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                $domitems->appendChild($domone);
            }
            if(Router::$isdebug){
                $sql = "SELECT * FROM operation WHERE status < 2 OR status =3";
            }else{
                $sql = "SELECT * FROM operation WHERE status < 2";
            }
            $operations =$db->fletch_assoc($db->query($sql));
            $ids = array();
            foreach($operations as $element){
                $ids[]=$element["id"];
            }

            $sql = "SELECT * FROM operation_players WHERE oid IN (".implode(",",$ids).") AND uid ='".$data['uid']."'";
            $myscore = array();
            $sqldata =$db->fletch_assoc($db->query($sql));
            foreach($sqldata as $element){
                $myscore[$element["oid"]] = $element["counter"];
            }

            foreach($operations as $element){
                if($element["status"]==1||(Router::$isdebug&&$element["status"]==3)){
                    $notOne   = new SimpleXMLElement('<lastoperation></lastoperation>');
                }else{

                    $notOne   = new SimpleXMLElement('<currentoperation></currentoperation>');
                }
                 $mycounter =  isset($myscore[$element["id"]])?$myscore[$element["id"]]:0;

                $notOne->addChild("id",$element["id"]);
                $notOne->addChild("prizeplaces",$element["prizeplaces"]);
                $tar = explode(",",$element["cashReward"]);
                foreach($tar as $reward){
                    $notOne->addChild("cashReward",$reward==""?0:$reward);
                }
                $tar = explode(",",$element["goldReward"]);
                foreach($tar as $reward){
                    $notOne->addChild("goldReward",$reward==""?0:$reward);
                }
                $tar = explode(",",$element["expReward"]);
                foreach($tar as $reward){
                    $notOne->addChild("expReward",$reward==""?0:$reward);
                }

                $domone  = dom_import_simplexml($notOne);
                $sql = "SELECT * FROM operation_players WHERE oid = ".$element["id"]." ORDER BY counter  DESC LIMIT 0,40 ";

                $sortedwinners =$db->fletch_assoc($db->query($sql));
                $sql = "SELECT COUNT(oid) FROM operation_players WHERE  oid = ".$element["id"]." AND counter > ".$mycounter;
                $place =$db->fletch_assoc($db->query($sql));
                $place = $place[0]["COUNT(oid)"]+1;

                foreach($sortedwinners as $winer){
                    $winerNode   = new SimpleXMLElement('<winners></winners>');
                    $winerNode->addChild("uid",$winer["uid"]);
                    $winerNode->addChild("score",$winer["counter"]);
                    $domwin  = dom_import_simplexml($winerNode);
                    $domwin  = $domone->ownerDocument->importNode($domwin, TRUE);
                    $domone->appendChild($domwin);
                }

                $notOne->addChild("start",$element["start"]);
                $notOne->addChild("end",$element["end"]);
                $notOne->addChild("name",$element["name"]);
                $notOne->addChild("desctiption",$element["desctiption"]);
                $notOne->addChild("counterEvent",$element["counterEvent"]);
                $notOne->addChild("myCounter",$mycounter);
                $notOne->addChild("myPlace",$place);
                $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                $domitems->appendChild($domone);

            }
        }
        if(isset($_REQUEST["premium"])){
            $sql = "SELECT * FROM `premium_skill`";
            $skills =$db->fletch_assoc($db->query($sql));

            $sql = "SELECT * FROM `premium_players` WHERE uid = '".$data['uid']."'";
            $data =$db->fletch_assoc($db->query($sql));
            if(isset($data[0]["data"])){
                $data = json_decode($data[0]['data'],true);
            }else{
                $data= array();
            }
            $domitems = dom_import_simplexml($xmlprofile);
            foreach($skills as $element){
                $notOne   = new SimpleXMLElement('<premiumskill></premiumskill>');
                $notOne->addChild("id",$element["id"]);
                $notOne->addChild("type",$element["type"]);
                $notOne->addChild("gameData",$element["gameData"]);
                $notOne->addChild("name",$element["name"]);
                $notOne->addChild("descr",$element["description"]);
                $notOne->addChild("icon",$element["icon"]);
                $notOne->addChild("maxAmount",$element["maxAmount"]==1?'true':"false");
                $tar = explode(",",$element["eventTriggers"]);
                foreach($tar as $event){
                    if($event!=""){
                        $notOne->addChild("eventTriggers",$event);
                    }
                }
                $tar = explode(",",$element["price"]);
                foreach($tar as $event){
                    $notOne->addChild("price",$event);
                }
                $notOne->addChild("team",$element["team"]);
                if(isset($data[$element["id"]])){
                    $notOne->addChild("timeEnd",$data[$element["id"]]);
                }else{
                    $notOne->addChild("timeEnd","");
                }
                $domone  = dom_import_simplexml($notOne);
                $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
                $domitems->appendChild($domone);
            }



        }
        echo $xmlprofile->asXML();
    }


    public function globalerrorlog(){

    }
	 

    public function buyPremiumSkill(){
        header('Content-type: text/xml');
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        $input = $_REQUEST;
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM `premium_skill` WHERE id = '".$input["itemid"]."'";
        $skills =$db->fletch_assoc($db->query($sql));

        $sql = "SELECT * FROM `premium_players` WHERE uid = '".$input['uid']."'";
        $data =$db->fletch_assoc($db->query($sql));
        if(isset($data[0]["data"])){
            $data = json_decode($data[0]['data'],true);
        }else{
            $data= array();
        }
        $skill = $skills[0];
        $price =     explode(",",$skill["price"]);
        $price = $price[$input["price"]];
        $sql = "SELECT * FROM statistic WHERE uid = '".$input['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];

        if($user["gold"]<  $price){
            $xmlresult->addChild("error",2);
            $xmlresult->addChild("errortext","Недостаточно денег");
            echo $xmlresult->asXML();
            return;
        }
        $xmlresult->addChild("error",0);
        $sql = "UPDATE statistic SET gold = gold -".$price." WHERE uid ='".$input['uid']."'";
        $db->query($sql);
        $addTime = 0;
        switch($input["price"]){
            case 0:
                $addTime =SKILL_PRICE_1_DAYS;
                break;
            case 1:
                $addTime =SKILL_PRICE_2_DAYS;
                break;
            case 2:
                $addTime =SKILL_PRICE_3_DAYS;
                break;
        }
        if(isset($data[$skill["id"]])&&$data[$skill["id"]]>time()){

            $newTime = $data[$skill["id"]]+$addTime*86400;
        }else{
            $newTime = time()+$addTime*86400;
        }
        $data[$skill["id"]]=$newTime;
        $data = json_encode($data);
        $sql = "INSERT INTO premium_players (`uid`,`data`) VALUES ('".$input["uid"]."','".$data."')  ON DUPLICATE KEY UPDATE data ='".$data."' ;";
        $db->query($sql);
        $domitems = dom_import_simplexml($xmlresult);
        foreach($skills as $element){
            $notOne   = new SimpleXMLElement('<premiumskill></premiumskill>');
            $notOne->addChild("id",$element["id"]);
            $notOne->addChild("timeEnd",$newTime);

            $domone  = dom_import_simplexml($notOne);
            $domone  = $domitems->ownerDocument->importNode($domone, TRUE);
            $domitems->appendChild($domone);
        }
        echo $xmlresult->asXML();
    }

    public function socialPrize(){
        $input = $_REQUEST;
        $sql = "SELECT * FROM jew_events WHERE uid = '".$input['uid']."'";
        $db = DBHolder::GetDB();
        $data =$db->fletch_assoc($db->query($sql));
        if(isset($data[0]["steps"])&&$data[0]["steps"]!=""){
            $answer = json_decode($data[0]["steps"],true);
        }else{
            $answer =array();
        }

        if($answer["finished"]==1){
            echo 1;
            return;
        }
        if($answer["invite"]==0){
            if($input["invite"]==0){
                echo 0;

                return;
            }
        }
        $answer["invite"]=1;
        if($answer["friends"]==0){
            if($input["friends"]==0){
                echo 0;
                $sql =  "INSERT INTO jew_events (`uid`,`steps`) VALUES ('".$input["uid"]."','".json_encode($answer)."')  ON DUPLICATE KEY UPDATE `steps` ='".json_encode($answer)."' ";
                $db->query($sql);
                return;
            }
        }
        $answer["friends"]=1;
        if($answer["group"]==0){
            if($input["group"]==0){
                $sql =  "INSERT INTO jew_events (`uid`,`steps`) VALUES ('".$input["uid"]."','".json_encode($answer)."')  ON DUPLICATE KEY UPDATE `steps` ='".json_encode($answer)."' ";
                $db->query($sql);
                return;
            }
        }
        $answer["group"]=1;


        if($answer["bookmarks"]==0){
            if($input["bookmarks"]==0){

                echo 0;
                $sql =  "INSERT INTO jew_events (`uid`,`steps`) VALUES ('".$input["uid"]."','".json_encode($answer)."')  ON DUPLICATE KEY UPDATE `steps` ='".json_encode($answer)."'  ";
                $db->query($sql);
                return;
            }
        }
        $answer["bookmarks"]=1;

        $sql =  "UPDATE statistic SET `cash` = `cash` + ".VIRAL_CASH." , `gold` = `gold` + ".VIRAL_GOLD." WHERE uid = '". $input['uid']."'";
        $db->query($sql);
        $answer["finished"]=1;
        $sql =  "INSERT INTO jew_events (`uid`,`steps`) VALUES ('".$input["uid"]."','".json_encode($answer)."')  ON DUPLICATE KEY UPDATE `steps` ='".json_encode($answer)."'  ";
        $db->query($sql);
        echo 1;
    }
    public function socialReward(){
        header('Content-type: text/xml');
        $input = $_REQUEST;
        $db = DBHolder::GetDB();
        if(!MoneyReward::isDoneSocialReward($input['uid'])){
            MoneyReward::setSocialRewardDone($input['uid']);
            $sql ="SELECT * FROM `game_items_kit` WHERE id=".SOCIAL_KIT;
            $sqldata =$db->fletch_assoc($db->query($sql));


            $kit = $sqldata[0];
            OrderController::resolveKit($kit,$input['uid'],0);
        }
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result><error>0</error>
							</result>');
        echo $xmlresult->asXML();
    }
}