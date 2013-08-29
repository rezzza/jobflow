<?php

namespace Rezzza\JobFlow\Scheduler;

use Psr\Log\LoggerAwareTrait;

use Rezzza\JobFlow\JobContext;
use Rezzza\JobFlow\JobInterface;
use Rezzza\JobFlow\JobMessage;
use Rezzza\JobFlow\Scheduler\ExecutionContext;
use Rezzza\JobFlow\Scheduler\Strategy\StrategyInterface;
use Rezzza\JobFlow\Scheduler\Transport\TransportInterface;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobScheduler
{
    use LoggerAwareTrait;

    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var StrategyInterface
     */
    protected $strategy;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @param TransportInterface $transport
     * @param StrategyInterface $strategy
     */
    public function __construct(TransportInterface $transport, StrategyInterface $strategy = null)
    {
        $this->transport = $transport;
        $this->strategy = $strategy;
    }

    /**
     * @return TransportInterface
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @return StrategyInterface
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

    public function getIo()
    {
        return $this->io;
    }

    public function setIo(IoDescriptor $io)
    {
        $this->io = $io;
    }

    /**
     * @param JobInterface $job
     *
     * @return JobScheduler
     */
    public function setJob(JobInterface $job)
    {
        $this->job = $job;
        $this->buildGraph();
        $this->job->setLocked(true);

        return $this;
    }

    public function init()
    {
        if (null === $this->getJob()) {
            throw new \RuntimeException('You need to set a job');
        }
        
        $name = sprintf('%s.%s', $this->getJob()->getName(), $this->jobGraph->current());

        $this->transport->addMessage($this->getInitMessage(), $name);

        return $this;
    }

    public function run($msg = null)
    {
        if (null !== $msg) {
            return $this->runJob($msg);
        }

        while ($msg = $this->transport->getMessage()) {
            $result = $this->runJob($msg);
        }

        return $result;
    }

    public function wait()
    {
        return $this->transport->getMessage();
    }

    public function addMessage(JobMessage $msg)
    {
        $this->transport->addMessage($msg, $msg->context->getMessageName());

        return $this;
    }

    protected function runJob($msg)
    {
        if (!$msg instanceof JobMessage) {
            return;
        }

        $context = new ExecutionContext(
            $this->job,
            $msg,
            $this->jobGraph
        );

        if (null !== $this->logger) {
            $context->setLogger($this->logger);
        }

        return $this->transport->store($context->executeJob($this->job));
    }

    private function getInitMessage()
    {
        return new JobMessage(new JobContext($this->getJob()->getName()));
    }

    /**
     * Build a graph on children execution order
     */
    private function buildGraph()
    {
        $children = $this->getJob()->getChildren();

        /*uasort($children, function($a, $b) {
            $stdinA = $a->getResolved()->getIo()->stdin;
            $stdinB = $b->getResolved()->getIo()->stdin;

            if ($stdinA && $stdinA->isFirstStep()) {
                return -1;
            }

            if ($stdinB && $stdinB->isFirstStep()) {
                return 1;
            }

            if ($stdinB && $stdinB->isConnectedTo($a->getName())) {
                return -1;
            }

            if ($stdinA && $stdinB && $stdinA->getDsn() === $stdinB->getDsn()) {
                return 0;
            }

            if ($stdinA && $stdinA->isConnectedTo($b->getName())) {
                return 1;
            }
        });

        $i = 0;
        foreach ($children as $child) {
            $stdin = $child->getResolved()->getIo()->stdin;

            if ($i++ > 0) {
                if (false === $stdin->isConnectedTo($last->getName())) {
                    throw new \RuntimeException('Job Graph is not consistent');
                }
            }

            $last = $child;
        }*/

        $this->jobGraph = new JobGraph(new \ArrayIterator(array_keys($children)));
    }
}