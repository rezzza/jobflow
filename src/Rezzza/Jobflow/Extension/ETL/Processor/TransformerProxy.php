<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\TransformerInterface;

use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class TransformerProxy extends ETLProcessor implements TransformerInterface
{
    public function transform($data, ContextInterface $context)
    {
        return $this->getProcessor()->transform($data, $context);
    }

    public function execute(JobInput $input, JobOutput &$output, ExecutionContext $execution)
    {       
        foreach ($input->read() as $k => $result) {
            // Read metadata
            $this->getMetadataAccessor()->read($input->getMetadata(), $this->processor, $k);

            // Write metadata
            $output->writeMetadata($result, $k, $this->getMetadataAccessor());

            $context = $this->createContext();

            if (null !== ($transformClass = $execution->getJobOption('transform_class'))) {
                $context->setTransformedData(new $transformClass);
            }

            $transformedData = $this->transform($result, $context);

            if (null !== ($updateMethod = $execution->getJobOption('update_method'))) {
                call_user_func_array($updateMethod, array($transformedData, $execution));
            }
            
            $output->write($transformedData, $k);
        }

        return $output;
    }
}