<?php

namespace Core\Attributes;

use Core\Abstract\Controller;
use Core\Interfaces\Middleware as InterfacesMiddleware;
use Core\Response;
use SplQueue;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Middleware
{
    private SplQueue $middleware;

    public function __construct(...$classes
    ) {
        $this->middleware = new SplQueue;

        foreach ($classes as $class){
            if(is_subclass_of($class,"Core\Interfaces\Middleware")){
                $this->add($class);
            }
        }
    }

    private function add(InterfacesMiddleware $class){
        $this->middleware->enqueue($class);
    }

    public function handleBefore(Controller $controller){
        
        foreach ($this->middleware as $middleware){
            $controller = $middleware->before($controller);
        }

        return $controller;
    }

    public function handleAfter(Response $response){

        while(!$this->middleware->isEmpty()){
            $middleware = $this->middleware->dequeue();

            $middleware->after($response);
        }

        return $response;
    }
}
