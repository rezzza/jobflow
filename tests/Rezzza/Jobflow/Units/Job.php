<?php

namespace Rezzza\Jobflow\Tests\Units;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\Job as TestedClass;

class Job extends Units\Test
{
    public function test_it_should_have_a_child()
    {
        $this
            ->given(
                $this->mockGenerator->orphanize('__construct'),
                $mockConfig = new \mock\Rezzza\Jobflow\JobConfig,
                $mockConfig->getMockController()->getName = 'child',
                $job = new TestedClass($mockConfig),
                $child = new TestedClass($mockConfig)
            )
            ->if($job->add($child))
                ->object($job->get('child'))
                    ->isIdenticalTo($child)
        ;
    }

    public function test_it_should_throw_exception_on_incorrect_child_name()
    {
        $this
            ->given(
                $this->mockGenerator->orphanize('__construct'),
                $mockConfig = new \mock\Rezzza\Jobflow\JobConfig,
                $mockConfig->getMockController()->getName = 'test'
            )
            ->if(
                $job = new TestedClass($mockConfig)
            )
                ->exception(function() use ($job) {
                    $job->get('test');
                })
                ->hasMessage('No child with name : "test" in job "test"')
        ;
    }

    public function test_job_callable_execution_should_spread_events()
    {
        $this
            ->given(
                $mockED = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface,
                $mockED->getMockController()->hasListeners = true,
                $this->mockGenerator->orphanize('__construct'),
                $mockExecution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $mockExecution->getMockController()->write = true,
                $this->mockGenerator->orphanize('__construct'),
                $mockConfig = new \mock\Rezzza\Jobflow\JobConfig,
                $mockConfig->getMockController()->getProcessorConfig = function () { return function ($e) { return $e->write('toto'); }; },
                $mockConfig->getMockController()->getEventDispatcher = $mockED,
                $mockConfig->getMockController()->resolveExecOptions = ['jean' => 'marc']
            )
            ->if(
                $job = new TestedClass($mockConfig),
                $job->execute($mockExecution)
            )

            ->mock($mockExecution)
                ->call('write')
                ->withArguments('toto')
                ->after(
                    $this
                        ->mock($mockED)
                            ->call('hasListeners')
                            ->withArguments('job.pre_execute')
                            ->once()

                            ->call('dispatch')
                            ->withArguments('job.pre_execute', new \Rezzza\Jobflow\Event\JobEvent($job, $mockExecution))
                            ->once()
                )
                ->before(
                    $this
                        ->mock($mockED)
                            ->call('hasListeners')
                            ->withArguments('job.post_execute')
                            ->once()

                            ->call('dispatch')
                            ->withArguments('job.post_execute', new \Rezzza\Jobflow\Event\JobEvent($job, $mockExecution))
                            ->once()
                )
                ->once()
        ;
    }

    public function test_job_processor_execution_should_spread_events()
    {
        $this
            ->given(
                $mockED = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface,
                $mockED->getMockController()->hasListeners = true,

                $this->mockGenerator->orphanize('__construct'),
                $mockExecution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,

                $mockJobProcessor = new \mock\Rezzza\Jobflow\Processor\JobProcessor,

                $this->mockGenerator->orphanize('__construct'),
                $mockProcessor = new \mock\Rezzza\Jobflow\Processor\ProcessorConfig,
                $mockProcessor->getMockController()->createProcessor = $mockJobProcessor,

                $this->mockGenerator->orphanize('__construct'),
                $mockMetadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $mockConfig = new \mock\Rezzza\Jobflow\JobConfig('alphonse', $mockED),
                $mockConfig->getMockController()->getProcessorConfig = $mockProcessor,
                $mockConfig->getMockController()->getMetadataAccessor = $mockMetadata,
                $mockConfig->getMockController()->resolveExecOptions = ['jean' => 'marc']
            )
            ->if(
                $job = new TestedClass($mockConfig),
                $job->execute($mockExecution)
            )

            ->mock($mockJobProcessor)
                ->call('execute')
                ->withArguments($mockExecution)
                ->after(
                    $this
                        ->mock($mockED)
                            ->call('hasListeners')
                            ->withArguments('job.pre_execute')
                            ->once()

                            ->call('dispatch')
                            ->withArguments('job.pre_execute', new \Rezzza\Jobflow\Event\JobEvent($job, $mockExecution))
                            ->once()
                )
                ->before(
                    $this
                        ->mock($mockED)
                            ->call('hasListeners')
                            ->withArguments('job.post_execute')
                            ->once()

                            ->call('dispatch')
                            ->withArguments('job.post_execute', new \Rezzza\Jobflow\Event\JobEvent($job, $mockExecution))
                            ->once()
                )
                ->once()
        ;
    }

    public function test_invalid_processor_should_fail()
    {
        $this
            ->given(
                $mockED = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface,
                $mockED->getMockController()->hasListeners = true,

                $this->mockGenerator->orphanize('__construct'),
                $mockExecution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,

                $mockConfig = new \mock\Rezzza\Jobflow\JobConfig('alphonse', $mockED),
                $mockConfig->getMockController()->getProcessorConfig = 'hey you',
                $mockConfig->getMockController()->resolveExecOptions = ['jean' => 'marc']
            )
            ->if(
                $job = new TestedClass($mockConfig)
            )
            ->exception(function () use ($job, $mockExecution) {
                $job->execute($mockExecution);
            })
                ->hasMessage('processor should be a ProcessorConfig or a callable')
        ;
    }
}
