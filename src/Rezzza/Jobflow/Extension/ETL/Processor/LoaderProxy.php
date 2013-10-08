<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\LoaderInterface;

use Rezzza\Jobflow\Processor\JobProcessor;

class LoaderProxy extends JobProcessor implements LoaderInterface
{
    public function load($data, ContextInterface $context)
    {
        return $this->getProcessor()->load($data, $context);
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