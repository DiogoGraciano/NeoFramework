<?php
namespace Core;

use App\View\Layout\Error;
use Core\Attributes\Route;
use App\View\Layout\Head;
use Core\Attributes\Middleware;
use DI\Container;
use Core\Container as CoreContainer;
use Exception;
use ReflectionAttribute;
use ReflectionClass;

final class Router{
   
    private string $uri;

    private array $folders = [];

    private string $namespace;

    private string $controller;

    private Container $container;

    public function __construct()
    {
        $this->uri = Url::getUriPath();
        $this->container = (new CoreContainer())->load();
        $this->getFolders();
    }

    private function getFolders(){
        $pasta = substr(__DIR__, 0, -5).DIRECTORY_SEPARATOR."App".DIRECTORY_SEPARATOR."Controllers";
        $arquivos = scandir($pasta);
        foreach ($arquivos as $arquivo) {
            if (!str_contains($arquivo, '.'))
                $this->folders[] = "App\Controllers\\".$arquivo;
        }
    }

    public function getNamespace(){
        return $this->namespace;
    }

    public function load(string|bool $controller = false){

        if ($controller){
            return $this->controllerSet($controller); 
        }
        
        if($this->isHome())
            return $this->controllerHome();
        
        return $this->controllerNotHome();
    }

    private function controllerHome(){
        if (!$this->controllerExist('home')){
            throw new Exception("A Pagina que está procurando não existe",404);
        }
      
        return $this->instatiateController();
    }

    private function controllerSet($controller){
        if (!$this->controllerExist($controller)){
            throw new Exception("A Pagina que está procurando não existe",404);
        }
        
        return $this->instatiateController();
    }

    private function controllerNotHome(){
        $controller = $this->getControllerNotHome();

        if (!$this->controllerExist($controller)){
            throw new Exception("A Pagina que está procurando não existe",404);
        }
        
        return $this->instatiateController();
    }

    public function getControllerNotHome(){

        if(substr_count($this->uri,'/') > 1){
            list($controller) = array_values(array_filter(explode('/',$this->uri)));
            return (($controller));
        }
        return ((ltrim($this->uri,"/")));
    }

    private function controllerExist($controller){
        $exists = false;

        $controller = ucfirst($controller);

        foreach ($this->folders as $folder){
            if(class_exists($folder."\\".$controller) && is_subclass_of($folder."\\".$controller,"Core\Abstract\Controller")){
                $exists = true;
                $this->namespace = $folder;
                $this->controller = $controller; 
            }
        }
        return $exists;
    }
    
    private function instatiateController(){

        $controller = $this->namespace.'\\'.$this->controller;
        $controller =  $this->container->get($controller);

        $ReflectionClass = new ReflectionClass($controller);
        $methods = $ReflectionClass->getMethods();

        $routeAttribute = null;
        $uri = null;
        $parameters = null;
        $middlewareAttribute = null;

        foreach ($methods as $method){
            
            $routeAttribute = $method->getAttributes(Route::class,ReflectionAttribute::IS_INSTANCEOF);
            $middlewareAttribute = $method->getAttributes(Middleware::class,ReflectionAttribute::IS_INSTANCEOF);

            if(isset($routeAttribute[0])){
                $routeAttribute = $routeAttribute[0]->newInstance();
            }
            else{
                continue;
            }

            if(isset($middlewareAttribute[0])){
                $middlewareAttribute = $middlewareAttribute[0]->newInstance();
            }
    
            $httpMethods = $routeAttribute->getMethods();

            $uri = $routeAttribute->getPath();
            $uri = explode("/",$uri);
            $path = $uri[0];
            unset($uri[0]); 

            if($path == $this->getPath() && in_array($_SERVER['REQUEST_METHOD'],$httpMethods)){
                $parameters = $this->getParameters($uri);
                break;
            }else{
                $routeAttribute = null;
                $uri = null;
                $parameters = null;
                $middlewareAttribute = null;
            }
        }    

        if(!$routeAttribute){
            throw new Exception("A Pagina que está procurando não existe",404);
        }

        $methodName = $method->getName();

        Session::set("controller_namespace",$this->namespace); 
        Session::set("controller",$controller::class);

        $controller->setResquest(new Request);
        $controller->setResponse(new Response);

        if($middlewareAttribute){
            $controller = $middlewareAttribute->handleBefore($controller);
        }

        $response = $controller->$methodName(...$parameters);

        if(!is_a($response,"Core\Response"))
            throw new Exception("O retorno de um metodo do controller deve ser a instância do do metodo Response");

        if($middlewareAttribute){
            $response = $middlewareAttribute->handleAfter($response);
        }
            
        $response->send();
    }

    public function getParameters(array $uriParameters):array
    {
        $uriParameters = array_values($uriParameters);
        
        $parameter = array_slice(array_values(explode('/',$this->uri)),3);
    
        $parametersFinal = [];

        foreach ($uriParameters as $key => $uriParameter){
            $uriArray = \explode(":",str_replace(["{","}"],"",$uriParameter));

            $var = null;
            $required = true;
            if(isset($uriArray[1]))
                $var = $uriArray[1];
            if(isset($uriArray[2]) && $uriArray[2] == "optional")
                $required = false;

            if(isset($parameter[$key]) && !is_null($var) && !empty($parameter[$key])){
                if($var == "num" && is_numeric($parameter[$key])){
                    $parametersFinal[] = $parameter[$key];
                }
                elseif($var == "any" && is_string($parameter[$key])){
                    $parametersFinal[] = $parameter[$key];
                }
                elseif(@preg_match("/".$var."/",'') == false){
                    if(preg_match($var,$parameter[$key]))
                        $parametersFinal[] = $parameter[$key];
                    else
                        throw new Exception(($key+1)."° Parametro é invalido",500);
                }
                else{
                    throw new Exception(($key+1)."° Parametro é invalido",500);
                }
            }
            elseif($required){
                throw new Exception(($key+1)."° Parametro é invalido",500);
            }
        }

        return $parametersFinal;
    }

    private function getPath(){

        if (substr_count($this->uri,'/') > 1){
            $method = array_values(array_filter(explode('/',$this->uri)));
            if (array_key_exists(1,$method))
                return $method[1];
        }

        return "index";
    }

    private function isHome(){
        return ($this->uri == "/");    
    }
}
?>
