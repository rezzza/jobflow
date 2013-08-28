<?php

namespace Rezzza\JobFlow\Scheduler;

use Psr\Log\LoggerAwareTrait;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Rezzza\JobFlow\JobInterface;
use Rezzza\JobFlow\JobMessage;

/**
 * Wrap and contextualize execution of job
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class ExecutionContext
{
    use LoggerAwareTrait;

    /**
     * Current msg
     *
     * @var JobMessage
     */
    public $msg;

    /**
     * Representation of the navigation through the jobs
     *
     * @var RecursiveArrayIterator
     */
    public $graph;

    /**
     * Current job in execution
     *
     * @var JobInterface
     */
    public $job;

    /**
     * Current child job in execution
     */
    public $child;

     /**
      * @var array
      */
    protected $options;

    /**
     * @param JobInterface $job
     * @param JobMessage $msg
     * @param JobGraph $graph
     * @param array $options
     */
    public function __construct(JobInterface $job, JobMessage $msg, JobGraph $graph)
    {
        $this->job = $job;
        $this->graph = $graph;
        $this->msg = $msg;
        $this->initCurrentJob();
 
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($this->msg->context->options);
    }

    /**
     * Run execute on a job for the current msg.
     * It will determine himself which child need to be execute
     *
     * @param JobInterface $job
     */
    public function executeJob(JobInterface $job)
    {
        $this->addOptions($job->getOptions());

        if (null === $this->getCurrentJob()) {
            return;
        }
        
        $this->child = $job->get($this->getCurrentJob());

        $this->addOptions($this->child->getOptions());

        // Run execution
        $result = $this->child->execute($this->readData(), $this);

        return $this->writeData($result);
    }

    /**
     * Looks for data in Message. If no result, try to read input
     *
     * @return mixed
     */
    public function readData()
    {
        if ($this->msg->hasData()) {
            return $this->msg->getData();
        }

        return $this->child->getResolved()->getIo()->read();
    }

    /**
     * Write data on current message and update context to follow the next job
     *
     * @param mixed $data
     */
    public function writeData($data)
    {
        $msg = clone $this->msg;
        
        $msg->setData($data);
        $msg->context->updateToNextJob($this->graph);

        return $msg;
    }

    /**
     * Get name of the child job in execution
     * 
     * @return string
     */
    public function getCurrentJob()
    {
        return $this->msg->context->current;
    }

    /**
     * @return string
     */
    public function getJobId()
    {
        return $this->msg->context->jobId;
    }

    /**
     * At the begining get the first item of the graph
     */
    public function initCurrentJob()
    {
        if ($this->msg->context->isStarting()) {
            $this->msg->context->current = $this->graph->current();

            return;
        }

        $index = array_search($this->msg->context->current, $this->graph->getArrayCopy());
        $this->graph->seek($index);
    }

    public function getOption($name)
    {
        return $this->options[$name];
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'offset' => null,
            'limit' => null,
            'mapping' => null,
            'total' => null
        ));
    }

    public function addOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getOutput()
    {
        return $this->child->getResolved()->getIo()->write();
    }

    public function setGlobalOption($key, $value)
    {
        $this->msg->context->options[$key] = $value;
    }
}