<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\Extension\ETL\Context\ETLProcessorContext;

abstract class ETLProcessor
{
    use LoggerAwareTrait;

    protected $processor;

    protected $metadataAccessor;

    public function __construct($processor, $metadataAccessor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->metadataAccessor = $metadataAccessor;
        $this->logger = $logger;
    }

    protected function createContext($execution = null, $metadata = null)
    {
        return new ETLProcessorContext($execution, $metadata);
    }

    protected function debug($msg)
    {
        if (null !== $this->logger) {
            $this->logger->debug($msg);
        }
    }
}
