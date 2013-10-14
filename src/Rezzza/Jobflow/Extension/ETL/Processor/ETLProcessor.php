<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Rezzza\Jobflow\Processor\JobProcessor;

abstract class ETLProcessor extends JobProcessor
{
    public function createContext()
    {
        return new \Knp\ETL\Context\Context;
    }
}