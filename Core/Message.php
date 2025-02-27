<?php

namespace Core;
use Core\Abstract\Layout;
use Core\Session;

class Message extends layout{

    public static function clean(){
        Session::set("Error",[]);
        Session::set("Message",[]);
        Session::set("Sucessos",[]);
    }

    public static function getError():array
    {
        return Session::get("Error")?:[];
    }

    public static function setError(...$erros):void
    {
        Session::set("Error",$erros);
    }

    public static function getMessage():array
    {
        return Session::get("Message")?:[];
    }

    public static function setMessage(...$Mensagens):void
    {
        Session::set("Message",$Mensagens);
    }

    public static function getSuccess():array
    {
        return Session::get("Sucessos")?:[];
    }

    public static function setSuccess(...$Sucessos):void
    {
        Session::set("Sucessos",$Sucessos);
    }
}
?>
