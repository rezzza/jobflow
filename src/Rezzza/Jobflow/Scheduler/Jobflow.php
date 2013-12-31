<?php

namespace Rezzza\Jobflow\Scheduler;

use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\JobInterface;
use Rezzza\Jobflow\JobFactory;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\JobMessageFactory;
use Rezzza\Jobflow\Strategy\ClassicStrategy;

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
        $this->msgFactory = new JobMessageFactory();
    }

    /**
     * Init $job execution and execute it.
     */
    public function run($job, array $jobOptions = [], Io\IoDescriptor $io = null)
    {
        if (is_string($job)) {
            $job = $this->createJob($job, $jobOptions);
        }

        if ($job instanceof JobInterface) {
            $job = new ExecutionContext($job);
        }

        if (!$job instanceof ExecutionContext) {
            throw new \InvalidArgumentException('Job should be a string, a JobInterface or a JobExecution');
        }

        $this->init($job, $io);
        $this->execute($job);

        return $this;
    }

    /**
     * Execute current step of $msg in $executionContext
     */
    public function executeMsg(JobMessage $msg, ExecutionContext $execution = null)
    {
        if (null === $execution) {
            $execution = $this->createJobExecutionFromStartMessage($msg);
        }

        return $execution->execute($msg, $this->msgFactory);
    }

    /**
     * Handle msg after its execution. If we need to create a new msg to continue execution or stop here
     */
    public function handle($msg)
    {
        if (!$msg instanceof JobMessage) {
            if ($this->logger) {
                $this->logger->info('No more message');
            }

            return false;
        }

        return $this->handleMsg($msg);
    }

    protected function execute(ExecutionContext $execution)
    {
        while ($msg = $this->wait()) {
            // In RabbitMQ mode, $msg will be empty and it is normal.
            // The worker handles the 'executeMsg' method and the rpc client (injected in RabbitMqTransport) the 'handleMsg' method
            // so we no longer need any more step at this point, so 'return' break point here is not a mistake ;)
            if (!$msg instanceof JobMessage) {
                return;
            }

            $msg = $this->executeMsg($msg, $execution);
            $this->handleMsg($msg, $execution);
        }
    }

    protected function handleMsg(JobMessage $msg, ExecutionContext $execution = null)
    {
        if (null === $execution) {
            $execution = $this->createJobExecutionFromEndMessage($msg);
        }

        $execution->logState($this->logger);
        $msgs = $this->getStrategy()->handle($execution, $this->msgFactory);

        foreach ($msgs as $msg) {
            $this->push($msg);
        }

        return $this;
    }

    protected function init(ExecutionContext $execution, Io\IoDescriptor $io = null)
    {
        $msgs = $execution->createInitMsgs($this->msgFactory, $io, $this->transport->getName());

        foreach ($msgs as $msg) {
            $this->push($msg);
        }
    }

    protected function push(JobMessage $msg)
    {
        $msg->logState($this->logger);

        $this->transport->addMessage($msg);

        return $this;
    }

    protected function getStrategy()
    {
        if (null === $this->strategy) {
            $this->strategy = new ClassicStrategy();
        }

        return $this->strategy;
    }

    private function createJobExecutionFromStartMessage(JobMessage $msg)
    {
        return $msg->createStartedJobExecution($this->jobFactory);
    }

    private function createJobExecutionFromEndMessage(JobMessage $msg)
    {
        return $msg->createEndedJobExecution($this->jobFactory);
    }

    private function createJob($job, array $jobOptions = [])
    {
        return $this->jobFactory->create($job, $jobOptions);
    }

    private function wait()
    {
        return $this->transport->getMessage();
    }
}