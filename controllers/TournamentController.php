<?php
/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 07.12.14
 * Time: 23:41
 */

class TournamentController extends AuthController{

    public function saveoperation(){

        $data =$_REQUEST;
        $sql = "INSERT INTO operation_players (`uid`,`oid`,`counter`) VALUES('".$data["uid"]."','".$data["oid"]."','".$data["points"]."')
        ON DUPLICATE KEY UPDATE counter = counter + ".$data["points"]."   ;";
        $db = DBHolder::GetDB();
        $db->query($sql);



    }

}