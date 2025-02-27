<?php

namespace Core\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Route
{
    public function __construct(
        private string $path,
        private array $methods = ['GET']
    ){
    }

    public function getMethods():array
    {
        return $this->methods;
    }

    public function getPath():string
    {
        return $this->path;
    }
}
