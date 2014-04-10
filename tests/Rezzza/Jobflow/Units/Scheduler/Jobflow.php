<?php

namespace Rezzza\Jobflow\Tests\Units\Scheduler;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\Scheduler\Jobflow as TestedClass;

class Jobflow extends Units\Test
{
    private $jobFactory;

    private $msgFactory;

    private $ctxFactory;

    private $transport;

    public function beforeTestMethod($method)
    {
        $registry = new \mock\Rezzza\Jobflow\JobRegistry(array());
        $this->jobFactory = new \mock\Rezzza\Jobflow\JobFactory($registry);
        $this->msgFactory = new \mock\Rezzza\Jobflow\JobMessageFactory;
        $this->ctxFactory = new \mock\Rezzza\Jobflow\JobContextFactory;
        $this->executionFactory = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContextFactory;
        $this->transport = new \mock\Rezzza\Jobflow\Scheduler\TransportInterface;
        $this->strategy = new \mock\Rezzza\Jobflow\Strategy\MessageStrategyInterface;
    }

    public function test_it_should_run_with_a_job_and_no_io()
    {
        $this
            ->given(
                $jobflow = $this->getMockJobflow(),
                $msgStart = $this->getMockMsg(),
                $msgEnd = $this->getMockMsg(),
                $msgNext = $this->getMockMsg(),
                $jobflow->getMockController()->executeMsg = $msgEnd,
                $this->strategy->getMockController()->handle = [$msgNext],
                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $this->executionFactory->getMockController()->create = $execution,
                $this->transport->getMockController()->getMessage[1] = $msgStart,
                $this->transport->getMockController()->getMessage[2] = false,
                $job = $this->getMockJob(),
                $context = new \mock\Rezzza\Jobflow\JobContext('id'),
                $this->ctxFactory->getMockController()->create = $context,
                $this->msgFactory->getMockController()->createInitMsgs = [$msgStart]
            )
            ->then(
                $jobflow->run($job, ['options1' => 'woot'])
            )
                ->mock($this->ctxFactory)
                    ->call('create')
                    ->withArguments($job, null, null, $this->transport)
                    ->once()

                ->mock($this->msgFactory)
                    ->call('createInitMsgs')
                    ->withArguments([$context])
                    ->once()

                ->mock($this->transport)
                    ->call('addMessage')
                    ->withArguments($msgStart)
                    ->once()

                ->mock($this->strategy)
                    ->call('handle')
                    ->withArguments($execution, $this->msgFactory)
                    ->once()

                ->mock($this->transport)
                    ->call('addMessage')
                    ->withArguments($msgNext)
                    ->once()
        ;
    }

    public function test_it_should_run_the_job_and_create_context_with_io()
    {
        $this
            ->given(
                $jobflow = $this->getMockJobflow(),
                $job = $this->getMockJob(),
                $this->mockGenerator->orphanize('__construct'),
                $io = new \mock\Rezzza\Jobflow\Io\IoDescriptor,
                $this->mockGenerator->orphanize('__construct'),
                $input = new \mock\Rezzza\Jobflow\Io\Input,
                $io->getMockController()->getStdin = $input,
                $context = new \mock\Rezzza\Jobflow\JobContext('id'),
                $this->ctxFactory->getMockController()->create = $context
            )
            ->then(
                $jobflow->run($job, ['options1' => 'woot'], $io)
            )
                ->mock($this->ctxFactory)
                    ->call('create')
                    ->withArguments($job, $io, null, $this->transport)
                    ->once()
        ;
    }

    public function test_it_should_run_the_job_and_create_context_with_multiple_io()
    {
        $this
            ->given(
                $jobflow = $this->getMockJobflow(),
                $job = $this->getMockJob(),
                $context = new \mock\Rezzza\Jobflow\JobContext('id'),
                $this->ctxFactory->getMockController()->create = $context
            )
            ->and(
                $file1 = new \Rezzza\Jobflow\Io\Driver\File("input1"),
                $file2 = new \Rezzza\Jobflow\Io\Driver\File("input2"),
                $input = new \mock\Rezzza\Jobflow\Io\Input($file1),
                $input2 = new \mock\Rezzza\Jobflow\Io\Input($file2),
                $inputAgregator = new \Rezzza\Jobflow\Io\InputAggregator([$input, $input2]),
                $io = new \mock\Rezzza\Jobflow\Io\IoDescriptor($inputAgregator)
            )
            ->then(
                $jobflow->run($job, ['options1' => 'woot'], $io)
            )
                ->mock($this->ctxFactory)
                    ->call('create')
                    ->withArguments($job, new \Rezzza\Jobflow\Io\IoDescriptor($input), null, $this->transport)
                    ->once()

                ->mock($this->ctxFactory)
                    ->call('create')
                    ->withArguments($job, new \Rezzza\Jobflow\Io\IoDescriptor($input2), null, $this->transport)
                    ->once()
        ;
    }

