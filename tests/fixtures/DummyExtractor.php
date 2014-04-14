<?php

namespace Rezzza\Jobflow\Tests\Fixtures;

use Knp\ETL\ContextInterface;
use Knp\ETL\ExtractorInterface;

class DummyExtractor implements ExtractorInterface
{
    public function extract(ContextInterface $context)
    {
        // Who's care ?
    }
}
