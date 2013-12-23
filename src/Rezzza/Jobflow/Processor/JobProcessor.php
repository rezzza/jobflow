<?php

namespace Rezzza\Jobflow\Processor;

use Psr\Log\LoggerAwareTrait;

abstract class JobProcessor
{
    use LoggerAwareTrait;

    protected $processor;

    protected $metadataAccessor;

    public function __construct($processor, $metadataAccessor)
    {
        $this->processor = $processor;
        $this->metadataAccessor = $metadataAccessor;
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
}