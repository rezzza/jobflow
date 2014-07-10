<?php

namespace Rezzza\Jobflow\Extension\Pipe;

use Knp\ETL\LoaderInterface;
use Knp\ETL\ContextInterface;
use Psr\Log\LoggerAwareTrait;
use Rezzza\Jobflow\Io;

/**
 * Forward $data as input for the next extractor
 */
class PipeLoader implements LoaderInterface
{
    use LoggerAwareTrait;

    private $execution;

    public function __construct($execution)
    {
        $this->execution = $execution;
    }

    public function load($data, ContextInterface $context)
    {
        $input = new Io\Input(
            new Io\Driver\File($data)
        );
        $this->execution->write($input, $context->metadata);
    }

    public function flush(ContextInterface $context)
    {
    }

    public function clear(ContextInterface $context)
    {
    }
}
