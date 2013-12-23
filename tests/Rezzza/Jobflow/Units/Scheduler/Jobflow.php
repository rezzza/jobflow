<?php

namespace Rezzza\Jobflow\Tests\Units\Scheduler;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\Scheduler\Jobflow as TestedClass;

class Jobflow extends Units\Test
{
    private $factory;

    private $transport;

    public function beforeTestMethod($method)
    {
        $registry = new \mock\Rezzza\Jobflow\JobRegistry(array());
        $this->factory = new \mock\Rezzza\Jobflow\JobFactory($registry);
        $this->transport = new \mock\Rezzza\Jobflow\Scheduler\TransportInterface;
    }

    private function getMockJobflow()
    {
        return new \mock\Rezzza\Jobflow\Scheduler\Jobflow($this->transport, $this->factory);
    }

    private function getMockJob()
    {
        $dispatcher = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface;
        $config = new \mock\Rezzza\Jobflow\JobConfig('jean-marc', $dispatcher);

        return new \mock\Rezzza\Jobflow\Job($config);
    }

    private function getMockMsg()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\Rezzza\Jobflow\JobMessage();
    }
}