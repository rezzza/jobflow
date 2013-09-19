<?php

namespace Rezzza\Jobflow;

class Pipe
{
    public $mapping = array();

    public $params = array();

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function addParam(array $param)
    {
        $key = key($param);

        if (!array_key_exists($key, $this->mapping)) {
            throw new \LogicException(sprintf('Pipe mapping does not have "%s" key', $key));
        }

        $new = $this->mapping[$key];

        $this->params[] = array(
            $new => current($param)
        );
    }
}