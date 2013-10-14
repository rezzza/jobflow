<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\LoaderInterface;

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

        foreach ($input->read() as $k => $d) {
            $this->getMetadataAccessor()->read($input->getMetadata(), $this->processor, $k);

            $this->load($d, $context);
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