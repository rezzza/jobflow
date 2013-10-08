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
        // We inject msg as it could be used during job runtime configuration
        $options = $this->getOptions();
        $options['message'] = $context->input;

        // Runtime configuration (!= buildJob which is executed when we build job)
        $this->getResolved()->configJob($this->getConfig(), $options);

        $input = $this->getInput($context->input);
        $output = $this->getOutput($context->output);

        if ($this->getLogger()) {
            $this->getLogger()->info(sprintf(
                'Start to execute Job [%s] : %s',
                $this->getParent()->getName(),
                $this->getName()
            ));
        }

        $this->getResolved()->execute($input, $output, $context);

        // Update context
        $output->setContextFromInput($input);

        if ($this->getLogger()) {
            $this->getLogger()->info(sprintf(
                'End to execute Job [%s] : %s',
                $this->getParent()->getName(),
                $this->getName()
            ));
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

    /**
     * @return JobInput
     */
    public function getInput(JobMessage $message)
    {
        return new JobInput($message, $this->getConfigProcessor());
    }

    /**
     * @return JobOutput
     */
    public function getOutput(JobMessage $message)
    {
        $output = new JobOutput($message, $this->getConfigProcessor());

        $output->setMetadataGenerator($this->config->getMetadataGenerator());

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
    public function getConfigProcessor()
    {
        return $this->config->getConfigProcessor();
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
        return $this->config->getETLType() === ETLType::TYPE_EXTRACTOR;
    }

    /**
     * @return boolean
     */
    public function isTransformer()
    {
        return $this->config->getETLType() === ETLType::TYPE_TRANSFORMER;
    }

    /**
     * @return boolean
     */
    public function isLoader()
    {
        return $this->config->getETLType() === ETLType::TYPE_LOADER;
    }

    public function __toString()
    {
        return $this->getName();
    }
}