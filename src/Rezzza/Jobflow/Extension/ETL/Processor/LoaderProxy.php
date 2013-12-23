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

        $context = $this->createContext();
        $property = $execution->getJobOption('property');

        if (null !== $property) {
            $accessor = PropertyAccess::createPropertyAccessor();
        }

        foreach ($execution->read() as $k => $data) {
            $d = $data->getValue();

            if (null !== $property) {
                $d = $accessor->getValue($d, $property);
            }

            $this->load($d, $context);
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