<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\LoaderInterface;

use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Processor\JobProcessor;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class LoaderProxy extends JobProcessor implements LoaderInterface
{
    public function load($data, ContextInterface $context)
    {
        return $this->getProcessor()->load($data, new \Knp\ETL\Context\Context);
    }

    public function execute(JobInput $input, JobOutput &$output, ExecutionContext $execution)
    {
        if ($execution->getLogger()) {
            $this->setLogger($execution->getLogger());
        }

        foreach ($input->read() as $k => $d) {
            $this->getMetadataAccessor()->read($input->getMetadata(), $this->processor, $k);

            $this->load($d, new \Knp\ETL\Context\Context);
        }

        // Should not use Events ? Will be more flexible
        $output->writePipe($this->processor->flush(new \Knp\ETL\Context\Context));
        $this->processor->clear(new \Knp\ETL\Context\Context);

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