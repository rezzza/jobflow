<?php

namespace Rezzza\Jobflow\Tests\Fixtures;

use Knp\ETL\ContextInterface;
use Knp\ETL\ExtractorInterface;

class DummyExtractor implements ExtractorInterface, \Iterator, \Countable
{
    public function extract(ContextInterface $context)
    {
        // Who's care ?
    }

    public function current()
    {
    }

    public function key()
    {
    }

    public function next()
    {
    }

    public function rewind()
    {
    }

    public function valid()
    {
    }

    public function count()
    {
    }

    public function seek()
    {
    }
}
