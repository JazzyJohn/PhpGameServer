<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 11.11.14
 * Time: 19:35
 */

class CronController extends BaseController{

    public function dayly(){
        $sql = "UPDATE `achievement_list`  SET open =0  WHERE multiplie=0";
        $db = DBHolder::GetDB();
        $db->query($sql);
        $sql = "SELECT * FROM  `achievement_list`  WHERE multiplie=0";
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

        $sql = "UPDATE `achievement_list`  SET open =1 WHERE id In (".implode(",",$todayIds).")";
        $db->query($sql);

        $sql = "UPDATE `statistic`  SET stamina =1  WHERE stamina=0";

        $db->query($sql);
    }

}