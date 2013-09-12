<?php

namespace Rezzza\JobFlow\Tests\Units;

use mageekguy\atoum as Units;

use Rezzza\JobFlow\JobContext as TestedClass;

class JobContext extends Units\Test
{
    public function test_it_updates_to_the_next_job()
    {
        $this
            ->if($this->mockGenerator->orphanize('__construct'))
            ->and($mockGraph = new \mock\Rezzza\JobFlow\Scheduler\JobGraph)
            ->and($mockGraph->getMockController()->hasNextJob = true)
            ->and($mockGraph->getMockController()->getNextJob = 'next')
            ->and($context = new TestedClass('job'))
            ->then($context->updateToNextJob($mockGraph))

                ->variable($context->getCurrent())
                    ->isEqualTo('next')

                ->mock($mockGraph)
                    ->call('getNextJob')
                    ->once()
        ;
    }

    public function test_it_returns_previous_step()
    {
        $this
            ->if($this->mockGenerator->orphanize('__construct'))
            ->and($mockGraph = new \mock\Rezzza\JobFlow\Scheduler\JobGraph)
            ->and($mockGraph->getMockController()->hasNextJob = true)
            ->and($mockGraph->getMockController()->getNextJob = 'next')
            ->and($context = new TestedClass('job'))
            ->and($context->setCurrent('first'))
            ->then($context->updateToNextJob($mockGraph))

                ->variable($context->getPrevious())
                    ->isEqualTo('first')

            ->then($context->updateToNextJob($mockGraph))

                ->variable($context->getPrevious())
                    ->isEqualTo('next')
        ;
    }

    public function test_it_indicates_it_is_finished()
    {
        $this
            ->if($context = new TestedClass('job'))
            ->and($context->setOption('offset', 0))
            ->then($context->setOption('total', 10))
                ->boolean($context->isFinished())
                    ->isFalse()

            ->if($context->setOption('offset', 10))
                ->boolean($context->isFinished())
                    ->isTrue()
        ;
    }

    public function test_it_indicates_it_is_starting()
    {
        $this
            ->if($this->mockGenerator->orphanize('__construct'))
            ->and($mockGraph = new \mock\Rezzza\JobFlow\Scheduler\JobGraph)
            ->and($mockGraph->getMockController()->hasNextJob = true)
            ->and($mockGraph->getMockController()->getNextJob = 'next')
            
            ->then($context = new TestedClass('job'))
                ->boolean($context->isStarting())
                    ->isTrue()

            ->if($context->updateToNextJob($mockGraph))
                ->boolean($context->isStarting())
                    ->isFalse()
        ;
    }

    public function test_it_gets_message_name()
    {
        $this
            ->if($context = new TestedClass('job'))
            ->then($context->setCurrent('first'))
                ->variable($context->getMessageName())
                    ->isEqualTo('job.first')
        ;
    }
}