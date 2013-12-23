<?php

namespace Rezzza\Jobflow\Processor;

use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Processor\ProcessorConfig;

class ProcessorFactory
{
    public function create(ProcessorConfig $config, MetadataAccessor $metadataAccessor)
    {
        $processor = $this->createObject($config->getClass(), $config->getArgs());

        if ($config->getProxyClass()) {
            $proxy = $this->createObject(
                $config->getProxyClass(),
                [
                    $processor,
                    $metadataAccessor
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