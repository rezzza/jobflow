<?php

namespace Rezzza\Jobflow\Extension\Pipe;

use Knp\ETL\LoaderInterface;
use Knp\ETL\ContextInterface;

use Psr\Log\LoggerAwareTrait;

class PipeLoader implements LoaderInterface
{
    use LoggerAwareTrait;

    private $pipe;

    public function __construct(array $mapping)
    {
        $this->pipe = new Pipe($mapping);
    }

    public function load($data, ContextInterface $context)
    {
        $this->pipe->addParam($data);
    }

    public function flush(ContextInterface $context)
    {
        return $this->pipe;
    }

    public function clear(ContextInterface $context)
    {

    }
}