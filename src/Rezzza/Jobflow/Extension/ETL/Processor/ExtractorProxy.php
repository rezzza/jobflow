<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Psr\Log\LoggerInterface;
use Knp\ETL\ContextInterface;
use Knp\ETL\ExtractorInterface;
use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Scheduler\ExecutionContext;
use Rezzza\Jobflow\Processor\JobProcessor;

class ExtractorProxy extends ETLProcessor implements ExtractorInterface, JobProcessor
{
    public function __construct(ExtractorInterface $processor, MetadataAccessor $metadataAccessor, LoggerInterface $logger = null)
    {
        // Construct used for TypeHinting
        parent::__construct($processor, $metadataAccessor, $logger);
    }

    public function execute(ExecutionContext $execution)
    {
        $data = $execution->read();

        // For data from pipe, we need to get back global metadata... Little crappy I know...
        $metadata = $execution->getContextMetadata();

        // Limit total to the max if lesser
        if ($execution->hasNoTotal()) {
            $execution->changeTotal($this->count());
        }

        // Read data
        try {
            $data = $this->slice($execution);
        } catch (\OutOfBoundsException $e) {
            // Message has no more data and should not be spread
            $execution->terminate();
            $data = [];
        }

        // No data
        if (count($data) <= 0) {
            $this->debug('No data');
        }

        // Store data read
        foreach ($data as $k => $result) {
            $metadata = $this->metadataAccessor->createMetadata($result, $metadata);
            $execution->write($result, $metadata);
        }

        $execution->valid();
    }

    public function slice(ExecutionContext $execution)
    {
        $offset = $execution->getOffset();
        $limit = $execution->getLimit();

        if (method_exists($this->processor, 'slice')) {
            return $this->processor->slice($offset, $limit);
        }

        $this->seek($offset);
        $data = [];

        for ($i = 0; $i < $limit && $this->valid(); $i++) {
            $data[] = $this->extract($this->createContext($execution));
        }

        return $data;
    }

    public function count()
    {
        return $this->processor->count();
    }

    public function extract(ContextInterface $context)
    {
        return $this->processor->extract($context);
    }

    public function rewind()
    {
        return $this->processor->rewind();
    }

    public function current()
    {
        return $this->processor->current();
    }

    public function key()
    {
        return $this->processor->key();
    }

    public function next()
    {
        return $this->processor->next();
    }

    public function valid()
    {
        return $this->processor->valid();
    }

    public function seek($position)
    {
        return $this->processor->seek($position);
    }
}
