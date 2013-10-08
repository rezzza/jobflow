<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\TransformerInterface;

use Rezzza\Jobflow\Processor\JobProcessor;

class TransformerProxy extends JobProcessor implements TransformerInterface
{
    public function transform($data, ContextInterface $context)
    {
        $this->getProcessor()->transform($data, $context);
    }
}