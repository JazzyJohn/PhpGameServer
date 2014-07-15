<?php
/**
 * Created by PhpStorm.
 * User: 804129
 * Date: 03.06.14
 * Time: 19:14
 */

class AdminController extends BaseController{
    public static $login ="admin";
    public static $pass = "titanfight";

    public function before(){
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="My Admin"');
            header('HTTP/1.0 401 Unauthorized');

            exit;
        }
        if(self::$login!=$_SERVER['PHP_AUTH_USER'] && self::$pass!=$_SERVER['PHP_AUTH_PW']){
            die('Неправильные данные!');
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
        header('Location: /kaspi/one_new?id='.$sqldata[0]["last_insert_id()"]);
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
    header('Location: /kaspi/listofnews');
}

}