<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Rezzza\Jobflow\Processor\JobProcessor;
use Rezzza\Jobflow\Extension\ETL\Context\MetadataContext;

abstract class ETLProcessor extends JobProcessor
{
    public function createContext()
    {
        return new MetadataContext;
    }
}