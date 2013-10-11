<?php

namespace Rezzza\Jobflow\Processor;

class ConfigProcessor
{
    protected $class;

    protected $args;

    protected $calls;

    public function __construct($class, $args, array $calls = array())
    {
        $this->class = $class;
        $this->args = $args;
        $this->calls = $calls;
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

    public function getCalls()
    {
        return $this->calls;
    }

    public function getProxyClass()
    {
        return false;
    }
}