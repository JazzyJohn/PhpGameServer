<?php
/**
 * Created by PhpStorm.
 * User: 804129
 * Date: 11.08.14
 * Time: 22:00
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
<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
    Login <input name="login" value=""/><br/>
    Password <input name="pass" value=""/><br/>
    <input name="submit" value="Войти" type="submit"/>
</form>

</body>
<html>