<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 16.07.14
 * Time: 12:49
 */


class LoreController extends BaseController{

    public function updatelore(){
        $uid  = $_REQUEST["uid"];
        $sql  ="";
        $ids = array();
        foreach($_REQUEST["index"] as $key=>$element){
            $sql .= "INSERT INTO statistic (`uid`,`blockId`,`alreadyAnalyzed`) VALUES('".$uid."','".$element."','".$_REQUEST["amount"][$key]."')
        ON DUPLICATE KEY UPDATE alreadyAnalyzed = alreadyAnalyzed + ".$_REQUEST["amount"][$key]."   ;";
            $ids[] = $element;

        }
        $db = DBHolder::GetDB();
        $db->query($sql);

    }
    public function loadlore(){
        $uid  = $_REQUEST["uid"];

        $db = DBHolder::GetDB();

        $sql  ="SELECT * FROM `lore_block` INNER JOIN `lore_open_block` ON lore_block.id=lore_open_block.blockId WHERE  lore_open_block.uid = '".$uid."' ";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $text = array();
        foreach($sqldata as $element){
            $text[$element["id"]]['text']=$element["text"];
            $text[$element["id"]]['alreadyAnalyzed']=$element["alreadyAnalyzed"];
        }

        $sql  ="SELECT lore_entry.id,`name`,guiIconWeb,lore_block.id AS blockId,openName,needToOpen,pointModifier FROM `lore_entry` INNER JOIN `lore_block` ON lore_entry.id=lore_block.entryId";
        $sqldata =$db->fletch_assoc($db->query($sql));
        $lore = array();
        foreach($sqldata as $element){
            if(!isset(   $lore[$element["id"]])){
                $lore[$element["id"]]["name"] = $element["name"];
                $lore[$element["id"]]["guiIconWeb"] = $element["guiIconWeb"];
                $lore[$element["id"]]["id"] = $element["entryID"];
            }
            $tar = array();
            $tar["blockId"] = $element["blockId"];
            $tar["openName"]= $element["openName"];
            $tar["needToOpen"]= $element["needToOpen"];
            $tar["pointModifier"]= $element["pointModifier"];
            if(isset($text[$element["blockId"]])){
                $tar["alreadyAnalyzed"] =$text[$element["blockId"]]['alreadyAnalyzed'];

            }else{
                $tar["alreadyAnalyzed"] =0;

            }

            $lore[$element["id"]]["block"][] =$tar;

        }
        $xmllore = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                            <loreData>
							</loreData>');
        $domlore = dom_import_simplexml($xmllore);
        foreach( $lore as $element){
            $entryOne   = new SimpleXMLElement('<loreentry></loreentry>');
            $entryOne->addChild("id",$element["id"]);
            $entryOne->addChild("guiIconWeb",$element["guiIconWeb"]);
            $entryOne->addChild("name",$element["name"]);
            $domone  = dom_import_simplexml($entryOne);

            foreach($element["block"] as $block){
                $blockOne   = new SimpleXMLElement('<loreblock></loreblock>');
                $blockOne->addChild("blockId",$block["blockId"]);
                $blockOne->addChild("openName",$block["openName"]);
                $blockOne->addChild("needToOpen",$block["needToOpen"]);
                $blockOne->addChild("pointModifier",$block["pointModifier"]);
                $blockOne->addChild("alreadyAnalyzed",$block["alreadyAnalyzed"]);
                $domoneblock  = dom_import_simplexml($blockOne);
                $domoneblock  = $domone->ownerDocument->importNode($domoneblock, TRUE);
                $domone->appendChild($domoneblock);
            }

            $domone  = $domlore->ownerDocument->importNode($domone, TRUE);
            $domlore->appendChild($domone);
        }
        echo $xmllore->asXML();

    }


}