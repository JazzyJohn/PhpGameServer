<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 11.11.14
 * Time: 19:35
 */

class CronController extends BaseController{

    public function dayly(){

        Logger::instance()->write("REMOTE_ADDR cron " .$_SERVER["REMOTE_ADDR"]);
        if($_SERVER["REMOTE_ADDR"]!=LOCAL_ADDR){
           echo"invaders must die";
            exit;

        }


        $sql = "SELECT * FROM  `achievement_list`  WHERE multiplie=1";
        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $idsArray = array();
        foreach($sqldata as $element){
            $idsArray[] = $element["id"];
        }

        $todayIds = array();
        $everyday = explode(",",EVERUDAY_ACHIV);
        foreach($everyday as $id){
            $todayIds[] =$id;
            $key = array_search ($id,$idsArray);
            unset($idsArray[$key]);
        }
       While(count($todayIds)<DAYLIC_COUNT){

           $k = array_rand($idsArray);
           $v = $idsArray[$k];
           unset($idsArray[$k]);
           $todayIds[] =$v;

       }
        Logger::instance()->write("set new daylic " .print_r($todayIds,true));
        $sql = "UPDATE `achievement_list`  SET open =
        CASE
        WHEN  id IN (".implode(",",$todayIds).") THEN 1
        ELSE 0
        END
        WHERE multiplie = 1

        ;";
        $db->query($sql);
        Logger::instance()->write("set new stamina ");
        $sql = "UPDATE `statistic`  SET stamina =1  WHERE stamina=0";

        $db->query($sql);
    }

}