<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Rezzza\Jobflow\Processor\ProcessorConfig;

class ETLProcessorConfig extends ProcessorConfig
{
    protected $proxy;

    public function __construct($class, $args, $calls, $proxy)
    {
        parent::__construct($class, $args, $calls);

        $this->proxy = $proxy;
    }

    public function getProxyClass()
    {
        return $this->proxy;
    }
}