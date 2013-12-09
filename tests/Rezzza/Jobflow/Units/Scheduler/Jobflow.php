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

    public function test_add_message()
    {
        $this
            ->if($flow = $this->getMockJobflow())
            ->and($this->transport->getMockController()->addMessage = true)
            ->and($msg = $this->getMockMsg())
            ->then($flow->addMessage($msg))

                ->mock($this->transport)
                    ->call('addMessage')
                    ->withArguments($msg)
                    ->once()
        ;
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
        return new \mock\Rezzza\Jobflow\JobMessage(null);
    }
}