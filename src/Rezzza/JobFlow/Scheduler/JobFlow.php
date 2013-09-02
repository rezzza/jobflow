<?php

namespace Rezzza\JobFlow\Scheduler;

use Psr\Log\LoggerAwareTrait;

use Rezzza\JobFlow\JobContext;
use Rezzza\JobFlow\JobInterface;
use Rezzza\JobFlow\JobMessage;
use Rezzza\JobFlow\JobOutput;
use Rezzza\JobFlow\Scheduler\ExecutionContext;
use Rezzza\JobFlow\Scheduler\Transport\TransportInterface;

/**
 * Handles job execution
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobFlow
{
    use LoggerAwareTrait;

    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
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

    /**
     * @param JobInterface $job
     *
     * @return JobFlow
     */
    public function setJob(JobInterface $job)
    {
        $this->job = $job;
        $this->buildGraph();
        $this->job->setLocked(true);

        return $this;
    }

    /**
     * Adds message in transport layer
     *
     * @param JobMessage $msg
     *
     * @return JobFlow
     */
    public function addMessage(JobMessage $msg)
    {
        $this->transport->addMessage($msg, $msg->context->getMessageName());

        return $this;
    }

    /**
     * Creates init message to start execution.
     *
     * @return JobFlow
     */
    public function init()
    {
        if (null === $this->getJob()) {
            throw new \RuntimeException('You need to set a job');
        }
        
        $name = sprintf('%s.%s', $this->getJob()->getName(), $this->jobGraph->current());

        $this->transport->addMessage($this->getInitMessage(), $name);

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
            $result = $this->runJob($msg);
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
    protected function runJob(JobMessage $msg)
    {
        // Store input message
        $this->startMsg = $msg;

        $context = new ExecutionContext(
            $this->startMsg,
            $this->jobGraph
        );

        if (null !== $this->logger) {
            $context->setLogger($this->logger);
        }

        $output = $context->executeJob($this->job);

        // Check if $context ended
        if ($context->msg->context->isFinished()) {
            return;
        }

        // Event ? To handle createEndMsg in a more readable way ?
        return $this->transport->store($this->createEndMsg($output));
    }

    /**
     * @return JobMessage
     */
    private function getInitMessage()
    {
        return new JobMessage(new JobContext($this->getJob()->getName()));
    }

    /**
     * @param JobOutput $output
     *
     * @return JobMessage
     */
    private function createEndMsg(JobOutput $output)
    {
        $msg = clone $this->startMsg;
        
        $msg->setData($output->getData());
        $msg->context->updateToNextJob($this->jobGraph);

        return $msg;
    }

    /**
     * Build a graph on children execution order
     */
    private function buildGraph()
    {
        $children = $this->getJob()->getChildren();

        // For the moment, jobGraph is built following add methods calls
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