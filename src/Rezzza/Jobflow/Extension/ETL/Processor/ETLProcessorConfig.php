<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\Extension\ETL\Processor\ETLProcessor;
use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Processor\ProcessorConfig;

class ETLProcessorConfig extends ProcessorConfig
{
    protected $proxy;

    public function __construct($class, $args, $calls, $proxy)
    {
        parent::__construct($class, $args, $calls);

        $this->proxy = $proxy;
    }

    public function createProcessor(MetadataAccessor $metadataAccessor, LoggerInterface $logger = null)
    {
        $processor = parent::createProcessor($metadataAccessor, $logger);

        // For ETLProcessor, we build a proxy around processor to wrap its execution regarding a defined process
        // Proxy should be ETLProcessor
        $proxy = $this->createObject(
            $this->proxy,
            [
                $processor,
                $metadataAccessor,
                $logger
            ]
        );

        if ($proxy instanceof ETLProcessor) {
            return $proxy;
        }

        throw new \InvalidArgumentException('Proxy argument in ETLProcessorConfig should extends ETLProcessor');
    }

}
