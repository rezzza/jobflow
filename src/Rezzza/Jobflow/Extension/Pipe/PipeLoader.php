<?php

namespace Rezzza\Jobflow\Extension\Pipe;

use Knp\ETL\LoaderInterface;
use Knp\ETL\ContextInterface;

use Psr\Log\LoggerAwareTrait;

use Rezzza\Jobflow\Io;

/**
 * Build input and write them in the current ExecutionContext as $data
 * On handleMsg, all Input will be transform as a new JobMessage
 */
class PipeLoader implements LoaderInterface
{
    use LoggerAwareTrait;

    private $forward;

    private $execution;

    public function __construct($forward, $execution)
    {
        $this->forward = $forward;
        $this->execution = $execution;
    }

    public function load($data, ContextInterface $context)
    {
        $input = new Io\Input(
            new Io\Driver\File($data[$this->forward])
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
