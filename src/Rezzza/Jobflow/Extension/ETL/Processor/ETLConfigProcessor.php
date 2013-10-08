<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Rezzza\Jobflow\Processor\ConfigProcessor;

class ETLConfigProcessor extends ConfigProcessor
{
    protected $etlType;

    public function __construct($class, $args, $etlType)
    {
        parent::__construct($class, $args);

        $this->etlType = $etlType;
    }

    public function getProxyClass()
    {
        if ($this->etlType === 'extractor') {
            return 'Rezzza\Jobflow\Extension\ETL\Processor\ExtractorProxy';
        } elseif ($this->etlType == 'transformer') {
            return 'Rezzza\Jobflow\Extension\ETL\Processor\TransformerProxy';            
        } elseif ($this->etlType == 'loader') {
            return 'Rezzza\Jobflow\Extension\ETL\Processor\LoaderProxy';
        } else {
            throw new \LogicException('ETL Type should be "extractor" or "transformer" or "loader"');
        }
    }
}