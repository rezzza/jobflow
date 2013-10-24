<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\LoaderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class LoaderProxy extends ETLProcessor implements LoaderInterface
{
    public function load($data, ContextInterface $context)
    {
        return $this->getProcessor()->load($data, $context);
    }

    public function execute(JobInput $input, JobOutput &$output, ExecutionContext $execution)
    {
        if ($execution->getLogger()) {
            $this->getProcessor()->setLogger($execution->getLogger());
        }

        $context = $this->createContext();
        $property = $execution->getJobOption('property');

        if (null !== $property) {
            $accessor = PropertyAccess::createPropertyAccessor();
        }

        foreach ($input->read() as $k => $d) {
            $this->getMetadataAccessor()->read($input->getMetadata(), $this->processor, $k);

            $output->writeMetadata($d, $k, $this->getMetadataAccessor());

            if (null !== $property) {
                $d = $accessor->getValue($d, $property);
            }
            
            $this->load($d, $context);
        }

        // If loader not requeue, we should keep data for the next step
        if (false === $execution->getJobOption('requeue')) {
            $output->setData($input->read());
        }

        // Should not use Events ? Will be more flexible
        $output->writePipe($this->flush($context));
        $this->clear($context);

        return $output; // End chain should return empty array
    }

    public function flush(ContextInterface $context)
    {
        return $this->getProcessor()->flush($context);
    }

    function clear(ContextInterface $context)
    {
        return $this->getProcessor()->clear($context);
    }
}