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

        $execution->getLogger()->debug(count($data) .' rows');

        foreach ($data as $k => $result) {
            $context = $this->createContext();
            $value = $result->getValue();
            $metadata = $result->getMetadata();
            $context->metadata = $metadata;

            if (null !== ($transformClass = $execution->getJobOption('transform_class'))) {
                $context->setTransformedData(new $transformClass);
            }

            $transformedData = $this->transform($value, $context);

            if (null !== ($updateMethod = $execution->getJobOption('update_method'))) {
                call_user_func_array($updateMethod, array($transformedData, $execution));
            }

            $metadata = $this->getMetadataAccessor()->createMetadata($value, $metadata);

            $execution->write($transformedData, $metadata);
        }
    }
}