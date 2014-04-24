<?php

namespace Rezzza\Jobflow\Processor;

use Psr\Log\LoggerInterface;
use Rezzza\Jobflow\Metadata\MetadataAccessor;

abstract class ProcessorConfig
{
    protected $class;

    protected $args;

    protected $calls;

    public function __construct($class, $args, array $calls = array())
    {
        $this->class = $class;
        $this->args = $args;
        $this->calls = $calls;
    }

    abstract public function createProcessor(MetadataAccessor $metadataAccessor, LoggerInterface $logger = null);

    protected function createObject($class, $args)
    {
        $ref = new \ReflectionClass($class);

        if (null !== $ref->getConstructor()) {
            return call_user_func_array([$ref, 'newInstance'], $args);
        }

        return call_user_func([$ref, 'newInstance']);
    }
}
