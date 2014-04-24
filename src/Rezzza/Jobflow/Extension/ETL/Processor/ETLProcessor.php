<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Rezzza\Jobflow\Extension\ETL\Context\ETLProcessorContext;
use Rezzza\Jobflow\Metadata\MetadataAccessor;

abstract class ETLProcessor
{
    use LoggerAwareTrait;

    protected $processor;

    protected $metadataAccessor;

    public function __construct($processor, MetadataAccessor $metadataAccessor, LoggerInterface $logger = null)
    {
        $this->processor = $processor;
        $this->metadataAccessor = $metadataAccessor;
        $this->logger = $logger;
    }

    /**
     * @param \Rezzza\Jobflow\Scheduler\ExecutionContext $execution
     */
    protected function createContext($execution = null, $metadata = null)
    {
        return new ETLProcessorContext($execution, $metadata);
    }

    /**
     * @param string $msg
     */
    protected function debug($msg)
    {
        if (null !== $this->logger) {
            $this->logger->debug($msg);
        }
    }
}
