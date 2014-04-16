<?php

namespace Rezzza\Jobflow\Tests\Units\Extension\ETL\Processor;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\Extension\ETL\Processor\LoaderProxy as TestedClass;
use Rezzza\Jobflow\JobData;
use Rezzza\Jobflow\Metadata\Metadata;
use Rezzza\Jobflow\Extension\ETL\Context\ETLProcessorContext;

class LoaderProxy extends Units\Test
{
    public function test_no_data_should_not_be_loaded()
    {
        $this
            ->given(
                $loader = new \mock\Knp\ETL\LoaderInterface,
                $accessor = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->read = [],
                $execution->getMockController()->getJobOption[1] = null, // No property
                $execution->getMockController()->getJobOption[2] = true, // Requeue

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\LoaderProxy($loader, $accessor)
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($proxy)
                    ->call('load')
                    ->never()

                    ->call('flush')
                    ->never()

                    ->call('clear')
                    ->never()
        ;
    }

    public function test_data_should_be_loaded()
    {
        $this
            ->given(
                $loader = new \mock\Knp\ETL\LoaderInterface,
                $accessor = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->read = [
                    new JobData('jean', new Metadata),
                    new JobData('marc', new Metadata),
                    new JobData('ecureuil', new Metadata)
                ],
                $execution->getMockController()->getJobOption[1] = null, // No property
                $execution->getMockController()->getJobOption[2] = true, // Requeue

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\LoaderProxy($loader, $accessor),
                $proxy->getMockController()->load = true,
                $proxy->getMockController()->flush = true,
                $proxy->getMockController()->clear = true,

                $context = new ETLProcessorContext($execution, new Metadata)
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($proxy)
                    ->call('load')
                    ->withArguments('jean', $context)
                    ->once()

                    ->withArguments('jean', $context)
                    ->once()

                    ->withArguments('jean', $context)
                    ->once()

                    ->call('flush')
                    ->once()

                    ->call('clear')
                    ->once()
        ;
    }

    public function test_no_requeue_should_rewind_data()
    {
        $this
            ->given(
                $loader = new \mock\Knp\ETL\LoaderInterface,
                $accessor = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->read = [],
                $execution->getMockController()->getJobOption[1] = null, // No property
                $execution->getMockController()->getJobOption[2] = true, // Requeue
                $execution->getMockController()->rewindData = true,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\LoaderProxy($loader, $accessor)
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($execution)
                    ->call('rewindData')
                    ->never()
        ;
    }

    public function test_requeue_should_not_rewind_data()
    {
        $this
            ->given(
                $loader = new \mock\Knp\ETL\LoaderInterface,
                $accessor = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->read = [],
                $execution->getMockController()->getJobOption[1] = null, // No property
                $execution->getMockController()->getJobOption[2] = false, // Requeue
                $execution->getMockController()->rewindData = true,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\LoaderProxy($loader, $accessor)
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($execution)
                    ->call('rewindData')
                    ->once()
        ;
    }
}
