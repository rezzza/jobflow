<?php

namespace Rezzza\Jobflow;

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

    protected $locked;

    /**
     * @var JobConfig $config
     */
    public function __construct(JobConfig $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
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
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
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

    public function isExtractor()
    {
        return $this->config->getEtlType() === ETLType::TYPE_EXTRACTOR;
    }

    public function isTransformer()
    {
        return $this->config->getEtlType() === ETLType::TYPE_TRANSFORMER;
    }

    public function isLoader()
    {
        return $this->config->getEtlType() === ETLType::TYPE_LOADER;
    }

    public function getETLWrapper($etlConfig)
    {
        if (!is_array($etlConfig)) {
            throw new \RuntimeException('etlConfig in JobConfig should be an array to built ETL Wrapper');
        }

        if (!array_key_exists('class', $etlConfig) || !array_key_exists('args', $etlConfig)) {
            throw new \RuntimeException('etlConfig should have "class" and "args" keys');
        }

        $args = array();

        // We execute all DelayedArg
        foreach ($etlConfig['args'] as $arg) {
            if ($arg instanceof DelayedArg) {
                $arg = $arg();
            }

            $args[] = $arg;
        }

        return call_user_func_array(
            array(new \ReflectionClass($etlConfig['class']), 'newInstance'),
            $args
        );
    }

    public function __toString()
    {
        return $this->getName();
    }
}