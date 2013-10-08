<?php

namespace Rezzza\Jobflow\Processor;

class ConfigProcessor
{
    protected $class;

    protected $args;

    public function __construct($class, $args)
    {
        $this->class = $class;
        $this->args = $args;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function setArgs($args)
    {
        $this->args = $args;
    }

    public function getProxyClass()
    {
        return $this->getClass();
    }
}