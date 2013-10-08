<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ContextInterface;
use Knp\ETL\ExtractorInterface;

use Rezzza\Jobflow\Processor\JobProcessor;

class ExtractorProxy extends JobProcessor implements ExtractorInterface
{
    public function count()
    {
        return $this->getProcessor()->count();
    }

    public function extract(ContextInterface $context)
    {
        return $this->getProcessor()->extract($context);
    }

    public function rewind()
    {
        return $this->getProcessor()->rewind();
    }

    public function current()
    {
        return $this->getProcessor()->current();
    }

    public function key()
    {
        return $this->getProcessor()->key();
    }

    public function next()
    {
        return $this->getProcessor()->next();
    }

    public function valid()
    {
        return $this->getProcessor()->valid();
    }

    public function seek($position)
    {
        return $this->getProcessor()->seek($position);
    }
}