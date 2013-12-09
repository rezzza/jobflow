<?php

namespace Rezzza\Jobflow\Scheduler;

use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\JobContext;
use Rezzza\Jobflow\JobMessage;

class JobExecution
{
    protected $job;

    protected $io;

    protected $jobGraph;

    public function __construct($job, $io = null)
    {
        $this->job = $job;
        $this->io = $io;

        $this->buildGraph();
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

    public function getMessages()
    {
        $messages = [];

        if (null !== $this->io) {
            if ($this->io->getStdin() instanceof \Traversable) {
                foreach ($this->io->getStdin() as $input) {
                    $msg = $this->getInitMessage();
                    $msg->context->setIo(
                        new Io\IoDescriptor($input, $this->io->getStdout())
                    );
                    $messages[] = $msg;
                }
            } else {
                $msg = $this->getInitMessage();
                $msg->context->setIo($this->io);
                $messages[] = $msg;
            }
        } else {
            $messages[] = $this->getInitMessage();
        }

        return $messages;
    }

    /**
     * @return JobMessage
     */
    protected function getInitMessage()
    {
        $msg = new JobMessage(
            new JobContext(
                $this->getJob()->getName(),
                $this->getJob()->getConfig()->getOption('context', array()),
                $this->getJobGraph()->current()
            )
        );

        $msg->context->setOrigin($this->getJobGraph()->current());
        $msg->jobOptions = $this->getJob()->getOptions();

        return $msg;
    }

    /**
     * Build a graph on children execution order
     */
    protected function buildGraph()
    {
        $children = $this->getJob()->getChildren();
        $this->jobGraph = new JobGraph(new \ArrayIterator(array_keys($children)));
    }
}