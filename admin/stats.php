<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 01.10.14
 * Time: 17:51
 */


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>!DOCTYPE</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">

    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
    <script>
        $(function() {
            $("#date").datepicker();
        });
    </script>
</head>
<body>
<form method="GET" action="/stats" enctype="multipart/form-data">
    С: <input  id="date" name="date" value="<?=$_REQUEST["date"]?>"><br/>

    <input type='submit' name="Поиск"><br/>
</form>

Данные с <?=$date?>
<table border="1">
    <tbody>
    <tr>
        <td>Всего игроков</td>
        <td>Игроков которые зашли и убили хотя бы жука</td>
        <td>Дошли до конца матча</td>
        <td>Зашли во второй раз</td>
    </tr>
    <tr>
        <td><?=  $result["summary"][0]["total"]?></td>
        <td><?=  $result["summary"][0]["KillBug"]?></td>
        <td><?=  $result["summary"][0]["FinishGame"]?></td>
        <td><?=  $result["summary"][0]["SecondTime"]?></td>
    </tr>
    </tbody>
</table>
10 самых новых игроков:
<table border="1">
    <tbody>
    <tr>
        <td>Имя:</td>
        <td>Деньги:</td>
        <td>Убийства АИ:</td>
        <td>Дата:</td>
    </tr>
    <?foreach($result["lastuser"] as $element){?>
    <tr>
        <td><a target="_blank" href="http://vk.com/id<?=  $element["UID"]?>"><?=  $element["NAME"]?></a></td>
        <td><?=  $element["cash"]?> / <?=  $element["gold"]?> </td>
        <td><?=  $element["killAi"]?></td>
        <td><?=  $element["dateIn"]?></td>

    </tr>
    <?}?>
    </tbody>
</table>
</body>
<html>