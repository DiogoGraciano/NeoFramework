<?php

namespace Core\Abstract;

use Core\Functions;
use Core\Template;

abstract class Layout{

    protected template $tpl;

    public function setTemplate(string $caminho,bool $accurate=false)
    {
        $this->tpl = new Template(Functions::getRoot()."/App/View/Templates/".$caminho,$accurate); 
    }

    public function getTemplate(string $caminho,bool $accurate=false):template
    {
        return new Template(Functions::getRoot()."/App/View/Templates/".$caminho,$accurate); 
    }

    public function isMobile():bool
    {
        return Functions::isMobile();
    }

    public function show():void
    {
        $this->tpl->show();
    }

    public function parse():string
    {
        return $this->tpl->parse();
    }

}


