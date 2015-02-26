<?

class AchivementController extends AuthController{

	public function loadachive(){
		header('Content-type: text/xml');
        $data =$_REQUEST;
        $db = DBHolder::GetDB();

        $sql = "SELECT * FROM `achievement_data` WHERE uid ='".$data["uid"]."' AND time ='".strtotime(date("d-m-Y",time()))."'";
        $dayly=$db->fletch_assoc($db->query($sql));
        if(!isset($dayly[0]["count"])){
            $sql = "INSERT INTO `achievement_data` (`uid`, `time`)VALUES ('".$data["uid"]."', '');";
            $db->query($sql);
        }



		$sql =
            "SELECT id,`order`,type,achievement_list.name,description,multiplie, icon,achivement_params.name AS paramname,needvalue FROM `achievement_list` INNER JOIN `achivement_params` ON achievement_list.id=achivement_params.aid WHERE achievement_list.open = 1 OR
             (achievement_list.type=\"TASK\" AND
             ( (previous = (SELECT `task_easy_step` FROM `achievement_data` WHERE uid =\"".$data["uid"]."\") AND `order`=1) OR
             (previous = (SELECT `task_medium_step` FROM `achievement_data` WHERE uid =\"".$data["uid"]."\" )AND `order`=2) OR
             (previous = (SELECT `task_hard_step` FROM `achievement_data` WHERE uid =\"".$data["uid"]."\")AND `order`=3)))";

        $sqldata =$db->fletch_assoc($db->query($sql));
		$achivmnets = array();
		foreach( $sqldata as $element){
			if(!isset($achivmnets[$element["id"]])){
				$achivmnets[$element["id"]] = $element;
				$achivmnets[$element["id"]]["PARAMS"] = array();
				
			}
			$achivmnets[$element["id"]]["PARAMS"][]= array(
					"name"=>$element["paramname"],
					"value"=>$element["needvalue"]
				);
		}




		$sql = "SELECT * FROM `achivement_opened` WHERE uid ='".$data["uid"]."'";
		$sqldata =$db->fletch_assoc($db->query($sql));
		$open = array();
		foreach( $sqldata as $element){
			$open[$element["aid"]] =$element;
		}
		$xmlachiv = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><achivements></achivements>');
		$domachiv = dom_import_simplexml($xmlachiv);
		foreach($achivmnets as $element){
			$achOne   = new SimpleXMLElement('<achivement></achivement>');
			$achOne->addChild("id",$element["id"]);
			$achOne->addChild("name",$element["name"]);
			$achOne->addChild("description",$element["description"]);
            $achOne->addChild("icon",$element["icon"]);
            $achOne->addChild("type",$element["type"]);
            $achOne->addChild("order",$element["order"]);
			if(isset($open[$element["id"]])){
                if($element["multiplie"]==1){
                    if($open[$element["id"]]['time']+86400<time()){
                        $achOne->addChild("ready","true");
                    }else{
                        $achOne->addChild("ready","false");
                    }
                    $achOne->addChild("multiplie","true");
                }else{
                    $achOne->addChild("ready","false");
                    $achOne->addChild("multiplie","false");
                }

				$achOne->addChild("open","true");
                $achOne->addChild("amount",$open[$element["id"]]["amount"]);
			}else{

                $achOne->addChild("ready","true");
                if($element["multiplie"]==1){
                    $achOne->addChild("multiplie","true");
                }else{
                    $achOne->addChild("multiplie","false");
                }
				$achOne->addChild("open","false");
                $achOne->addChild("amount",0);
			}
			$domone  = dom_import_simplexml($achOne);
		
			foreach($element["PARAMS"] as $parms){
					$paramOne   = new SimpleXMLElement('<param></param>');
					$paramOne->addChild("name",$parms["name"]);
					$paramOne->addChild("value",$parms["value"]);
                    $paramOne->addChild("resetname",$parms["resetname"]);

					$domoneparam  = dom_import_simplexml($paramOne);
					$domoneparam  = $domone->ownerDocument->importNode($domoneparam, TRUE);
					$domone->appendChild($domoneparam);
			}
			$domone  = $domachiv->ownerDocument->importNode($domone, TRUE);
			$domachiv->appendChild($domone);
		}
        if(isset($dayly[0]["count"])){
            $xmlachiv->addChild("daylyfinish", $dayly[0]["count"]==10?"true":"false");
        }else{
            $xmlachiv->addChild("daylyfinish", "false");
        }
		echo $xmlachiv->asXml();
	}
	public function saveachive(){
        $data =$_REQUEST;
        $array = array();
        $today =  mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
        foreach($data["ids"] as $element){
            $array[] = "('".$data["uid"]."',".$element.",'1','".$today."')";
        }
        $db = DBHolder::GetDB();
            $sql = "INSERT INTO `achivement_opened` (`uid`,`aid`,`amount`,`time`)  VALUES ".implode(",",$array)."  ON DUPLICATE KEY UPDATE amount = amount+1, time = ".time();
        $db->query($sql);
        $sql = "SELECT * FROM `achievement_list` WHERE id IN (".implode(",",$data["ids"] ).")";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $addToDaylic = 0;
        $addcash = 0;
        $addgold = 0;

        foreach($sqldata as $element){
            switch($element["type"]){

                case "DAYLIC":
                    $addToDaylic++;
                    $addcash+= $element["cashreward"];

                    break;
                case "ACHIEVEMENT":
                    $addgold+= $element["cashreward"];
                    break;
            }
        }
        $sql = "UPDATE statistic SET cash = cash +".$addcash." , gold = gold+ ".$addgold.",daylicCnt = daylicCnt + ".$addToDaylic." WHERE uid ='".$data["uid"]."'";

        $db->query($sql);
        $sql = "UPDATE `achievement_data` SET count ="
           . "CASE \n"
            . " WHEN (`time`< ".$today. ") THEN \"".$addToDaylic."\"\n"

            . " ELSE `count` +".$addToDaylic." \n"
            . "END,\n".

           "time =  ".$today." WHERE `uid`='".$data["uid"]."'  ";
        $db->query($sql);
        $sql = "SELECT * FROM `achievement_data` WHERE uid ='".$data["uid"]."' AND time ='".$today."'";
        $dayly=$db->fletch_assoc($db->query($sql));
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        if($dayly[0]["count"]==DAYLIC_COUNT){
            $xmlresult->addChild("daylyfinish", "true");
            $sql = "UPDATE statistic SET gold = gold + ".GOLD_FOR_DAYLIC." WHERE uid ='".$data["uid"]."'";
            
            $db->query($sql);
        }else{
          $xmlresult->addChild("daylyfinish","false");
        }
        $xmlresult->addChild("error", 0);
        echo $xmlresult->asXml();

    }

    public function daylyTask(){
        $db = DBHolder::GetDB();
        $sql = "SELECT uid FROM `statistic`";
        $sqldata =$db->fletch_assoc($db->query($sql));
        foreach($sqldata as $element){
            $this->_finishTask(array("uid"=>$element["uid"]));
        }

    }

    public  function finishTask(){
        $data =$_REQUEST;
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM statistic WHERE uid = '".$data['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];

        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        if(FINISH_TASK_COST>$user["gold"]){
            $xmlresult->addChild("error",2);
            $xmlresult->addChild("errortext","Недостаточно денег");
            echo $xmlresult->asXML();
            return;
        }
        $sql = "UPDATE statistic SET gold = gold -".FINISH_TASK_COST." WHERE uid ='".$data['uid']."'";
        $db->query($sql);

        $this->_finishTask($data);
        $xmlresult->addChild("error", 0);
        echo $xmlresult->asXml();
    }
    public  function _finishTask($data){
        $db = DBHolder::GetDB();



        $sql = "SELECT * FROM  `achievement_data` WHERE `uid`='".$data["uid"]."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $ids= array($sqldata[0]["task_easy_step"],$sqldata[0]["task_medium_step"],$sqldata[0]["task_hard_step"]);
        $sql = "SELECT * FROM  `achievement_list` LEFT JOIN  `achivement_opened` ON (`achivement_opened`.`aid` = `achievement_list` .`id`AND  `uid` = '".$data["uid"]."') WHERE `previous` IN  (".implode(",",$ids).")";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $task=array();
        foreach($sqldata as $element){
            if($element["amount"]>0){
                switch($element["order"]){
                    case "1":
                        $task[]= "task_easy_step = '".$element["id"]."'";
                        break;
                    case "2":
                        $task[]= "task_medium_step = '".$element["id"]."'";
                        break;
                    case "3":
                        $task[]= "task_hard_step = '".$element["id"]."'";
                        break;
                }
            }

        }
        if($task>0){
            $sql = "UPDATE `achievement_data` SET '".implode(",",$task)."' WHERE uid='".$data["uid"]."'";
             $db->query($sql);
        }

    }
    public  function skipTask(){
        $data =$_REQUEST;
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM statistic WHERE uid = '".$data['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        if(SKIP_TASK_COST>$user["gold"]){
            $xmlresult->addChild("error",2);
            $xmlresult->addChild("errortext","Недостаточно денег");
            echo $xmlresult->asXML();
            return;
        }
        $sql = "UPDATE statistic SET gold = gold -".SKIP_TASK_COST." WHERE uid ='".$data['uid']."'";
        $db->query($sql);



        $sql = "SELECT * FROM  `achievement_list` WHERE id = ".$data["id"];
        $sqldata =$db->fletch_assoc($db->query($sql));
        $task=array();
        foreach($sqldata as $element){
            switch($element["order"]){
                case "1":
                    $task[]= "task_easy_step = '".$element["id"]."'";
                    break;
                case "2":
                    $task[]= "task_medium_step = '".$element["id"]."'";
                    break;
                case "3":
                    $task[]= "task_hard_step = '".$element["id"]."'";
                    break;
            }

        }
        if($task>0){
            $sql = "UPDATE `achievement_data` SET '".implode(",",$task)."'";
            $db->query($sql);
        }

        $xmlresult->addChild("error", 0);
        echo $xmlresult->asXml();
    }
}
?>