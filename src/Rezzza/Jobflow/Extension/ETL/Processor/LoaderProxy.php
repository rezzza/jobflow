<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\LoaderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Rezzza\Jobflow\Scheduler\ExecutionContext;

class LoaderProxy extends ETLProcessor implements LoaderInterface
{
    public function load($data, ContextInterface $context)
    {
        return $this->getProcessor()->load($data, $context);
    }

    public function execute(ExecutionContext $execution)
    {
        $property = $execution->getJobOption('property');

        if (null !== $property) {
            $accessor = PropertyAccess::createPropertyAccessor();
        }

        foreach ($execution->read() as $k => $result) {
            $context = $this->createContext();
            $context->metadata = $result->getMetadata();
            $data = $result->getValue();

            if (null !== $property) {
                $data = $accessor->getValue($data, $property);
            }

            $this->load($data, $context);
        }

        $context = $this->createContext();

        $this->flush($context);
        $this->clear($context);

        if (false === $execution->getJobOption('requeue')) {
            // If a loader don't requeue message, the next job step will need the original data
            $execution->rewindData();
        }
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