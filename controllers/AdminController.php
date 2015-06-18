<?php
/**
 * Created by PhpStorm.
 * User: 804129
 * Date: 03.06.14
 * Time: 19:14
 */

class AdminController extends BaseController{
    public static $login ="admin";
    public static $pass = "nightguard";

    public function before(){
        session_start();


        //print_r(self::$pass);
        if(self::$login!=$_SESSION['login'] || self::$pass!=$_SESSION['pass']){
            if(isset($_REQUEST["login"])&&isset($_REQUEST["pass"])&&self::$login==$_REQUEST['login'] && self::$pass==$_REQUEST['pass']){
                $_SESSION['login']=$_REQUEST["login"];
                $_SESSION['pass'] =$_REQUEST['pass'];

            }else{
                require_once(__DIR__."/../admin/form.php");
                exit;
            }

        }
        return true;
    }

    public function notifyUsers(){
        $data =$_REQUEST;
        if(!isset($data["message"])){
            include "static/form.php";
            return;
        }

        $sql = "SELECT uid FROM notify WHERE version<".$data['version']. " LIMIT 0,".$data["number"];
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $token = self::getVKAUTH();
        Logger::instance()->write($token );
        $VK = new vkapi(self::$api_id, self::$secret_key);
        $i=0;

        while($i<count($sqldata)){
            $uids =array();
            $sqlid= array();
            for(;$i<count($sqldata);$i++){
                if($sqldata[$i]['uid']>0){
                    $uids[] = $sqldata[$i]['uid'];
                    $sqlid[]="'". $sqldata[$i]['uid']."'";
                    if(count($uids)>=99){
                        break;
                    }
                }
            }


            $resp = $VK->api('secure.sendNotification', array('user_ids'=>implode(",",$uids),'message'=>$data["message"],"client_secret"=>$token));
            $sql = "UPDATE notify SET version = ".$data['version']." WHERE uid IN (".implode(",",$sqlid).")";
            $db = DBHolder::GetDB();
            $db->query($sql);
            Logger::instance()->write(print_r($resp,true) );
            print_r($resp);
        }





    }

    public function listofnews(){


        $sql = "SELECT title,id FROM `news`";

        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        require_once(__DIR__."/../admin/list_of_news.php");

    }
    public function one_new(){
        if(isset($_REQUEST["id"])){
            $sql = "SELECT * FROM `news` WHERE id='".$_REQUEST["id"]."'";

            $db = DBHolder::GetDB();
            $sqldata =$db->fletch_assoc($db->query($sql));
            $new = $sqldata[0];
          //  print_r($new);
            require_once(__DIR__."/../admin/one_news.php");

        }else{
            require_once(__DIR__."/../admin/add_news.php");

        }

    }
    public function  addnews(){
        $uploads_dir = dirname(__DIR__)."/gameRes/News";

        $file =$_FILES["img"];
            if ($file["error"] == UPLOAD_ERR_OK) {
                $tmp_name =$file["tmp_name"];
                $name =$file["name"];
                $url ="/gameRes/News/".$name;
                $fileName = "$uploads_dir/$name";

                move_uploaded_file($tmp_name,$fileName);
            }else{
                exit;
            }


        $title = addslashes($_REQUEST["title"]);
        $text = addslashes($_REQUEST["text"]);
        $sql = "INSERT INTO `news` (`title`,`text`,`image`)  VALUES ('".$title."','".$text."','".$url."')";
        $db = DBHolder::GetDB();
        $db->query($sql);
        $sql = "SELECT last_insert_id();";
        $sqldata =$db->fletch_assoc($db->query($sql));
        header('Location: /one_new?id='.$sqldata[0]["last_insert_id()"]);
    }

    public function stats(){
        $date = (isset($_REQUEST["date"])&&$_REQUEST["date"]!="")?date("Y-m-d", strtotime($_REQUEST["date"])):date("Y-m-d", time() -60*60*24*7);
        $sql = "select \n"
            . " count(*) total,\n"
            . " sum(case when ingameenter >0 then 1 else 0 end) SecondTime,\n"
            . " sum(case when  uid IN ( SELECT uid FROM `level_player` WHERE exp >0 ) then 1 else 0 end) FinishGame,\n"
            . " sum(case when killAi> 0 then 1 else 0 end) KillBug\n"
            . "\n"
            . "\n"
            . "from statistic WHERE dateIn > \"$date 00:00:00\"";
        $db = DBHolder::GetDB();
       // $db->query($sql);



        $result["summary"]  =$db->fletch_assoc($db->query($sql));
        $sql = "SELECT * FROM `statistic`\n"
            . "ORDER BY `statistic`.`dateIn` DESC LIMIT 0, 10 ";
        $db = DBHolder::GetDB();
        $db->query($sql);
        $result["lastuser"]  =$db->fletch_assoc($db->query($sql));
        require_once(__DIR__."/../admin/stats.php");
    }
    public function  savenews(){

        $color = addslashes($_REQUEST["color"]);
        $fontsize = addslashes($_REQUEST["fontsize"]);
        $text = addslashes($_REQUEST["text"]);
        $textX = addslashes($_REQUEST["textX"]);
        $textY = addslashes($_REQUEST["textX"]);
        $sql = "UPDATE `news` SET `text`='".$text."', `color`='".$color."' ,`fontsize`='".$fontsize."', `textX`='".$textX."', `textY`='".$textY."' WHERE id='".$_REQUEST["id"]."'";
        $db = DBHolder::GetDB();
        $db->query($sql);


    }
    public function  deletenews(){
    if(isset($_REQUEST["id"])){
        $sql = "DELETE FROM  `news`WHERE id='".$_REQUEST["id"]."'";
        $db = DBHolder::GetDB();
        $db->query($sql);

    }
    header('Location: /listofnews');
}

