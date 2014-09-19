<?

class OrderController extends BaseController{

	public function order_call_back(){
		header("Content-Type: application/json; encoding=utf-8"); 
		$input = $_POST; 
		$sig = $input['sig'];
		unset($input['sig']);
		ksort($input);
		$str = '';
		foreach ($input as $k => $v) {
			$str .= $k.'='.$v;
		}
        $response = array();
		if ($sig != md5($str.self::$secret_key)) {
			$response['error'] = array(
				'error_code' => 10,
				'error_msg' => 'Несовпадение вычисленной и переданной подписи запроса.',
				'critical' => true
				);
		} else {
            $db = DBHolder::GetDB();
            switch ($input['notification_type']) {

                case 'get_item':
                case 'get_item_test':
                    // Получение информации о товаре
                    $item = $input['item'];
                    $sql = "SELECT * FROM `items` WHERE item_id = '".$item."'";

                    $sqldata =$db->fletch_assoc($db->query($sql));
                    if(isset($sqldata[0])){
                        $response['response'] = array(
                            'item_id' => $sqldata[0]["item_id"],
                            'title' => $sqldata[0]["title"],
                            'photo_url' => $sqldata[0]["title"],
                            'price' =>  $sqldata[0]["price"]
                        );
                    }else{
                        $response['error'] = array(
                            'error_code' => 20,
                            'error_msg' => 'Товара не существует.',
                            'critical' => true
                        );
                    }
                 break;

                case 'order_status_change':
                case 'order_status_change_test':
                    if ($input['status'] != 'chargeable') {
                        $response['error'] = array(
                            'error_code' => 100,
                            'error_msg' => 'Передано непонятно что вместо chargeable.',
                            'critical' => true
                        );

                    }
                    $item = $input['item'];
                     $sql = "SELECT * FROM `items` WHERE item_id = '".$item."'";
                    $sqldata =$db->fletch_assoc($db->query($sql));
                    if(isset($sqldata[0])){
                        $ourItem   =$sqldata[0];
                        if($input['item_price'] !=  $sqldata[0]["price"]){
                            $response['error'] = array(
                                'error_code' => 20,
                                'error_msg' => 'Неверная цена товара',
                                'critical' => true
                            );

                        }
                        $order_id = intval($input['order_id']);
                        $receiver_id  = intval($input['receiver_id']);
                        $sql = "SELECT * FROM `items_order` WHERE order_id = '".$order_id."'";
                        $sqldata =$db->fletch_assoc($db->query($sql));
                        if(isset($sqldata[0])){
                            $response['response'] = array(
                                'order_id' => $order_id,
                                'app_order_id' => $sqldata[0]["app_order_id	"],
                            );
                        }else{
                            $sql = "INSERT INTO `items_order`  (`uid`,`order_id`,`date_create`,`item_id`)
                                                   VALUES ('".$receiver_id."',".$order_id.",".intval($input['date ']).",'".$item."')";
                            $db->query($sql);
                            $sql = "SELECT last_insert_id();";
                            $sqldata =$db->fletch_assoc($db->query($sql));
                            $response['response'] = array(
                                'order_id' => $order_id,
                                'app_order_id' => $sqldata[0]["last_insert_id()"],
                            );
                            switch($ourItem["type"]){
                                case 0:
                                    $sql = "UPDATE statistic SET gold = gold +".$ourItem["amount"]." WHERE uid ='".$receiver_id."'";
                                    $db->query($sql);
                                    break;
                                case 1:
                                    $sql = "SELECT * FROM `player_opened_gameitem` WHERE uid = '".$receiver_id."' AND itid='".$ourItem["game_item_id"]."'";
                                    $sqldata =$db->fletch_assoc($db->query($sql));
                                    if(isset($sqldata[0])){
                                        if($sqldata[0]["timeend"]!=0){
                                            $sql = "UPDATE `player_opened_gameitem`  SET timeend = timeend +".($ourItem["amount"]*86400)." WHERE uid ='".$receiver_id."'AND itid='".$ourItem['game_item_id']."'";
                                            $db->query($sql);
                                        }

                                    }else{
                                        $sql = "INSERT INTO `player_opened_gameitem`   (uid,timeend,itid) VALUES ('".$receiver_id."','".( time()+$ourItem["amount"]*86400)."','".$ourItem["game_item_id"]."')";
                                        $db->query($sql);
                                    }

                                    break;

                            }

                        }
                    }else{
                    $response['error'] = array(
                        'error_code' => 20,
                        'error_msg' => 'Товара не существует.',
                        'critical' => true
                    );
                }

                break;
             }
        }

    echo json_encode($response);
	
	}

