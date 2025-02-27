<?php

namespace Core\Interfaces;

use Core\Abstract\Controller;
use Core\Response;

interface Middleware 
{
    public function before(Controller $controller):Controller;

    public function after(Response $response):Response;
}
