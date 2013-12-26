<?php

namespace Rezzza\Jobflow\Processor;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

abstract class JobProcessor
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

    //abstract function execute(JobInput $input, JobOutput &$output, ExecutionContext $context);

    public function getProcessor()
    {
        return $this->processor;
    }

    public function getMetadataAccessor()
    {
        return $this->metadataAccessor;
    }

    public function debug($msg)
    {
        if (null !== $this->logger) {
            $this->logger->debug($msg);
        }
    }
}