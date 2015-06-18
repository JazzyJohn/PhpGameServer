<?php
/**
 * Created by PhpStorm.
 * User: vania_000
 * Date: 03.04.15
 * Time: 11:27
 */
class TwitterApi{
    public static $_LVLMESSAGE = "{1} получил {2} уровень в Red Rage #redrageprogress \n";

    public static $_ACHIVMESSAGE = "{1} получил достижение {2} в Red Rage #redrageprogress \n";

    public static  function postLevel($name,$lvl){

        \Codebird\Codebird::setConsumerKey('0Ge2zqwv9QageIvpzGrSv5unR', 'dTO258zglejE56vfiWpeY3YuoO3zNW5ZL83KvUklx5fcnwaBJy'); // static, see 'Using multiple Codebird instances'

        $cb = \Codebird\Codebird::getInstance();

        $result = str_replace("{1}",$name,self::$_LVLMESSAGE);
        $result = str_replace("{2}",$lvl,$result);
        $cb->setToken("3131497203-uQPUavcJCtZsAZFcUVeOlCjx6CK2AvBFNPbeS7Z", "4Tf5ngmSJEtYeYVmFqniaeJRDxo5WJP2CqF35dfe5bvfj"); // see above

        $reply = $cb->statuses_update('status='.$result);

    }

    public static  function postAchivment($name,$achiv){

        \Codebird\Codebird::setConsumerKey('0Ge2zqwv9QageIvpzGrSv5unR', 'dTO258zglejE56vfiWpeY3YuoO3zNW5ZL83KvUklx5fcnwaBJy'); // static, see 'Using multiple Codebird instances'

        $cb = \Codebird\Codebird::getInstance();

        $result = str_replace("{1}",$name,self::$_ACHIVMESSAGE);
        $result = str_replace("{2}",$achiv,$result);
        $cb->setToken("3131497203-uQPUavcJCtZsAZFcUVeOlCjx6CK2AvBFNPbeS7Z", "4Tf5ngmSJEtYeYVmFqniaeJRDxo5WJP2CqF35dfe5bvfj"); // see above

        $reply = $cb->statuses_update('status='.$result);

    }
}