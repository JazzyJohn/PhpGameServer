<?php
/**
 * Created by PhpStorm.
 * User: 804129
 * Date: 03.06.14
 * Time: 19:18
 */

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>!DOCTYPE</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<ul>
    <?foreach($sqldata as $element){?>
<li>
    <a href="/one_new?id=<?=$element["id"]?>"><?=$element["title"]?></a>  <a href="/deletenews?id=<?=$element["id"]?>">УДАЛИТЬ</a>
</li>

    <?}?>
    <li>
        <a href="/one_new">Добавить новость</a>
    </li>
</ul>

</body>
<html>