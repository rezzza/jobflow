<?php

namespace Rezzza\Jobflow;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

use Rezzza\Jobflow\Extension\Pipe\Pipe;

abstract class JobStream
{
    /**
     * @var JobMessage
     */
    protected $message;

    /**
     * @var ConfigProcessor
     */
    protected $configProcessor;

    /**
     * @var JobProcessor
     */
    protected $processor;

    public function __construct($message, $configProcessor)
    {
        $this->message = $message;
        $this->configProcessor = $configProcessor;
        $this->setProcessorFromConfig($configProcessor);
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    public function setProcessorFromConfig($config)
    {
        $pipe = $this->message->pipe;

        if ($pipe && !$pipe instanceof Pipe) {
            $args = $config->getArgs();
            foreach($pipe as $key => $value) {
                $args[$key] = $value;
            }
            $config->setArgs($args);
        }

        //var_dump($this->message->pipe);

        $proxyConfig  = new Configuration();
        $proxyFactory = new LazyLoadingValueHolderFactory($proxyConfig);

        $proxy = $proxyFactory->createProxy(
            $config->getProxyClass(),
            function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use ($config) {
                $initializer = null;
                $wrappedObject = call_user_func_array(
                    array(new \ReflectionClass($config->getClass()), 'newInstance'),
                    $config->getArgs()
                );

                return true;
            }
        );

        $this->setProcessor($proxy);
    }
}