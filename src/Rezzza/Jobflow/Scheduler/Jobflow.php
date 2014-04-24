<?php

namespace Rezzza\Jobflow\Scheduler;

use Psr\Log\LoggerInterface;
use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\JobInterface;
use Rezzza\Jobflow\JobFactory;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\JobMessageFactory;
use Rezzza\Jobflow\JobContextFactory;
use Rezzza\Jobflow\Strategy\ClassicStrategy;
use Rezzza\Jobflow\Strategy\MessageStrategyInterface;

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

    protected $msgFactory;

    protected $ctxFactory;

    protected $executionFactory;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(
        TransportInterface $transport,
        JobFactory $jobFactory,
        JobMessageFactory $msgFactory,
        JobContextFactory $ctxFactory,
        ExecutionContextFactory $executionFactory,
        MessageStrategyInterface $strategy = null,
        LoggerInterface $logger = null
    )
    {
        $this->transport = $transport;
        $this->jobFactory = $jobFactory;
        $this->msgFactory = $msgFactory;
        $this->ctxFactory = $ctxFactory;
        $this->executionFactory = $executionFactory;
        $this->strategy = $strategy ?: new ClassicStrategy($jobFactory, $ctxFactory, $msgFactory);
        $this->logger = $logger;
    }

    /**
     * Init $job execution and execute it.
     */
    public function run($job, array $jobOptions = [], Io\IoDescriptor $io = null)
    {
        if (is_string($job)) {
            $job = $this->createJob($job, $jobOptions);
        }

        if (!$job instanceof JobInterface) {
            throw new \InvalidArgumentException('Job should be a string or a JobInterface');
        }

        $graph = $this->buildGraph($job);
        $this->init($job, $graph, $io);
        $execution = $this->executionFactory->create($job, $graph);
        $this->execute($execution);

        return $this;
    }

    /**
     * Execute current step of $msg in $executionContext
     */
    public function executeMsg(JobMessage $msg, ExecutionContext $execution = null)
    {
        if (null === $execution) {
            $execution = $this->createJobExecutionFromMessage($msg);
        }

        return $msg->execute($execution, $this->msgFactory);
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

    protected function buildGraph(JobInterface $job)
    {
        return new JobGraph($job);
    }

    protected function execute(ExecutionContext $execution)
    {
        while ($msg = $this->wait()) {
            // In RabbitMQ mode, main script will be blocked on wait method
            // When rpcClient will anwser, $msg will be an array of replies from it.
            // As The worker handles the 'executeMsg' method and the rpc client (injected in RabbitMqTransport) the 'handleMsg' method
            // so we no longer need any more step at this point, so 'return' break point here is not a mistake ;)
            if (!$msg instanceof JobMessage) {
                return;
            }

            $msg = $this->executeMsg($msg, $execution);
            $this->handleMsg($msg);
        }
    }

    protected function handleMsg(JobMessage $msg)
    {
        $msgs = $this->strategy->handle($msg);

        foreach ($msgs as $msg) {
            $this->push($msg);
        }

        return $this;
    }

    /**
     * From the IO, create the first message(s) to initialize the job and then run its execution
     */
    protected function init(JobInterface $job, JobGraph $graph, Io\IoDescriptor $io = null)
    {
        $contexts = [];
        $inputs = $this->buildInputs($io);

        foreach ($inputs as $input) {
            $contexts[] = $this->ctxFactory->create($job, $input, $graph->current(), $this->transport);
        }

        $msgs = $this->msgFactory->createInitMsgs($contexts);
        foreach ($msgs as $msg) {
            $this->push($msg);
        }
    }

    protected function buildInputs(Io\IoDescriptor $io = null)
    {
        $stdin = $io ? $io->getStdin() : null;

        if (null === $stdin) {
            // If no IO defined, we want to keep the loop over results of this method.
            // So we return explicitely an array with only null value
            return [null];
        }

        if (!$stdin instanceof \Traversable) {
            return [$io];
        }

        // If Stdin is traversable we create a separated IO for each input.
        // Thus we will be able to create a message for each input.
        $inputs = [];
        $stdout = $io ? $io->getStdout() : null;

        foreach ($stdin as $input) {
            $inputs[] = new Io\IoDescriptor($input, $stdout);
        }

        return $inputs;
    }

    protected function push(JobMessage $msg)
    {
        if (null !== $this->logger) {
            $msg->logState($this->logger);
        }

        $this->transport->addMessage($msg);

        return $this;
    }

    private function createJobExecutionFromMessage(JobMessage $msg)
    {
        $job = $msg->recoverJob($this->jobFactory);
        $graph = $this->buildGraph($job);

        return $this->executionFactory->create($job, $graph);
    }

    /**
     * @param string $job
     */
    private function createJob($job, array $jobOptions = [])
    {
        return $this->jobFactory->create($job, $jobOptions);
    }

    private function wait()
    {
        return $this->transport->getMessage();
    }
}
