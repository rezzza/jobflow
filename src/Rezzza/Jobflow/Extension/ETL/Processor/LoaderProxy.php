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
        if ($execution->getLogger()) {
            $this->getProcessor()->setLogger($execution->getLogger());
        }

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

        $this->flush($context);
        $this->clear($context);
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