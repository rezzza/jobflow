<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\TransformerInterface;

use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Processor\JobProcessor;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class TransformerProxy extends JobProcessor implements TransformerInterface
{
    public function transform($data, ContextInterface $context)
    {
        return $this->getProcessor()->transform($data, $context);
    }

    public function execute(JobInput $input, JobOutput &$output, ExecutionContext $execution)
    {       
        foreach ($input->read() as $k => $result) {
            $output->writeMetadata($result, $k, $this->getMetadataAccessor());

            /*if ($this->transformClass) {
                $this->etlContext->setTransformedData(new $this->transformClass);
            }*/

            $transformedData = $this->transform($result, new \Knp\ETL\Context\Context);

            /*if ($this->updateMethod) {
                call_user_func($this->updateMethod, $transformedData);
            }*/

            if ($execution->getLogger()) {
                $execution->getLogger()->debug('transformation '.$k.' : '.json_encode($transformedData));
            }
            
            $output->write($transformedData, $k);
        }

        return $output;
    }
}