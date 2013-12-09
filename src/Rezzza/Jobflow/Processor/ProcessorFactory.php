<?php

namespace Rezzza\Jobflow\Processor;

use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Processor\ProcessorConfig;

class ProcessorFactory
{
    public function create(array $pipe, ProcessorConfig $config, MetadataAccessor $metadataAccessor)
    {
        if (count($pipe) > 0) {
            $args = $config->getArgs();

            foreach($pipe as $key => $value) {
                $args[$key] = $value;
            }

            $config->setArgs($args);
        }

        $processor = $this->createObject($config->getClass(), $config->getArgs());

        if ($config->getProxyClass()) {
            $proxy = $this->createObject(
                $config->getProxyClass(),
                array(
                    $processor,
                    $metadataAccessor
                )
            );
        } else {
            $proxy = $processor;
        }

        return $proxy;
    }

    public function createObject($class, $args)
    {
        return call_user_func_array(
            array(new \ReflectionClass($class), 'newInstance'),
            $args
        );
    }
}