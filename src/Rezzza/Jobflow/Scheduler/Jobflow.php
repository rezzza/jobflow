<?php

namespace Rezzza\Jobflow\Scheduler;

use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\Extension\Pipe\Pipe;
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

        // We can get back the job from the msg. So job does not have to be set before. 
        if (null === $this->job) {
            $this->setJobFromMessage($msg);
        }

        if ($this->logger) {
            $this->logger->info(sprintf(
                'Read message for job [%s] : %s => %s',
                $msg->context->getJobId(),
                $msg->context->getCurrent(),
                json_encode($msg->context->getOptions())
            ));
        }

        $current = $msg->context->getCurrent();

        // Move graph to the current value
        $this->jobGraph->move($current);

        // Gets the current job
        $child = $this->job->get($current);

        if ($msg->pipe instanceof Pipe) {
            foreach ($msg->pipe->params as $pipe) {
                $forward = clone $msg;
                $forward->context->initOptions();
                $forward->pipe = $pipe;
                $graph = clone $this->jobGraph;
                $forward->context->updateToNextJob($graph);
                $this->addMessage($forward);
            }

            // Reset pipe as we already ran through above
            $msg->pipe = array();
        } 

        if ($child->isLoader() || $this->jobGraph->isLast($current)) {
            $msg->context->tick();

            if (!$msg->context->isFinished()) {
                // When loader we add msg to the requeue to the previous extractor
                $extractor = false;

                while (false === $extractor) {
                    $previous = $this->jobGraph->getPreviousJob();
                    $jobPrevious = $this->job->get($previous);

                    if ($jobPrevious->isExtractor()) {
                        $extractor = $previous;
                    }

                    $this->jobGraph->move($previous);
                }

                $msg->context->addStep($current);
                $msg->context->setCurrent($extractor);
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
        $endMsg = clone $msg;
        $endMsg->reset();

        $this->jobGraph->move($msg->context->getCurrent());
        $context = new ExecutionContext($this->startMsg, $endMsg);
        $output = $context->executeJob($this->job);

        // Event ? To handle createEndMsg in a more readable way ?
        if (!$output instanceof JobOutput) {
            return $output;
        }

        // If $output is ended (no data for example)
        if ($output->isEnded()) {
            return null;
        }

        return $output->getMessage();
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
                $this->getJob()->getConfig()->getOption('context'), // Should be inject in job
                $this->jobGraph->current()
            )
        );

        $msg->jobOptions = $this->getJob()->getOptions();

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