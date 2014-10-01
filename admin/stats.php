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
<form method="POST" action="/stats" enctype="multipart/form-data">
    С: <input  id="date" name="date" value="<?=$_REQUEST["date"]?>"><br/>

    <input type='submit' name="Поиск"><br/>
</form>

Данные с <?=$date?>
<table>
    <tbody>
    <tr>
        <td>Всего игроков</td>
        <td>Игроков которые зашли и убили хотя бы жука</td>
        <td>Дошли до конца матча</td>
        <td>Зашли во второй раз</td>
    </tr>
    <tr>
        <td><?=  $result["summary"][0]["total"]?></td>
        <td><?=  $result["summary"][0]["killbug"]?></td>
        <td><?=  $result["summary"][0]["finishgame"]?></td>
        <td><?=  $result["summary"][0]["secondtime"]?></td>
    </tr>
    </tbody>
</table>
10 самых новых игроков:
<table>
    <tbody>
    <tr>
        <td>Имя:</td>
        <td>Деньги:</td>
        <td>Убийства АИ:</td>
        <td>Дата:</td>
    </tr>
    <?foreach($result["lastuser"] as $element){?>
    <tr>
        <td><a href="http://vk.com/id<?=  $element["uid"]?>"><?=  $element["name"]?></a></td>
        <td><?=  $element["cash"]?> / <?=  $element["gold"]?> </td>
        <td><?=  $element["killai"]?></td>
        <td><?=  $element["datein"]?></td>

    </tr>
    <?}?>
    </tbody>
</table>
</body>
<html>