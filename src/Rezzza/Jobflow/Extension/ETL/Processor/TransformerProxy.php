<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\TransformerInterface;
use Psr\Log\LoggerInterface;
use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Processor\JobProcessor;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class TransformerProxy extends ETLProcessor implements TransformerInterface, JobProcessor
{
    public function __construct(TransformerInterface $processor, MetadataAccessor $metadataAccessor, LoggerInterface $logger = null)
    {
        // Construct used for TypeHinting
        parent::__construct($processor, $metadataAccessor, $logger);
    }

    public function transform($data, ContextInterface $context)
    {
        return $this->processor->transform($data, $context);
    }

    public function execute(ExecutionContext $execution)
    {
        $data = $execution->read();

        $this->debug(count($data) .' rows');

        foreach ($data as $k => $result) {
            $value = $result->getValue();
            $metadata = $result->getMetadata();
            $context = $this->createContext($execution, $metadata);

            $transformedData = $this->transform($value, $context);

            if (is_array($transformedData)) {
                foreach ($transformedData as $d) {
                    $this->writeData($value, $metadata, $execution, $d);
                }
            } else {
                $this->writeData($value, $metadata, $execution, $transformedData);
            }
        }

        $execution->valid();
    }

    private function writeData($value, $metadata, $execution, $data)
    {
        $metadata = $this->metadataAccessor->createMetadata($value, $metadata);
        $execution->write($data, $metadata);
    }
}
