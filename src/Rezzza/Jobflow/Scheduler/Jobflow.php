<?php

namespace Rezzza\Jobflow\Scheduler;

use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\Io\Input;
use Rezzza\Jobflow\JobContext;
use Rezzza\Jobflow\JobInterface;
use Rezzza\Jobflow\JobFactory;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;
use Rezzza\Jobflow\Strategy\ClassicStrategy;

/**
 * Handles job execution
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Jobflow
{
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
     * @var MessageStrategyInterface
     */
    protected $strategy;

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

    public function getStrategy()
    {
        if (null === $this->strategy) {
            $this->strategy = new ClassicStrategy();
        }

        return $this->strategy;
    }

    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * @param String | JobInterface | JobExecution $job
     *
     * @return Jobflow
     */
    public function execute($job, array $jobOptions = array(), $io = null)
    {
        if (is_string($job)) {
            $job = $this->createJob($job, $jobOptions);
        }

        if ($job instanceof JobInterface) {
            $job = new JobExecution($job, $io);
        }

        if (!$job instanceof JobExecution) {
            throw new \InvalidArgumentException('Job should be a string, a JobInterface or a JobExecution');
        }

        $messages = $job->getMessages();

        foreach ($messages as $msg) {
            $this->addMessage($msg);
        }
        $this->run($job);

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
     * Handles a message in args or look to the transport for next one
     *
     * @param JobMessage|null $msg
     */
    public function run($jobExecution = null)
    {
        $result = null;

        while ($msg = $this->wait()) {
            if (!$msg instanceof JobMessage) {
                return;
            }

            $result = $this->runJob($msg, $jobExecution);

            if ($result instanceof JobMessage) {
                $this->handleMessage($result, $jobExecution);
            }
        }

        return $result;
    }

    /**
     * Executes current job.
     * Injects ExecutionContext as Visitor
     *
     * @param JobMessage $msg
     */
    public function runJob(JobMessage $msg, $jobExecution = null)
    {
        if (null === $jobExecution) {
            $jobExecution = $this->createJobExecutionFromMessage($msg);
        }

        // Store input message
        $this->startMsg = $msg;
        $endMsg = clone $msg;
        $endMsg->reset();

        $jobExecution->getJobGraph()->move($msg->context->getCurrent());
        $context = new ExecutionContext(
            new JobInput($this->startMsg),
            new JobOutput($endMsg)
        );
        $output = $context->executeJob($jobExecution->getJob());

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
     * Need to work on this method to make it more simple and readable
     */
    public function handleMessage($msg, $jobExecution = null)
    {
        if (!$msg instanceof JobMessage) {
            if ($this->logger) {
                $this->logger->info('No more message');
            }

            return false;
        }

        if (null === $jobExecution) {
            $jobExecution = $this->createJobExecutionFromMessage($msg);
        }

        if ($this->logger) {
            $this->logger->info(sprintf(
                'Read message for job [%s] : %s => %s',
                $msg->context->getJobId(),
                $msg->context->getCurrent(),
                json_encode($msg->context->getOptions())
            ));
        }

        $this->getStrategy()->handle($this, $jobExecution, $msg);

        return $this;
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
     * Init job from the message
     *
     * @param JobMessage $msg
     */
    public function createJobExecutionFromMessage(JobMessage $msg)
    {
        return new JobExecution($this->createJob($msg->context->getJobId(), $msg->jobOptions));
    }

    public function forwardPipeMessage($msg, $graph)
    {
        foreach ($msg->pipe->params as $pipe) {
            $graph = clone $graph;
            $forward = clone $msg;
            $forward->context->initOptions();
            $forward->pipe = $pipe;
            $forward->context->updateToNextJob($graph);
            $forward->context->setOrigin($forward->context->getCurrent());
            $this->addMessage($forward);
        }
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
}