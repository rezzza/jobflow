<?php

namespace Rezzza\Jobflow\Processor;

use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Processor\JobProcessor;

class ProcessorConfig
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

    public function createProcessor(MetadataAccessor $metadataAccessor, LoggerInterface $logger = null)
    {
        $processor = $this->createObject($this->class, $this->args);

        if (method_exists($processor, 'setLogger')) {
            $processor->setLogger($logger);
        }

        return $processor;
    }

    protected function createObject($class, $args)
    {
        return call_user_func_array([new \ReflectionClass($class), 'newInstance'], $args);
    }
}
