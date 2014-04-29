<?php
/**
 * Created by PhpStorm.
 * User: Ivan.Ochincenko
 * Date: 17.04.14
 * Time: 12:23
 */

class Logger{

    //сиглетон
    private static $sigleton= null;
    public static function instance(){
        if(self::$sigleton==null){

            self::$sigleton= new Logger();
        }
        return self::$sigleton;
    }
    private $h;

    static private  $path ="log%s.log";

    public function __construct(){
        $this->h = fopen(sprintf(self::$path,date("d-m-Y")),"a+");

    }

    public function write($log){
        fwrite($this->h,date("H:i:s")." ::: ".$log."\r\n");

    }

}