    public function useItem(){
        $input = $_REQUEST;
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM player_inventory WHERE uid ='".$input['uid']."' and game_id IN (".$input['game_item'].")";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $to_delete= array();
        $to_update= array();
        foreach($sqldata as $element){
            if($element["personal"]==1){
                if($element["charge"]>=0){
                    $to_update[]=$element['id'];
                }
            }else{
                if($element["charge"]==0){
                    $to_delete[]=$element['id'];
                }else{
                    $to_update[]=$element['id'];
                }
            }
        }
        $sql = "UPDATE player_inventory SET charge= charge-1 WHERE id IN (".implode(",",$to_update).")";
        $db->query($sql);
        $sql = "DELETE FROM player_inventory WHERE id IN (".implode(",",$to_delete).")";
        $db->query($sql);
    }

    public function repairItem(){
        header('Content-type: text/xml');
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
                            <inventory></inventory>
							</result>');
        $input = $_REQUEST;
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM player_inventory WHERE uid ='".$input['uid']."' and game_id = ".$input["kit_id"]." ";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $delete_id= false;
        $tool= array();
        foreach($sqldata as $element){
            if($element["charge"]>=0){
                $delete_id = $element['id'];
                $tool = $element;
                break;
            }
        }
        if(!$delete_id){
            $xmlresult->addChild("error",1);
            echo $xmlresult->asXML();
            return;
        }

        $sql = "SELECT * FROM player_inventory WHERE id ='".$input["game_id"]."' ";
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(count($sqldata)==0){
            $xmlresult->addChild("error",1);
            echo $xmlresult->asXML();
            return;
        }
        $item  =$sqldata[0];
        $sql = "SELECT * FROM inventory_item_dictionary WHERE game_id ='".$item["game_id"]."' ";
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(count($sqldata)==0){
            $xmlresult->addChild("error",1);
            echo $xmlresult->asXML();
            return;
        }
        $dictionary = $sqldata[0];
        if($item["charge"]==$dictionary["charge"]){
            $xmlresult->addChild("error",1);
            echo $xmlresult->asXML();
            return;

        }
        $setPoint=$item["charge"] +$tool['charge'];
        if($dictionary["charge"]<$setPoint){
            $setPoint =$dictionary["charge"];
        }
        $sql = "UPDATE player_inventory SET charge=".$setPoint." WHERE  id ='".$input["game_id"]."' ";
        $db->query($sql);
        $sql = "DELETE FROM player_inventory WHERE id = ".$delete_id."";
        $db->query($sql);
        $itmcontroller = new ItemController();
        $sql = 'SELECT `player_inventory` . * , `game_item`.ingamekey, `inventory_item_dictionary`.class, `inventory_item_dictionary`.type, `inventory_item_dictionary`.charge AS maxcharge, `inventory_item_dictionary`.shopicon, `inventory_item_dictionary`.description, `inventory_item_dictionary`.name, `inventory_item_dictionary`.model
FROM `player_inventory`
LEFT JOIN `inventory_item_dictionary` ON `player_inventory`.game_id = `inventory_item_dictionary`.game_id
LEFT JOIN `game_item` ON `player_inventory`.game_id = `game_item`.id WHERE uid="'.$input["uid"].'"';

        $playerInventory =$db->fletch_assoc($db->query($sql));
        $itmcontroller->loadInventory($xmlresult,$playerInventory);

        echo $xmlresult->asXml();
    }
    /*
    public function buyItem(){
        $input = $_REQUEST;
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM `game_item` WHERE id = '".$input['game_item']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(!isset($sqldata[0])){
            $xmlresult->addChild("error",1);
            $xmlresult->addChild("errortext","item not found");
            echo $xmlresult->asXML();
            return;
        }
        $item = $sqldata[0];
        $sql = "SELECT * FROM statistic WHERE uid = '".$input['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];

        if($input["forGold"]=="True"){
            $price= $item["gold_cost"];
            if($user["gold"]<  $price){
                $xmlresult->addChild("error",2);
                $xmlresult->addChild("errortext","not enough money ");
                echo $xmlresult->asXML();
                return;
            }
            $sql = "UPDATE statistic SET gold = gold -".$price." WHERE uid ='".$input['uid']."'";
            $db->query($sql);
        }else{
            $price= $item["cash_cost"];
            if($item["cash_cost"]==0){
                $xmlresult->addChild("error",3);
                $xmlresult->addChild("errortext","no cash price");
                echo $xmlresult->asXML();
                return;
            }
            if($user["cash"]<  $price){
                $xmlresult->addChild("error",2);
                $xmlresult->addChild("errortext","not enough money ");
                echo $xmlresult->asXML();
                return;
            }
            $sql = "UPDATE statistic SET cash = cash -".$price." WHERE uid ='".$input['uid']."'";
            $db->query($sql);
        }

        $sql = "INSERT INTO `player_purchase`   (uid,item_id,amount,date,currency) VALUES ('".$input['uid']."','".$input['game_item']."','1','".time()."','".($input["forGold"]==true?1:0)."')";
        $db->query($sql);
        switch($item["type"]){
            case 0:
                $sql = "SELECT * FROM `player_opened_gameitem` WHERE uid = '".$input['uid']."' AND itid='".$input['game_item']."'";
                $sqldata =$db->fletch_assoc($db->query($sql));
                if(isset($sqldata[0])){
                    if($sqldata[0]["timeend"]!=0){
                        $sql = "UPDATE `player_opened_gameitem`  SET timeend = timeend +".(86400)." WHERE uid ='".$input['uid']."' AND itid='".$input['game_item']."'";
                        $db->query($sql);
                    }

                }else{
                    $sql = "INSERT INTO `player_opened_gameitem`   (uid,timeend,itid) VALUES ('".$input['uid']."','".( time()+86400)."','".$input['game_item']."')";
                    $db->query($sql);
                }
                break;

            case 1:
                $sql = "INSERT INTO player_game_items_amount (`uid`,`id`,`amount`) VALUES('".$input['uid']."','".$input['game_item']."',1)
        ON DUPLICATE KEY UPDATE amount = amount + 1   ;";

                $db->query($sql);
                break;

        }

        $xmlresult->addChild("error",0);
        $xmlresult->addChild("errortext","");
        echo $xmlresult->asXML();
        return;

    }
    */
    public function buyItem(){
        $input = $_REQUEST;
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM `shop_items` WHERE id = '".$input['shop_item']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(!isset($sqldata[0])){
            $xmlresult->addChild("error",1);
            $xmlresult->addChild("errortext","item not found");
            echo $xmlresult->asXML();
            return;
        }
        $item = $sqldata[0];
        //TODO: DO LOCK;
        $sql = "SELECT * FROM statistic WHERE uid = '".$input['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];
        $price=$item["price"];
        switch($item["priceType"]){
            case "KP":
                if($user["cash"]<  $price){
                    $xmlresult->addChild("error",2);
                    $xmlresult->addChild("errortext","not enough money ");
                    echo $xmlresult->asXML();
                    return;
                }
                $sql = "UPDATE statistic SET cash = cash -".$price." WHERE uid ='".$input['uid']."'";
                $db->query($sql);
                break;
            case "GTIP":

                if($user["gold"]<  $price){
                    $xmlresult->addChild("error",2);
                    $xmlresult->addChild("errortext","not enough money ");
                    echo $xmlresult->asXML();
                    return;
                }
                $sql = "UPDATE statistic SET gold = gold -".$price." WHERE uid ='".$input['uid']."'";
                $db->query($sql);
                break;
        }
        $sql = "SELECT * FROM inventory_item_dictionary WHERE id = '".$item["inv_id"]."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $inventory = $sqldata[0];


        $sql = "INSERT INTO `player_purchase`   (uid,item_id,amount,date,currency) VALUES ('".$input['uid']."','".$input['shop_item']."','1','".time()."','".($item["priceType"]=="KP"?1:0)."')";
        $db->query($sql);
        switch($item["priceType"]){
            case "KP":
                 switch($inventory["type"]){
                     case 'ETC':
                         $sql = "INSERT INTO `player_inventory`   (uid,game_id,personal,time_end,modslot) VALUES ('".$input['uid']."','".$inventory['game_id']."','0','".$inventory['charge']."','".$inventory['modslot']."')";
                         break;
                     default:

                         $sql = "INSERT INTO `player_inventory`   (uid,game_id,personal,time_end,modslot) VALUES ('".$input['uid']."','".$inventory['game_id']."','0','".(time()+86400*$inventory['time_end'])."','".$inventory['modslot']."')";
                 }
                 break;
            case "GTIP":
                switch($inventory["type"]){
                    case 'ETC':
                        $sql = "INSERT INTO `player_inventory`   (uid,game_id,personal,charge,modslot) VALUES ('".$input['uid']."','".$inventory['game_id']."','0','".$inventory['charge']."','".$inventory['modslot']."')";
                        break;
                    default:

                        $sql = "INSERT INTO `player_inventory`   (uid,game_id,personal,charge,modslot) VALUES ('".$input['uid']."','".$inventory['game_id']."','1','".$inventory['charge']."','".$inventory['modslot']."')";
                        break;
                }
                break;
        }
        $db->query($sql);
        $xmlresult->addChild("error",0);
        $xmlresult->addChild("errortext","");
        echo $xmlresult->asXML();
        return;
    }
}