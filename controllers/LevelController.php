<?



class LevelController extends AuthController{
	static $CLASSCOUNT =0;

	public function loadlvl(){
		$data =$_REQUEST;
		$sql = "SELECT * FROM `level_count` ORDER BY  exp ASC";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
		$classLvl = array();
		$playerLvl  = array();
		
		self::$CLASSCOUNT =4;
			$xmlleveling= new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
						<leveling>
							<player>							
							</player>
							<classes>
								<classcount>'.self::$CLASSCOUNT .'</classcount>
							</classes>
							<expdictionary>
							</expdictionary>
							<passiveskill></passiveskill>
						</leveling>');
						
		$classcount  = 0;
		$playercount = 0;
					
						
						
		foreach($sqldata as $element){
			if($element["classed"]==1){
			$classcount++;
				$xmlleveling->classes->AddChild("level",$element["exp"]);
			}else{
			$playercount++;
				$xmlleveling->player->AddChild("level",$element["exp"]);
				
			}
			
		}
		$xmlleveling->classes->AddChild("levelcount",$classcount);
		$xmlleveling->player->AddChild("levelcount",$playercount);
		$domaevel = dom_import_simplexml($xmlleveling->expdictionary);
		$sql = "SELECT * FROM `level_dictionary` ";
        $sqldata =$db->fletch_assoc($db->query($sql));
		foreach($sqldata as $element){
		
			$classOne   = new SimpleXMLElement('<slots></slots>');
			$classOne->addChild("value",$element["value"]);
			$classOne->addChild("name",$element["name"]);
			$domone  = dom_import_simplexml($classOne);
	
			$domone  = $domaevel->ownerDocument->importNode($domone, TRUE);
			$domaevel->appendChild($domone);
			
			
		}
		
		$sql = "SELECT * FROM `level_player` WHERE uid = '".$data["uid"]."' ORDER BY  class ASC";
		//$sql = "SELECT * FROM `level_dictionary` ";
        $sqldata =$db->fletch_assoc($db->query($sql));
		$sorted_data = array();
		foreach($sqldata as $element){
			$sorted_data[$element["class"]] = $element;
		}
		if(isset($sorted_data[-1])){
			$xmlleveling->player->AddChild("currentlvl",$sorted_data[-1]["lvl"]);
			$xmlleveling->player->AddChild("currentexp",$sorted_data[-1]["exp"]);
		}else{
			$xmlleveling->player->AddChild("currentlvl",0);
			$xmlleveling->player->AddChild("currentexp",0);
		}
		$domclass = dom_import_simplexml($xmlleveling->classes);
		for($i = 0;$i<self::$CLASSCOUNT =4;$i++){
			$classOne   = new SimpleXMLElement('<current></current>');
			if(isset($sorted_data[$i])){
				$classOne->addChild("lvl",$sorted_data[$i]["lvl"]);
				$classOne->addChild("exp",$sorted_data[$i]["exp"]);
			
			
			}else{
				$classOne->addChild("lvl",0);
				$classOne->addChild("exp",0);
				
			}
		
			$domone  = dom_import_simplexml($classOne);
	
			$domone  = $domclass->ownerDocument->importNode($domone, TRUE);
			$domclass->appendChild($domone);
		}

        $sql = "SELECT * FROM `player_skill` WHERE uid='".$data["uid"]."' ";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $openSkill = array();
        if(isset($sqldata[0]["skills"])){
            $xmlleveling->player->AddChild("skillpoint",$sqldata[0]["skillpoint"]);
            $openSkill = explode(",",$sqldata[0]["skills"]);
        }else{
            $xmlleveling->player->AddChild("skillpoint",0);
        }

        $sql = "SELECT * FROM `skills` ";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $skillbyClass= array();
        foreach($sqldata as $element){
            $skillbyClass[$element["class"]][]=$element;
        }
        $dompassiveskill = dom_import_simplexml($xmlleveling->passiveskill);
        foreach($skillbyClass as $element){
            $classOne   = new SimpleXMLElement('<class></class>');
            $domone  = dom_import_simplexml($classOne);
            foreach($element as $skill){
                $skilOne   = new SimpleXMLElement('<skill></skill>');
                $domskill  = dom_import_simplexml($skilOne);
                $skilOne->addChild("id",$skill["id"]);
                $skilOne->addChild("buff",$skill["buff"]);
                $skilOne->addChild("lvl",$skill["lvl"]);
                $skilOne->addChild("name",$skill["name"]);
                $skilOne->addChild("cash",$skill["cash"]);
                $skilOne->addChild("gold",$skill["gold"]);
                $skilOne->addChild("exp",$skill["skillpoint"]);
                $skilOne->addChild("descr",$skill["description"]);
                $skilOne->addChild("guiimage",$skill["guiimage"]);
                if(in_array($skill["id"],$openSkill)){
                    $skilOne->addChild("open","true");
                }else{
                    $skilOne->addChild("open","false");
                    $skilOne->addChild("condition",$skill["conditionType"]);
                    $skilOne->addChild("openKey",$skill["openKey"]);
                }

                $domskill  = $domone->ownerDocument->importNode($domskill, TRUE);
                $domone->appendChild($domskill);
            }
            $domone  = $dompassiveskill->ownerDocument->importNode($domone, TRUE);
            $dompassiveskill->appendChild($domone);
        }
		header('Content-type: text/xml');
		echo $xmlleveling->asXml();
		
	
	}
	public function savelvl(){
            header('Content-type: text/xml');
			$data =$_REQUEST;
			$sql = "SELECT * FROM `level_player` WHERE uid = '".$data["uid"]."' ORDER BY  class ASC";
				$db = DBHolder::GetDB();
			//$sql = "SELECT * FROM `level_dictionary` ";
			$sqldata =$db->fletch_assoc($db->query($sql));
			$sorted_data = array();
			foreach($sqldata as $element){
				$sorted_data[$element["class"]] = $element;
			}
            $levels_up = array();
			if(isset($sorted_data[-1])){
                if($sorted_data[-1]["lvl"]<$data["playerLvl"]){
                    $levels_up[] = array("class"=>-1,"lvl"=>$data["playerLvl"]);
                }

				$sql = "UPDATE `level_player` SET exp='".$data["playerExp"]."', lvl ='".$data["playerLvl"]."'  WHERE uid = '".$data["uid"]."' AND class = -1;";
			}else{
				$sql = "INSERT INTO `level_player`  (uid,exp,lvl,class) VALUES ('".$data["uid"]."','".$data["playerExp"]."','".$data["playerLvl"]."',-1);";
			}
		
			$db->query($sql);
			for($i = 0;$i<self::$CLASSCOUNT =4;$i++){
				if(isset($sorted_data[$i])){
                    if($sorted_data[$i]["lvl"]<$data["classLvl"][$i]){
                        $levels_up[] = array("class"=>$i,"lvl"=>$data["classLvl"][$i]);
                    }

                    $sql = "UPDATE `level_player` SET exp='".$data["classExp"][$i]."', lvl ='".$data["classLvl"][$i]."'  WHERE uid = '".$data["uid"]."' AND class=".$i.";";
				}else{
					$sql = "INSERT INTO `level_player`  (uid,exp,lvl,class) VALUES ('".$data["uid"]."','".$data["classExp"][$i]."','".$data["classLvl"][$i]."',$i);";
				}
				$db->query($sql);	
			}
			$token = self::getVKAUTH();
			Logger::instance()->write($token );
			$VK = new vkapi(self::$api_id, self::$secret_key);
			


            $resp = $VK->api('secure.setUserLevel', array('uid'=>$data["uid"],'level'=>$data["playerLvl"],"client_secret"=>$token));

			Logger::instance()->write(print_r($resp,true) );

            if(count($levels_up )>0){
                LevelUp::init($levels_up,$data["uid"]);

            }else{
                $xml =new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result></result>');
                echo $xml->asXML();
            }
				
	}

