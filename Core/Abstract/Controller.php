<?php
namespace Core\Abstract;

use Core\Functions;
use Core\Url;
use Core\Request;
use Core\Response;

abstract class Controller
{
    protected readonly int $page;

    protected readonly array $urlQuery;

    protected Request $request;

    protected Response $response;

    public function __construct()
    {
        $this->urlQuery = Url::getUriQueryArray();
        $this->page = isset($this->urlQuery["page"])?intval($this->urlQuery["page"]):1;
    }

    public function setResquest(Request $request){
        $this->request = $request;
    }

    public function setResponse(Response $response){
        $this->response = $response;
    }

    public function getResponse(){
        return $this->response;
    }

    public function getRequest(){
        return $this->request;
    }

    protected function getOffset(int $limit = 30):int
    {
        return ($this->page-1)*$limit;
    }

    protected function getLimit(int $limit = 30):int
    {
        return $limit?:20;
    }

    protected function isMobile():bool
    {
        return Functions::isMobile();
    }
}
