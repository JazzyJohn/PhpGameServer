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
    <?foreach($operations as $element){?>
        <li>
            <?=$element["name"]?>
            <?
            Switch($element["status"]){
                case 0:
                    echo "текущая";
                    break;
                case 1:
                    echo "прошлая";
                    break;
                case 2:
                    echo "архив";
                    break;
                case 3:
                    echo "подготавливается к запуску";
                    break;
            }
            ?>
        </li>

    <?}?>

</ul>
<a href="/operations?action=finish">Закончить операцию</a>
</body>
<html>