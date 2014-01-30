<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\TransformerInterface;

use Rezzza\Jobflow\Scheduler\ExecutionContext;

class TransformerProxy extends ETLProcessor implements TransformerInterface
{
    public function transform($data, ContextInterface $context)
    {
        return $this->getProcessor()->transform($data, $context);
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

            $metadata = $this->getMetadataAccessor()->createMetadata($value, $metadata);

            $execution->write($transformedData, $metadata);
        }

        $execution->valid();
    }
}