    public function test_it_should_loop_while_messages_are_stacked()
    {
        $this
            ->given(
                $jobflow = $this->getMockJobflow(),
                $msgStart = $this->getMockMsg(),
                $msgStart2 = $this->getMockMsg(),
                $msgEnd = $this->getMockMsg(),
                $msgNext = $this->getMockMsg(),
                $jobflow->getMockController()->executeMsg = $msgEnd,
                $this->strategy->getMockController()->handle = [$msgNext],
                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $this->executionFactory->getMockController()->create = $execution,
                $this->transport->getMockController()->getMessage[1] = $msgStart,
                $this->transport->getMockController()->getMessage[2] = $msgStart2,
                $this->transport->getMockController()->getMessage[3] = false,
                $job = $this->getMockJob(),
                $context = new \mock\Rezzza\Jobflow\JobContext('id'),
                $this->ctxFactory->getMockController()->create = $context,
                $this->msgFactory->getMockController()->createInitMsgs = [$msgStart, $msgStart2]
            )
            ->then(
                $jobflow->run($job, ['options1' => 'woot'])
            )
                ->mock($this->ctxFactory)
                    ->call('create')
                    ->withArguments($job, null, null, $this->transport)
                    ->once()

                ->mock($this->msgFactory)
                    ->call('createInitMsgs')
                    ->withArguments([$context])
                    ->once()

                ->mock($this->transport)
                    ->call('addMessage')
                    ->exactly(4)

                ->mock($jobflow)
                    ->call('executeMsg')
                    ->withArguments($msgStart, $execution)
                    ->once()

                ->mock($jobflow)
                    ->call('executeMsg')
                    ->withArguments($msgStart2, $execution)
                    ->once()
        ;
    }

    public function test_it_should_run_with_string_for_job()
    {
        $this
            ->given(
                $jobflow = $this->getMockJobflow(),
                $job = $this->getMockJob(),
                $context = new \mock\Rezzza\Jobflow\JobContext('id'),
                $this->ctxFactory->getMockController()->create = $context,
                $this->jobFactory->getMockController()->create = $this->getMockJob()
            )
            ->then(
                $jobflow->run('test', ['options1' => 'woot'])
            )
                ->mock($this->jobFactory)
                    ->call('create')
                    ->withArguments('test', ['options1' => 'woot'])
                    ->once()
        ;
    }

    public function test_it_should_not_run_with_invalid_type()
    {
        $this
            ->given(
                $jobflow = $this->getMockJobflow()
            )
            ->exception(function () use ($jobflow) {
                $jobflow->run(1000, ['options1' => 'woot']);
            })
                ->hasMessage('Job should be a string or a JobInterface')
        ;
    }

    public function test_it_should_create_execution_context_to_execute_msg()
    {
        $this
            ->given(
                $msg = $this->getMockMsg(),
                $jobflow = $this->getMockJobflow(),
                $job = $this->getMockJob(),
                $this->jobFactory->getMockController()->create = $job,
                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->execute = null,
                $this->executionFactory->getMockController()->create = $execution
            )
            ->then(
                $jobflow->executeMsg($msg)
            )
                ->mock($msg)
                    ->call('recoverJob')
                    ->withArguments($this->jobFactory)
                    ->once()

                ->mock($this->jobFactory)
                    ->call('create')
                    ->withArguments('my.id', [])
                    ->once()

                ->mock($execution)
                    ->call('execute')
                    ->withArguments($msg, $this->msgFactory)
                    ->once()
        ;
    }

    public function test_it_should_execute_msg_with_execution_context_given()
    {
        $this
            ->given(
                $msg = $this->getMockMsg(),
                $jobflow = $this->getMockJobflow(),
                $job = $this->getMockJob(),
                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $this->jobFactory->getMockController()->create = $job,
                $execution->getMockController()->execute = null,
                $this->executionFactory->getMockController()->create = $execution

            )
            ->then(
                $jobflow->executeMsg($msg, $execution)
            )
                ->mock($msg)
                    ->call('recoverJob')
                    ->never()

                ->mock($this->jobFactory)
                    ->call('create')
                    ->never()

                ->mock($execution)
                    ->call('execute')
                    ->withArguments($msg, $this->msgFactory)
                    ->once()
        ;
    }

    public function test_it_should_handle_real_msg()
    {
        $this
            ->given(
                $jobflow = $this->getMockJobflow(),
                $msgStart = $this->getMockMsg(),
                $msgNext = $this->getMockMsg(),
                $this->strategy->getMockController()->handle = [$msgNext],
                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->end = null,
                $this->executionFactory->getMockController()->create = $execution,
                $this->jobFactory->getMockController()->create = $this->getMockJob()
            )
            ->then(
                $jobflow->handle($msgStart)
            )
                ->mock($msgStart)
                    ->call('recoverJob')
                    ->withArguments($this->jobFactory)
                    ->once()

                ->mock($this->executionFactory)
                    ->call('create')
                    ->once()

                ->mock($this->strategy)
                    ->call('handle')
                    ->withArguments($execution, $this->msgFactory)
                    ->once()

                ->mock($this->transport)
                    ->call('addMessage')
                    ->withArguments($msgNext)
                    ->once()
        ;
    }

    public function test_it_should_not_handle_fake_msg()
    {
        $this
            ->given(
                $jobflow = $this->getMockJobflow()
            )
            ->then(
                $result = $jobflow->handle(true)
            )
                ->boolean($result)
                    ->isFalse()
        ;
    }

    private function getMockJobflow()
    {
        return new \mock\Rezzza\Jobflow\Scheduler\Jobflow(
            $this->transport,
            $this->jobFactory,
            $this->msgFactory,
            $this->ctxFactory,
            $this->executionFactory,
            $this->strategy
        );
    }

    private function getMockJob()
    {
        $dispatcher = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface;
        $config = new \mock\Rezzza\Jobflow\JobConfig('jean-marc', $dispatcher);

        return new \mock\Rezzza\Jobflow\Job($config);
    }

    private function getMockMsg()
    {
        $context = new \Rezzza\Jobflow\JobContext('my.id');
        $payload = new \Rezzza\Jobflow\JobPayload;

        return new \mock\Rezzza\Jobflow\JobMessage($context, $payload);
    }
}
