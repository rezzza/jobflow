<?php

namespace Rezzza\Jobflow;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Job implements \IteratorAggregate, JobInterface
{
    /**
     * @var JobConfig
     */
    private $config;

    /**
     * @var JobInterface
     */
    private $parent;

    /**
     * @var JobInterface[]
     */
    protected $children = array();

    /**
     * @var boolean
     */
    protected $locked;

    /**
     * @var JobConfig $config
     */
    public function __construct(JobConfig $config)
    {
        $this->config = $config;
    }

    public function setParent(JobInterface $parent = null)
    {
        if (null !== $parent && '' === $this->config->getName()) {
            throw new \LogicException('A job with an empty name cannot have a parent job.');
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * @var ExecutionContext $context
     */
    public function execute(ExecutionContext $context)
    {
        $this->getResolved()->configJob($this->getConfig(), $this->getOptions());

        $input = $this->getInput($context);
        $output = $this->getOutput($context);

        if ($this->getLogger()) {
            $this->getLogger()->info(sprintf('Start to execute Job : %s', $this->getName()));
        }

        $this->getResolved()->execute($input, $output, $context);

        if ($this->getLogger()) {
            $this->getLogger()->info(sprintf('End to execute Job : %s', $this->getName()));
        }

        return $output;
    }

    /**
     * @param JobInterface $child
     */
    public function add(JobInterface $child)
    {
        if ($this->isLocked()) {
            throw new \RuntimeException('Cannot add child on job locked');
        }

        $child->setParent($this);

        $this->children[$child->getName()] = $child;
    }

    /**
     * @param $name
     *
     * @return JobInterface
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->children)) {
            throw new \LogicException(sprintf('No child with name : "%s" in job "%s"', $name, $this->getName()));
        }

        return $this->children[$name];
    }

    /**
     * @return JobConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->config->getOptions();
    }

    /**
     * @return array
     */
    public function getOption($name, $default = null)
    {
        return $this->config->getOption($name, $default);
    }

    public function getETLWrapper($etlConfig)
    {
        $config    = new Configuration();
        $factory   = new LazyLoadingValueHolderFactory($config);

        $proxy = $factory->createProxy(
            $etlConfig['class'],
            function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use ($etlConfig) {
                $initializer = null;
                $wrappedObject = call_user_func_array(
                    array(new \ReflectionClass($etlConfig['class']), 'newInstance'),
                    $etlConfig['args']
                );

                return true;
            }
        );

        return $proxy;
    }

    /**
     * @return JobInput
     */
    public function getInput(ExecutionContext $execution)
    {
        $input = new JobInput();

        if ($this->isExtractor()) {
            $etl = $this->getEtlConfig();

            // Mapping du Pipe
            if ($execution->msg->pipe) {
                foreach($execution->msg->pipe as $key => $value) {
                    $etl['args'][$key] = $value;
                }
            }

            $input->setExtractor($this->getETLWrapper($etl));
        } 

        if ($execution->msg->input) {
            $input->setData($execution->msg->input);
        }

        $input->setMetadata($execution->msg->metadata);
        $input->setMetadataManager($this->config->getMetadataManager());

        if ($this->isTransformer()) {
            $etl = $this->getEtlConfig();

            $input->setTransformer($this->getETLWrapper($etl));
        }

        return $input;
    }

    /**
     * @return JobOutput
     */
    public function getOutput(ExecutionContext $context)
    {
        $output = new JobOutput();
        $destination = null;

        if ($this->isLoader()) {
            $etl = $this->getEtlConfig();
            $destination = $this->getETLWrapper($etl);
        }

        $output->setDestination($destination);
        $output->setMetadataManager($this->config->getMetadataManager());

        return $output;
    }

    /**
     * @return ResolvedJob
     */
    public function getResolved()
    {
        return $this->config->getResolved();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->config->getName();
    }

    /**
     * @return JobInterface[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param boolean $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return JobInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return IoDescriptor
     */
    public function getIo()
    {
        return $this->config->getIo();
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->config->getLogger();
    }

    /**
     * @return array
     */
    public function getEtlConfig()
    {
        return $this->config->getEtlConfig();
    }

    public function getContextOptions()
    {
        return $this->config->getContextOptions();
    }

    /**
     * Returns the iterator for this job.
     *
     * @return \RecursiveArrayIterator
     */
    public function getIterator()
    {
        return new \RecursiveArrayIterator($this->children);
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->getParent()->getName().'.'.$this->getName();
    }

    /**
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @return boolean
     */
    public function isExtractor()
    {
        return $this->config->getEtlType() === ETLType::TYPE_EXTRACTOR;
    }

    /**
     * @return boolean
     */
    public function isTransformer()
    {
        return $this->config->getEtlType() === ETLType::TYPE_TRANSFORMER;
    }

    /**
     * @return boolean
     */
    public function isLoader()
    {
        return $this->config->getEtlType() === ETLType::TYPE_LOADER;
    }

    public function __toString()
    {
        return $this->getName();
    }
}