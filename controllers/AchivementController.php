<?

class AchivementController extends BaseController{

	public function loadachive(){
		header('Content-type: text/xml');
		$sql = "SELECT id,achievement_list.name,description, icon,achivement_params.name AS paramname,needvalue FROM `achievement_list` INNER JOIN `achivement_params` ON achievement_list.id=achivement_params.aid WHERE achievement_list.open = 1";
		$data =$_REQUEST;
        $db = DBHolder::GetDB();
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
        $sql = "SELECT * FROM `achievement_daylyrecord` WHERE uid ='".$data["uid"]."' AND time ='".strtotime(date("d-m-Y",time()))."'";
        $dayly=$db->fletch_assoc($db->query($sql));



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

			if(isset($open[$element["id"]])){
                if($element["multiplie"]==1){
                    if($open[$element["id"]]['time']+86400<time()){
                        $achOne->addChild("ready","true");
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
                $achOne->addChild("multiplie",$element["multiplie"]);
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
        }
		echo $xmlachiv->asXml();
	}
	public function saveachive(){
        $data =$_REQUEST;
        $array = array();
        $today = strtotime(date("d-m-Y",time()));
        foreach($data["ids"] as $element){
            $array[] = "('".$data["uid"]."',".$element.",'1','".$today."')";
        }
        $db = DBHolder::GetDB();
            $sql = "INSERT INTO `achivement_opened` (`uid`,`aid`,`amount`,`time`)  VALUES ".implode(",",$array)."  ON DUPLICATE KEY UPDATE amount = amount+1, time = ".$today;
        $db->query($sql);
        $sql = "SELECT * FROM `achievement_list` WHERE id IN (".implode(",",$data["ids"] ).")";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $addToDaylic = 0;
        $addcash = 0;
        foreach($sqldata as $element){
            if($element["multiplie"]){
                $addToDaylic++;


            }
            $addcash+= $element["cashreward"];
        }
        $sql = "UPDATE statistic SET cash = cash +".$addcash." WHERE uid ='".$data["uid"]."'";

        $db->query($sql);
        $sql = "INSERT INTO `achievement_daylyrecord` (`uid`,`time`,`count`)  VALUES ('".$data["uid"]."','".$today."',".$addToDaylic.")  ON DUPLICATE KEY UPDATE count = count+ ".$addToDaylic."";
        $db->query($sql);
        $sql = "SELECT * FROM `achievement_daylyrecord` WHERE uid ='".$data["uid"]."' AND time ='".$today."'";
        $dayly=$db->fletch_assoc($db->query($sql));
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        if($dayly[0]["count"]==10){
            $xmlresult->addChild("daylyfinish", "true");
            $sql = "UPDATE statistic SET gold = gold + 1 WHERE uid ='".$data["uid"]."'";

            $db->query($sql);
        }else{
          $xmlresult->addChild("daylyfinish","false");
        }
        $xmlresult->addChild("error", 0);
        echo $xmlresult->asXml();

    }
}
?>