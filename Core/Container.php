<?php

namespace Core;

use DI\Container as DIContainer;
use DI\ContainerBuilder;

class Container
{
    public function load():DIContainer
    {
        $builder = new ContainerBuilder();
        $builder->useAttributes(true);
        $builder->useAutowiring(true);
        return $builder->build();
    }

    public function get(string $id){
        return $this->load()->get($id);
    }
}
