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
                                case 3:
                                    $sql = "UPDATE statistic SET cash = cash +".$ourItem["amount"]." WHERE uid ='".$receiver_id."'";
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
                                //premium
                                case 2:
                                    $sql = "UPDATE statistic SET premium = 1, premiumEnd =
                                            case
                                            WHEN(premiumEnd < ".time().") THEN ".(time()+60*60*$ourItem["amount"])."
                                            ELSE premiumEnd +".(60*60*$ourItem["amount"])."
                                            END
                                            WHERE uid ='$receiver_id'";
                                    $db->query($sql);

                                    $sql = "INSERT INTO `asyncnotifiers`   (uid,type,params) VALUES ('".$receiver_id."','PREMIUM','".$item."')";
                                    $db->query($sql);
                                  //  Logger::instance()->write(print_r($ourItem["bonuses"],true));
                                    $bonuses = json_decode($ourItem["bonuses"],true);
                                    //Logger::instance()->write(print_r($bonuses,true));
                                    foreach($bonuses as $element){
                                        switch($element["type"]){
                                            case "item":
                                                switch($element["subtype"]){
                                                    case "weapon":
                                                        $sql = "INSERT INTO `player_inventory`   (uid,game_id,personal,time_end,modslot) VALUES ('".$receiver_id."','".$element['game_id']."','0','-1','0')";

                                                        break;
                                                    case "etc":

                                                        $sql = "INSERT INTO `player_inventory`   (uid,game_id,personal,charge,modslot) VALUES ('".$receiver_id."','".$element['game_id']."','0','".$element['charge']."','0')";

                                                        break;
                                                }
                                                $db->query($sql);
                                                break;

                                            case "cash":
                                                $sql = "UPDATE statistic SET cash = cash +".$element["amount"]." WHERE uid ='".$receiver_id."'";
                                                $db->query($sql);
                                                break;

                                            case "gold":
                                                $sql = "UPDATE statistic SET gold = gold +".$element["amount"]." WHERE uid ='".$receiver_id."'";
                                                $db->query($sql);
                                                break;




                                        }
                                    }


                                    break;
                            }
                            $sql = "SELECT * FROM statistic WHERE uid = '".$receiver_id."'";
                            $sqldata =$db->fletch_assoc($db->query($sql));
                            $sqldata = $sqldata[0];
                            $reward = new MoneyReward($sqldata);
                            $reward->userBuy();

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
        header('Content-type: text/xml');
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
                            <inventory></inventory>
							</result>');
        $input = $_REQUEST;
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM game_items_dictionary WHERE uid ='".$input['uid']."' and 	item_id IN (".$input['game_item'].")";
        $sqldata =$db->fletch_assoc($db->query($sql));

        $to_update= array();
        foreach($sqldata as $element){
           

           $to_update[]=$element['id'];

        }
        if(count($to_update)>0){
              $sql = "UPDATE game_items_dictionary SET charge= charge-1 WHERE  uid ='".$input['uid']."' and 	item_id IN (".implode(",",$to_update).")";
              $db->query($sql);
        }


        $itmcontroller = new ItemController();

        $itmcontroller->loadInventory($xmlresult,$input['uid']);

        echo $xmlresult->asXml();
    }

    public function repairItem(){
        header('Content-type: text/xml');
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
                            <inventory></inventory>
							</result>');
        $input = $_REQUEST;
        $db = DBHolder::GetDB();


        $sql = "SELECT * FROM game_items_dictionary AS dic JOIN game_items_players AS fact ON dic.id = fact.item_id JOIN game_items_sets AS sets ON dic.set_id = sets.sid WHERE dic.id ='".$input["game_id"]."' AND fact.uid =  '".$input["uid"]."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        if(count($sqldata)==0){
            $xmlresult->addChild("error",1);
            echo $xmlresult->asXML();
            return;
        }
        $dictionary = $sqldata[0];
        if(0==$dictionary["charge"]){
            $xmlresult->addChild("error",1);
            echo $xmlresult->asXML();
            return;

        }

        $sql = "SELECT * FROM statistic WHERE uid = '".$input['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];


        if($dictionary["charge"]<$input["amount"]){
            $input["amount"] = $dictionary["charge"];
        }
        $price = $input["amount"]*$dictionary["repair_cost"];
        if($user["cash"]<  $price){
            $xmlresult->addChild("error",2);
            $xmlresult->addChild("errortext","Недостаточно денег");
            echo $xmlresult->asXML();
            return;
        }
        $sql = "UPDATE statistic SET cash = cash -".$price." WHERE uid ='".$input['uid']."'";
        $db->query($sql);



        $sql = "UPDATE game_items_players SET charge=charge -". $input["amount"]." WHERE  item_id ='".$input["game_id"]."' AND uid ='".$input['uid']."'";
        $db->query($sql);
        $xmlresult->addChild("error",0);
        $xmlresult->addChild("errortext","");
        $itmcontroller = new ItemController();


        $itmcontroller->loadInventory($xmlresult,$input["uid"]);

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
        $sql = "SELECT * FROM `game_items_price` AS t WHERE id = '".$input['shop_item']."' OR (t.group = (SELECT `group` FROM `game_items_price` WHERE id = '".$input['shop_item']."') AND t.group<> 0) ORDER BY `order`";

        $item_prices =$db->fletch_assoc($db->query($sql));

        if(!isset($item_prices[0])){
            $xmlresult->addChild("error",1);
            $xmlresult->addChild("errortext","Извините лот не найден");
            echo $xmlresult->asXML();
            return;
        }
        $sql = "SELECT * FROM statistic WHERE uid = '".$input['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];



        $sql = "SELECT * FROM game_items_players WHERE item_id = '".$item_prices[0]["inv_id"]."' AND  uid = '".$input['uid']."'";
        $playerinv =$db->fletch_assoc($db->query($sql));
        if(count($playerinv)>0){
            switch($playerinv[0]["buytype"]){
                case "FOR_KP":
                    if($item_prices[0]["type"]=="KP_PRICE"){
                        $xmlresult->addChild("error",5);
                        $xmlresult->addChild("errortext","Ошибка,обратитесь к администратору");
                        echo $xmlresult->asXML();
                        return;
                    }
                    break;
                case "FOR_KP_UNBREAK":

                    $xmlresult->addChild("error",5);
                    $xmlresult->addChild("errortext","Ошибка,обратитесь к администратору");
                    echo $xmlresult->asXML();
                    return;

                    break;
                case "FOR_GOLD_FOREVER":

                    $xmlresult->addChild("error",5);
                    $xmlresult->addChild("errortext","Ошибка,обратитесь к администратору");
                    echo $xmlresult->asXML();
                    return;

                    break;
            }
        }

        if(count($item_prices)==1){


            $item  = $item_prices[0];
            //TODO: DO LOCK;
            $inv_id = $item["inv_id"];
            $price=$item["amount"];
          //  print_r($item);
            switch($item["type"]){
                case "KP_PRICE":
                    if($user["cash"]<  $price){
                        $xmlresult->addChild("error",2);
                        $xmlresult->addChild("errortext","Недостаточно денег");
                        echo $xmlresult->asXML();
                        return;
                    }
                    $sql = "UPDATE statistic SET cash = cash -".$price." WHERE uid ='".$input['uid']."'";
                    $db->query($sql);
                    break;
                  default:

                    if($user["gold"]<  $price){
                        $xmlresult->addChild("error",2);
                        $xmlresult->addChild("errortext","Недостаточно денег ");
                        echo $xmlresult->asXML();
                        return;
                    }
                    $sql = "UPDATE statistic SET gold = gold -".$price." WHERE uid ='".$input['uid']."'";
                    $db->query($sql);
                    break;

            }

        }else{
            $sqls =array();
            foreach( $item_prices as  $item){
                $price=$item["amount"];
                switch($item["type"]){
                    case "KP_PRICE":
                        if($user["cash"]<  $price){
                            $xmlresult->addChild("error",2);
                            $xmlresult->addChild("errortext","Недостаточно денег");
                            echo $xmlresult->asXML();
                            return;
                        }
                        $sqls[] = " cash = cash -".$price."";
                         break;

                    default:

                        if($user["gold"]<  $price){
                            $xmlresult->addChild("error",2);
                            $xmlresult->addChild("errortext","Недостаточно денег ");
                            echo $xmlresult->asXML();
                            return;
                        }
                        $sqls[] = " gold = gold -".$price."";
                        break;
                }
            }

            $sql = "UPDATE statistic SET ".implode(",",$sqls)." WHERE uid ='".$input['uid']."'";
            $db->query($sql);
        }
        $item  = $item_prices[0];
        $sql = "SELECT * FROM game_items_dictionary WHERE id = '".$item["inv_id"]."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $inventory = $sqldata[0];


        $sql = "INSERT INTO `player_purchase`   (uid,item_id,amount,date,currency) VALUES ('".$input['uid']."','".$input['shop_item']."','1','".time()."','".$item["type"]."')";
        $db->query($sql);
        switch($item["type"]){
            case "KP_PRICE":
                $sql ="INSERT INTO game_items_players (`uid`,`item_id`,`buytype`) VALUES('".$input['uid']."','".$inventory["id"]."','FOR_KP')";

                break;
            case "GOLD_PRICE_UNBREAKE":
                $sql ="INSERT INTO game_items_players (`uid`,`item_id`,`buytype`) VALUES('".$input['uid']."','".$inventory["id"]."','FOR_KP_UNBREAK')
                  ON DUPLICATE KEY UPDATE `buytype` = 'FOR_KP_UNBREAK'
                ";

                break;
            case "GOLD_PRICE_FOREVER":
                $sql ="INSERT INTO game_items_players (`uid`,`item_id`,`buytype`) VALUES('".$input['uid']."','".$inventory["id"]."','FOR_GOLD_FOREVER')
                  ON DUPLICATE KEY UPDATE `buytype` = 'FOR_GOLD_FOREVER'
                ";

                break;
            default:
                $day_count=0;
                switch($item["type"]){
                    case "GOLD_PRICE_1":
                        $day_count =GOLD_PRICE_1_DAYS;
                        break;
                    case "GOLD_PRICE_2":
                        $day_count =GOLD_PRICE_2_DAYS;
                        break;
                    case "GOLD_PRICE_3":
                        $day_count =GOLD_PRICE_3_DAYS;
                        break;
                }
                $sql ="INSERT INTO game_items_players (`uid`,`item_id`,`buytype`,`time_end`) VALUES('".$input['uid']."','".$inventory["id"]."','FOR_GOLD_TIME','".($day_count*86400+time())."')
                  ON DUPLICATE KEY UPDATE `buytype` = 'FOR_GOLD_TIME', time_end	 = CASE \n"
                    . " WHEN (`time_end`> ".time().") THEN  time_end	 +'".($day_count*86400)."'\n"
                     . " ELSE   '".(time() + $day_count*86400)."\N'
                     END
                ";
                break;
        }
        $db->query($sql);
        $xmlresult->addChild("error",0);
        $xmlresult->addChild("errortext","");
        echo $xmlresult->asXML();
        $itmcontroller = new ItemController();
        $_REQUEST['game_id']=$inventory['game_id'];
        $itmcontroller->unmarkitem();
        //print_r($data);

        return;
    }
    public function disentegrateItem(){
        $input = $_REQUEST;
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');

        $sql = "SELECT `gold_cost`,`cash_cost` FROM `game_item` WHERE `id` =( SELECT `game_id` FROM `player_inventory` WHERE `id` =".$input["game_id"]." AND `uid` ='".$input["uid"]."') ";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));

        $sql = "UPDATE statistic SET gold = gold +".$sqldata[0]["gold_cost"]." ,cash = cash +".$sqldata[0]["cash_cost"]." WHERE uid ='".$input["uid"]."'";
        $db->query($sql);
        if($sqldata[0]["gold_cost"]!=0||$sqldata[0]["cash_cost"]!=0){
            $xmlresult->addChild("error",0);
            $xmlresult->addChild("gold",$sqldata[0]["gold_cost"]);
            $xmlresult->addChild("cash",$sqldata[0]["cash_cost"]);
            echo $xmlresult->asXML();
            $sql ="DELETE FROM `player_inventory` WHERE `id` =".$input["game_id"]." AND `uid` ='".$input["uid"]."'";
              $db->query($sql);


        }else{
            $xmlresult->addChild("error",1);
            $xmlresult->addChild("errortext","Неизвестный предмет");
            echo $xmlresult->asXML();

        }

    }

    public  function buynextset(){
        header('Content-type: text/xml');
        $data =$_REQUEST;
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM statistic WHERE uid = '".$data['uid']."'";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $user =$sqldata[0];
        $xmlresult = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <result>
							</result>');
        // echo SKIP_TASK_COST;

        $open_price = $GLOBALS["OPEN_SET_PRICE"][$user["open_sid"]+1];


        if($open_price>$user["gold"]){
            $xmlresult->addChild("error",2);
            $xmlresult->addChild("errortext","Недостаточно денег");
            echo $xmlresult->asXML();
            return;
        }

        $sql = "UPDATE statistic SET gold = gold -".$open_price.", open_sid= open_sid + 1 WHERE uid ='".$data['uid']."'";
        $db->query($sql);
        $xmlresult->addChild("error",0);
        $xmlresult->addChild("errortext","");
        $xmlresult->addChild("price",$open_price);
        echo $xmlresult->asXML();
    }
}