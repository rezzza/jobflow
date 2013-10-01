<?php

namespace Rezzza\Jobflow\Scheduler;

use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\JobContext;
use Rezzza\Jobflow\JobInterface;
use Rezzza\Jobflow\JobFactory;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * Handles job execution
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Jobflow
{
    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @var JobFactory
     */
    protected $jobFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JobGraph
     */
    protected $jobGraph;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport, JobFactory $jobFactory, LoggerInterface $logger = null)
    {
        $this->transport = $transport;
        $this->jobFactory = $jobFactory;
        $this->logger = $logger;
    }

    /**
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return JobInterface
     */
    public function getJob()
    {
        return $this->job;
    }

    public function getJobGraph()
    {
        return $this->jobGraph;
    }

    /**
     * Creates init message to start execution.
     *
     * @return Jobflow
     */
    public function init()
    {
        if (null === $this->getJob()) {
            throw new \RuntimeException('You need to set a job');
        }

        $init = $this->getInitMessage();

        $this->addMessage($init);

        return $this;
    }

    /**
     * @param JobInterface $job
     *
     * @return Jobflow
     */
    public function setJob($job, array $jobOptions = array())
    {
        if (is_string($job)) {
            $this->job = $this->createJob($job, $jobOptions);
        } elseif ($job instanceof JobInterface) {
            $this->job = $job;
        } else {
            throw new \InvalidArgumentException('Job should be a string or a JobInterface');
        }

        $this->buildGraph();
        $this->job->setLocked(true);

        return $this;
    }

    /**
     * Adds message in transport layer
     *
     * @param JobMessage $msg
     *
     * @return Jobflow
     */
    public function addMessage(JobMessage $msg)
    {
        if ($this->logger) {
            if (null === $msg->context->getCurrent()) {
                $step = 'starting';
            } else {
                $step = 'step '.$msg->context->getCurrent();
            }

            $this->logger->info(sprintf(
                'Add new message for job [%s] : %s', 
                $msg->context->getJobId(),
                $step
            ));
        }
        
        $this->transport->addMessage($msg);

        return $this;
    }

    /**
     * Need to work on this method to make it more simple and readable
     */
    public function handleMessage($msg)
    {
        if (!$msg instanceof JobMessage) {
            if ($this->logger) {
                $this->logger->info('No more message');
            }

            return false;
        }

        if (null === $this->job) {
            $this->setJobFromMessage($msg);
        }

        if ($this->logger) {
            $this->logger->info('Read msg : '.$msg->context->getMessageName());
        }

        $msg = clone $msg;
        $msg->context->moveToCurrent($this->jobGraph);
        $msg->input = $msg->output;
        $msg->output = null;

        $child = $this->job->get($msg->context->getCurrent());

        if ($msg->pipe) {
            foreach ($msg->pipe->params as $pipe) {
                $forward = clone $msg;
                $forward->context->initOptions();
                $forward->pipe = $pipe;
                $graph = clone $this->jobGraph;
                $forward->context->updateToNextJob($graph);
                $this->addMessage($forward);
            }
        } elseif ($child->isLoader()) {
            $msg->context->tick();

            if (!$msg->context->isFinished()) {
                $msg->context->addStep($msg->context->getCurrent());
                $this->jobGraph->next();
                $msg->context->setCurrent($this->jobGraph->current());
            } else {
                $msg = null;
            }
        } elseif (!$this->jobGraph->hasNextJob() && $msg->context->isFinished()) {
            $msg = null;
        } else {
            $msg->context->updateToNextJob($this->jobGraph);
        }

        if (null !== $msg) {
            $this->addMessage($msg);
        }

        return $this;
    }

    /**
     * Handles a message in args or look to the transport for next one
     *
     * @param JobMessage|null $msg
     */
    public function run(JobMessage $msg = null)
    {
        if (null !== $msg) {
            return $this->runJob($msg);
        }

        while ($msg = $this->wait()) {
            if (!$msg instanceof JobMessage) {
                return;
            }

            $result = $this->runJob($msg);

            if ($result instanceof JobMessage) {
                $this->handleMessage($result);
            }
        }

        return $result;
    }

    /**
     * Waits for message
     *
     * @return JobMessage
     */
    public function wait()
    {
        return $this->transport->getMessage();
    }

    /**
     * Executes current job.
     * Injects ExecutionContext as Visitor
     *
     * @param JobMessage $msg
     */
    public function runJob(JobMessage $msg)
    {
        if (null === $this->job) {
            $this->setJobFromMessage($msg);
        }

        // Store input message
        $this->startMsg = $msg;

        $context = new ExecutionContext(
            $this->startMsg,
            $this->jobGraph
        );

        $output = $context->executeJob($this->job);

        // Event ? To handle createEndMsg in a more readable way ?
        if (!$output instanceof JobOutput) {
            return $output;
        }

        $end = $this->createEndMsg($output);

        return $end;
    }

    /**
     * Init job from the message
     *
     * @param JobMessage $msg
     */
    public function setJobFromMessage(JobMessage $msg)
    {
        $job = $this->createJob($msg->context->getJobId(), $msg->jobOptions);

        return $this->setJob($job);
    }

    /**
     * Create job thanks to jobFactory
     *
     * @param string $job
     * @param array $jobOptions
     *
     * @return JobInterface
     */
    private function createJob($job, array $jobOptions = array())
    {
        return $this->jobFactory->create($job, $jobOptions);
    }

    /**
     * @return JobMessage
     */
    private function getInitMessage()
    {
        $msg = new JobMessage(
            new JobContext(
                $this->getJob()->getName(),
                $this->getJob()->getOption('context')
            )
        );

        $msg->jobOptions = $this->getJob()->getOptions();

        return $msg;
    }

    /**
     * @param JobOutput $output
     *
     * @return JobMessage
     */
    private function createEndMsg(JobOutput $output)
    {
        $msg = clone $this->startMsg;
        
        $msg->output = $output->getData();
        $msg->pipe = $output->getPipe();

        return $msg;
    }

    /**
     * Build a graph on children execution order
     */
    private function buildGraph()
    {
        $children = $this->getJob()->getChildren();

        $this->jobGraph = new JobGraph(new \ArrayIterator(array_keys($children)));
    }
}