<?php
namespace Core;
use Monolog\Level;
use Monolog\Logger as L;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class Logger 
{
    private static function load(){
        $logger = new L('System');
        $logger->pushHandler(new StreamHandler(Functions::getRoot().'Logs/system.log', Level::Debug));
        $logger->pushHandler(new FirePHPHandler());

        return $logger;
    }

    public static function debug(array|string $message){
        self::load()->debug($message);
    }

    public static function info(array|string $message){
        self::load()->info($message);
    }

    public static function notice(array|string $message){
        self::load()->notice($message);
    }

    public static function warning(array|string $message){
        self::load()->warning($message);
    }
    public static function error(array|string $message){
        self::load()->error($message);
    }

    public static function critical(array|string $message){
        self::load()->critical($message);
    }

    public static function alert(array|string $message){
        self::load()->alert($message);
    }

    public static function emergency(array|string $message){
        self::load()->emergency($message);
    }
}