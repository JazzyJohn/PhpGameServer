<?

class AchivementController extends BaseController{

	public function loadachive(){
		header('Content-type: text/xml');
		$sql = "SELECT id,achievement_list.name,description, achivement_params.name AS paramname,needvalue FROM `achievement_list` INNER JOIN `achivement_params` ON achievement_list.id=achivement_params.aid";
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
		
		
      
		$sql = "SELECT * FROM `achivement_opened` WHERE uid ='".$data["uid"]."'";
		$sqldata =$db->fletch_assoc($db->query($sql));
		$open = array();
		foreach( $sqldata as $element){
			$open[] =$element["aid"];
		}
		$xmlachiv = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><achivements></achivements>');
		$domachiv = dom_import_simplexml($xmlachiv);
		foreach($achivmnets as $element){
			$achOne   = new SimpleXMLElement('<achivement></achivement>');
			$achOne->addChild("id",$element["id"]);
			$achOne->addChild("name",$element["name"]);
			$achOne->addChild("description",$element["description"]);
			if(in_array($element["id"],$open)){
				$achOne->addChild("open","true");
			}else{
					$achOne->addChild("open","false");
			}
			$domone  = dom_import_simplexml($achOne);
		
			foreach($element["PARAMS"] as $parms){
					$paramOne   = new SimpleXMLElement('<param></param>');
					$paramOne->addChild("name",$parms["name"]);
					$paramOne->addChild("value",$parms["value"]);
					$domoneparam  = dom_import_simplexml($paramOne);
					$domoneparam  = $domone->ownerDocument->importNode($domoneparam, TRUE);
					$domone->appendChild($domoneparam);
			}
			$domone  = $domachiv->ownerDocument->importNode($domone, TRUE);
			$domachiv->appendChild($domone);
		}
	
		echo $xmlachiv->asXml();
	}
	public function saveachive(){
			$data =$_REQUEST;
			$array = array();
			foreach($data["ids"] as $element){
				$array[] = "('".$data["uid"]."',".$element.")";
			}
			$db = DBHolder::GetDB();
				$sql = "INSERT INTO `achivement_opened` (`uid`,`aid`)  VALUES ".implode(",",$array);
			$db->query($sql);
				
	}
}
?>