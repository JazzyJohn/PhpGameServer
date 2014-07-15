<?php
/**
 * Created by PhpStorm.
 * User: 804129
 * Date: 03.06.14
 * Time: 22:19
 */

class NewsController extends BaseController{


    public function allnews(){
        header('Content-type: text/xml');
        $sql = "SELECT * FROM `news`";

        $db = DBHolder::GetDB();
        $sqldata =$db->fletch_assoc($db->query($sql));
        $xmlnews= new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><allnews></allnews>');
        $domnews = dom_import_simplexml($xmlnews);
        foreach($sqldata as $element){
            $newOne   = new SimpleXMLElement('<new></new>');
            $newOne->addChild("id",$element["id"]);
            $newOne->addChild("title",$element["text"]);
            $newOne->addChild("img",$element["image"]);
            $newOne->addChild("titleX", round ( $element["textX"],2));
            $newOne->addChild("titleY",round ( $element["textY"],2));
            $newOne->addChild("fontsize",$element["fontsize"]);
            $newOne->addChild("color",$element["color"]);
            $domone  = dom_import_simplexml($newOne);


            $domone  = $domnews->ownerDocument->importNode($domone, TRUE);
            $domnews->appendChild($domone);
        }

        echo $xmlnews->asXml();
    }

}
