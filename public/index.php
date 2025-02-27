<?php
require '../bootstrap.php';

use Core\Router;
use Core\Session;
use App\View\Layout\Error;
use App\View\Layout\Head;
use Core\Logger;
use Core\Response;

Session::start();
date_default_timezone_set('America/Sao_Paulo');

$whoops = new \Whoops\Run;
if ($_ENV["ENVIRONMENT"] !== "prod") {
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
} else {
    $whoops->pushHandler(function($e){
        Logger::error('Error: '.$e->getMessage().' Trace: '.$e->getTraceAsString());
        $response = new Response;
        $response->setCode($e->getCode()?:500);
        $response->addContent(new Head("Error"));
        $response->addContent(new Error($e->getCode()?:500,!$e->getCode()?"Erro ao processar requisição":$e->getMessage()));
        $response->send();
    });
}
$whoops->register();

(new Router)->load();

?>