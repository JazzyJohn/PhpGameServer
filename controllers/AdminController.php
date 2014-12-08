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

}