<?php

namespace Rezzza\Jobflow\Processor;

use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Processor\ProcessorConfig;

class ProcessorFactory
{
    public function create(ProcessorConfig $config, MetadataAccessor $metadataAccessor, LoggerInterface $logger)
    {
        $processor = $this->createObject($config->getClass(), $config->getArgs());

        if (method_exists($processor, 'setLogger')) {
            $processor->setLogger($logger);
        }

        if ($config->getProxyClass()) {
            $proxy = $this->createObject(
                $config->getProxyClass(),
                [
                    $processor,
                    $metadataAccessor,
                    $logger
                ]
            );
        } else {
            $proxy = $processor;
        }

        return $proxy;
    }

    protected function createObject($class, $args)
    {
        return call_user_func_array([new \ReflectionClass($class), 'newInstance'], $args);
    }
}