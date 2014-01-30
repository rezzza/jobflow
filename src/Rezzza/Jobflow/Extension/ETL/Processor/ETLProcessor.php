<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Rezzza\Jobflow\Processor\JobProcessor;
use Rezzza\Jobflow\Extension\ETL\Context\ETLProcessorContext;

abstract class ETLProcessor extends JobProcessor
{
    public function createContext($execution = null, $metadata = null)
    {
        return new ETLProcessorContext($execution, $metadata);
    }
}