    public function spendskillpoint(){
        header('Content-type: text/xml');
        $data =$_REQUEST;
        $db = DBHolder::GetDB();
        $xmlresult= new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
						<result>
						</result>');
        $sql = "SELECT * FROM  `player_skill`  WHERE uid =\"".$data["uid"]."\"";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $playerskill = $sqldata[0];
        $openSkill = array();
        if(isset($sqldata[0]["skills"])){

            $openSkill = explode(",",$sqldata[0]["skills"]);
        }
        if(in_array($data["id"],$openSkill)){
            $xmlresult->addChild("error",5);
            $xmlresult->addChild("errortext","Ошибка,обратитесь к администратору");
            echo $xmlresult->asXML();
            return;
        }

        $sql = "SELECT * FROM  `skills`  WHERE id =\"".$data["id"]."\"";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $skill = $sqldata[0];

        $sql = "SELECT * FROM statistic WHERE uid = '".$data['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];
 // echo $user["cash"]." ".$skill["cash"]." ".$user["gold"]." ".$skill["gold"]." ".$playerskill." ".$skill["skillpoint"];
        if($user["cash"]<$skill["cash"]||$user["gold"]<$skill["gold"]||$playerskill["skillpoint"]<$skill["skillpoint"]){
            $xmlresult->addChild("error",2);
            $xmlresult->addChild("errortext","Недостаточно денег");
            echo $xmlresult->asXML();
            return;
        }

        $sql = "UPDATE statistic SET cash = cash -".$skill["cash"].",gold = gold -".$skill["gold"]." WHERE uid ='".$data['uid']."'";
        $db->query($sql);

        $sql = "UPDATE `player_skill` SET `skills`=\n"
            . "\n"
            . "CASE \n"
            . " WHEN (`skills`=\"\" ) THEN \"".$data["id"]."\"\n"
            . " ELSE  CONCAT(`skills`,\",".$data["id"]."\")\n"
            . "END\n"            . ",\n"
            . "`skillpoint` =  `skillpoint`- ".$skill["skillpoint"]."\n"

            . " WHERE uid =\"".$data["uid"]."\"";
          $db->query($sql);
        /*
        $sql = "UPDATE `player_skill` SET `skills`=\n"
            . "\n"
            . "CASE \n"
            . " WHEN (`skills`=\"\" AND `skillpoint`>=1) THEN \"".$data["id"]."\"\n"
            . " WHEN (`skills`<>\"\" AND `skillpoint`>=1)THEN CONCAT(`skills`,\",".$data["id"]."\")\n"
            . " ELSE `skills`\n"
            . "END\n"
            . ",\n"
            . "`skillpoint` = \n"
            . "\n"
            . "CASE \n"
            . " WHEN `skillpoint`>=1 THEN `skillpoint`-1\n"
            . " ELSE `skillpoint`\n"
            . "END\n"
            . " WHERE uid =\"".$data["uid"]."\"";*/



        $xmlresult->addChild("error",0);
            $xmlresult->addChild("open","true");



            echo $xmlresult->asXml();
    }
    public function resetSkills(){
        header('Content-type: text/xml');
        $xmlresult= new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
						<result>
						</result>');
        $data =$_REQUEST;
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM `player_skill` WHERE uid='".$data["uid"]."' ";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $playerskill = $sqldata[0];

        $sql = "SELECT * FROM statistic WHERE uid = '".$data['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];
        // echo $user["cash"]." ".$skill["cash"]." ".$user["gold"]." ".$skill["gold"]." ".$playerskill." ".$skill["skillpoint"];
        if($user["gold"]<RESET_SKILL_PRICE){
            $xmlresult->addChild("error",2);
            $xmlresult->addChild("errortext","Недостаточно денег");
            echo $xmlresult->asXML();
            return;
        }

        $sql = "SELECT * FROM  `skills`  WHERE id IN (\"".$playerskill["skills"]."\")";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $exp = 0; $gold=0; $cash = 0;
        foreach($sqldata  as $skill){
            $exp += $skill["skillpoint"];
            $gold += $skill["gold"];
            $cash += $skill["cash"];
        }
        $gold-=RESET_SKILL_PRICE;


        $sql = "UPDATE statistic SET cash = cash +".$cash.",gold = gold +".$gold." WHERE uid ='".$data['uid']."'";
        $db->query($sql);

        $sql = "UPDATE `player_skill` SET `skills`='',"

            . "`skillpoint` =  `skillpoint`+ ".$exp."\n"

            . " WHERE uid =\"".$data["uid"]."\"";
        $db->query($sql);
        $xmlresult->addChild("error",0);
        $xmlresult->addChild("open","true");



        echo $xmlresult->asXml();
    }
}