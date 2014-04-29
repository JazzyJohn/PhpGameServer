<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 16.04.14
 * Time: 14:36
 */

class DBHolder
{
        private static $base_name  = "bh51020_kaspigame_db";

        private static $base_host  = "91.219.195.42";

        private static $base_user  ="bh51020_kaspi";

        private static $base_psw  ="I8P&TZ(?qNzR";

        private static $DB = null;

        private $link;

        static public  function GetDB(){
            if(self::$DB==null){
                self:: $DB = new DBHolder();
            }
            return  self:: $DB ;
        }
        public function __construct(){
            $this->link = mysql_connect(self::$base_host, self::$base_user,self::$base_psw);
            if (! $this->link) {
                die('Ошибка соединения: ' . mysql_error());
            }

            mysql_select_db(self::$base_name, $this->link);
			mysql_set_charset('utf8',$this->link);
        }
        public function query($sql){
            $rid =  mysql_query($sql,$this->link);
            Logger::instance()->write($sql);
			$error =mysql_error ($this->link);
			if($error!=""){
                Logger::instance()->write($error);
				echo $error;
			}
			return $rid;
        }
        public function fletch_assoc($rid){
            $answer = array();
            while($data = mysql_fetch_assoc($rid)){
                $answer[]= $data;
            }
            Logger::instance()->write(print_r($answer,true));
            return $answer;
        }



}