    public function daylicfix(){
        $db = DBHolder::GetDB();
        $sql = "SELECT * FROM `achievement_daylyrecord`";
        $dayly=$db->fletch_assoc($db->query($sql));
        $data= array();
        foreach($dayly as $element){
            if(!isset($data[$element["uid"]])){
                $data[$element["uid"]]=0;
            }
            $data[$element["uid"]]+=$element["count"];
        }
        foreach($data as $key=>$element){
            $sql = "UPDATE statistic SET daylicCnt=".$element."  WHERE uid ='".$key."'";
            $db->query($sql);

        }
        print_r($data);
    }
    public function twitterTest(){
        TwitterApi::postLevel("test",10);
    }
    public function  operations(){
        $db = DBHolder::GetDB();
        switch($_REQUEST["action"]){

            case "finish":
                $sql = "SELECT * FROM operation WHERE status =3";
                $operations =$db->fletch_assoc($db->query($sql));
                if(count($operations)==0){
                    echo "Нет операции для отправки в бой невозможно завершить текущую";

                }else{

                    echo "Операция ".$operations[0]["name"]. " отправлена в бой<br>";



                    $sql = "SELECT * FROM operation WHERE status =0";
                    $operations =$db->fletch_assoc($db->query($sql));
                    $operation =$operations[0];
                    echo "Операция ".$operation["name"]. " завершена<br>";
                    $sql = "SELECT * FROM operation WHERE status =1";
                    $operations =$db->fletch_assoc($db->query($sql));
                    if(count($operations)!=0){
                        $lastoperation= $operations[0];
                        echo "Операция ".$lastoperation["name"]. " отправлена в архив<br>";

                    }
                    $sql = "UPDATE operation SET status=2  WHERE status = 1";
                    $db->query($sql);
                    $sql = "UPDATE operation SET status=1  WHERE status = 0";
                    $db->query($sql);
                    $sql = "UPDATE operation SET status=0  WHERE status = 3";
                    $db->query($sql);

                    $sql = "SELECT * FROM operation_players WHERE oid = '".$operation["id"]."' ORDER BY counter  DESC ";
                    $winners =$db->fletch_assoc($db->query($sql));
                    $cashReward = explode(",",$operation["cashReward"]);
                    $skillReward = explode(",",$operation["expReward"]);
                    $goldReward = explode(",",$operation["goldReward"]);
                    for($i=0;$i<=$operation["prizeplaces"];$i++){
                        echo "Место ".$i. " <a href='http://vk.com/id".$winners[$i]["uid"]."'>".$winners[$i]["uid"]."</a> +".$cashReward[$i]. " кредитов +".$goldReward[$i]." золота + ".$skillReward[$i]." скиллпоинтов<br>";

                        $sql = "UPDATE statistic SET cash = cash +".$cashReward[$i].", gold = gold+ ".$goldReward[$i]." WHERE uid ='".$winners[$i]["uid"]."'";
                        echo $sql." <br>";
                        $db->query($sql);
                        $sql = "INSERT INTO player_skill (`uid`,`skillpoint`) VALUES('".$winners[$i]["uid"]."',".$skillReward[$i].")
                 ON DUPLICATE KEY UPDATE skillpoint  = skillpoint +".$skillReward[$i]."  ;";
                        ;
                        $db->query($sql);
                    }

                  /*  for($i= $operation["prizeplaces"]; $i<count($winners);$i++){
                        echo "Место ".$i. " <a href='http://vk.com/id".$winners[$i]["uid"]."'>".$winners[$i]["uid"]."</a> +".$cashReward[$operation["prizeplaces"]]. " кредитов +".$goldReward[$operation["prizeplaces"]]." золота <br>";
                        $uids[] ="'".$winners[$i]["uid"]."'";


                    }
                    $sql = "UPDATE statistic SET cash = cash +".$cashReward[$operation["prizeplaces"]].", gold = gold+ ".$goldReward[$operation["prizeplaces"]]." WHERE uid IN (".implode(",",$uids).")";
                    echo $sql;
                    $db->query($sql);*/
                }


                break;

        }
        $sql = "SELECT * FROM operation ";
        $operations =$db->fletch_assoc($db->query($sql));

        require_once(__DIR__."/../admin/operation.php");
    }
    public function badInventor(){
        $db = DBHolder::GetDB();
        $sql = "SELECT uid,item_id FROM `game_items_players` ORDER BY uid ASC, item_id ASC";
        $db->query($sql);
        $bad= array();
        $lastUid = -1;
        $all =$db->fletch_assoc($db->query($sql));
        foreach($all as $element){

            if($lastUid!=$element["uid"] &&$element["item_id"]!=1){
                $bad[] = $element["uid"];
            }
            $lastUid=$element["uid"];
        }
        print_r($bad);
        foreach($bad as $element){
            $sql = ' INSERT INTO `game_items_players`  (`uid`,`item_id`,`buytype`) VALUES ("'.$element.'","'.NEWBIE_PISTOL.'","FOR_KP")';
            $db->query($sql);

        }

    }

}