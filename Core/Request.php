<?php

namespace Core;

final class Request
{
    private readonly array $get;
    private readonly array $post;
    private readonly array $cookie;
    private readonly array $session;
    private readonly array $server;
    private readonly array $files;

    public function __construct()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerClass = $backtrace[1]['class'] ?? null;

        if ($callerClass !== Router::class) {
            throw new \Exception("A classe Request só pode ser instanciada pela classe Router.");
        }

        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookie = $_COOKIE;
        $this->session = $_SESSION;
        $this->server = $_SERVER;
        $this->files = $_FILES;
    }

    public static function isXmlHttpRequest():bool
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : "";
        return (strtolower($isAjax) === 'xmlhttprequest');
    }

    public static function getAllHeaders():array
    {
        $headers = []; 
        foreach ($_SERVER as $name => $value) 
        { 
            if (substr($name, 0, 5) == 'HTTP_') 
            { 
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
            } 
        } 
        return $headers; 
    }

    public function getMethod():string 
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function get(string $var){
        if (isset($this->get[$var]))
            return $this->get[$var];
        else
            return null;
    }

    public function post(string $var){
        if (isset($this->post[$var]))
            return $this->post[$var];
        else
            return null;
    }

    public function cookie(string $var){
        if (isset($this->cookie[$var]))
            return $this->cookie[$var];
        else
            return null;
    }

    public function session(string $var){
        if (isset($this->session[$var]))
            return $this->session[$var];
        else
            return null;
    }

    public function server(string $var){
        if (isset($this->server[$var]))
            return $this->server[$var];
        else
            return null;
    }

    public function file(string $var){
        if (isset($this->files[$var]))
            return $this->files[$var];
        else
            return null;
    }

    public function getArray(){
        return $this->get;
    }

    public function postArray(){
        return $this->post;
    }

    public function cookieArray(){
        return $this->cookie;
    }

    public function sessionArray(){
        return $this->session;
    }

    public function serverArray(){
        return $this->server;
    }

    public function filesArray(){
        return $this->files;
    }

    public function getBody():string
    {
        return file_get_contents('php://input') ?? "";
    }

    public function getBodyAsJson():mixed
    {
        return json_decode($this->getBody());
    }
}