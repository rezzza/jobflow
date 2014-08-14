<?php

namespace Rezzza\Jobflow\Tests\Units\Extension\ETL\Processor;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\Extension\ETL\Processor\ExtractorProxy as TestedClass;

class ExtractorProxy extends Units\Test
{
    public function test_out_of_bound_should_terminate_the_execution_and_write_nothing()
    {
        $this
            ->given(
                $extractor = new \mock\Rezzza\Jobflow\Tests\Fixtures\DummyExtractor,

                $metadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->hasNoTotal = false,
                $execution->getMockController()->terminate = false,
                $execution->getMockController()->valid = false,
                $execution->getMockController()->getContextMetadata = new \Rezzza\Jobflow\Metadata\Metadata,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\ExtractorProxy($extractor, $metadata),
                $proxy->getMockController()->slice->throw = new \OutOfBoundsException
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($execution)
                    ->call('terminate')
                    ->once()

                    ->call('write')
                    ->never()
        ;
    }

    public function test_slice_results_should_be_written()
    {
        $this
            ->given(
                $extractor = new \mock\Rezzza\Jobflow\Tests\Fixtures\DummyExtractor,

                $metadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->hasNoTotal = false,
                $execution->getMockController()->valid = false,
                $execution->getMockController()->write = false,
                $execution->getMockController()->getContextMetadata = new \Rezzza\Jobflow\Metadata\Metadata,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\ExtractorProxy($extractor, $metadata),
                $proxy->getMockController()->slice = array('jean', 'marc', 'ecureuil')
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($execution)
                    ->call('write')
                    ->withArguments('jean',  new \Rezzza\Jobflow\Metadata\Metadata)
                    ->once()
                    ->withArguments('marc',  new \Rezzza\Jobflow\Metadata\Metadata)
                    ->once()
                    ->withArguments('ecureuil',  new \Rezzza\Jobflow\Metadata\Metadata)
                    ->once()
        ;
    }

    public function test_slice_no_results_should_not_be_written()
    {
        $this
            ->given(
                $extractor = new \mock\Rezzza\Jobflow\Tests\Fixtures\DummyExtractor,

                $metadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->hasNoTotal = false,
                $execution->getMockController()->valid = false,
                $execution->getMockController()->write = false,
                $execution->getMockController()->getContextMetadata = new \Rezzza\Jobflow\Metadata\Metadata,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\ExtractorProxy($extractor, $metadata),
                $proxy->getMockController()->slice = array()
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($execution)
                    ->call('write')
                    ->never()
        ;
    }

    public function test_it_should_pass_total()
    {
        $this
            ->given(
                $extractor = new \mock\Rezzza\Jobflow\Tests\Fixtures\DummyExtractor,
                $extractor->getMockController()->count = 18,

                $metadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->hasNoTotal = true,
                $execution->getMockController()->changeTotal = true,
                $execution->getMockController()->valid = false,
                $execution->getMockController()->write = false,
                $execution->getMockController()->getContextMetadata = new \Rezzza\Jobflow\Metadata\Metadata,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\ExtractorProxy($extractor, $metadata),
                $proxy->getMockController()->slice = array()
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($execution)
                    ->call('write')
                    ->never()

                    ->call('changeTotal')
                    ->withArguments(18)
                    ->once()
        ;
    }

    public function test_slice_should_extract_from_offset_to_limit()
    {
        $this
            ->given(
                $extractor = new \mock\Rezzza\Jobflow\Tests\Fixtures\DummyExtractor,
                $extractor->getMockController()->valid = true,
                $extractor->getMockController()->seek = true,
                $extractor->getMockController()->extract[1] = 'jean',
                $extractor->getMockController()->extract[2] = 'marc',
                $extractor->getMockController()->extract[3] = 'ecureuil',
                $extractor->getMockController()->extract[4] = 'chuck',
                $extractor->getMockController()->extract[5] = 'testa',

                $metadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->getOffset = 15,
                $execution->getMockController()->getLimit = 5,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\ExtractorProxy($extractor, $metadata)
            )
            ->if(
                $result = $proxy->slice($execution, new \Rezzza\Jobflow\Metadata\Metadata)
            )
                ->mock($extractor)
                    ->call('extract')
                    ->exactly(5)

                    ->call('seek')
                    ->withArguments(15)
                    ->once()

                ->array($result)
                    ->isEqualTo(array('jean', 'marc', 'ecureuil', 'chuck', 'testa'))
        ;
    }

    public function test_slice_should_extract_from_offset_while_valid()
    {
        $this
            ->given(
                $extractor = new \mock\Rezzza\Jobflow\Tests\Fixtures\DummyExtractor,
                $extractor->getMockController()->valid = true,
                $extractor->getMockController()->valid[5] = false,
                $extractor->getMockController()->seek = true,
                $extractor->getMockController()->extract[1] = 'jean',
                $extractor->getMockController()->extract[2] = 'marc',
                $extractor->getMockController()->extract[3] = 'ecureuil',
                $extractor->getMockController()->extract[4] = 'chuck',

                $metadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->getOffset = 15,
                $execution->getMockController()->getLimit = 5,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\ExtractorProxy($extractor, $metadata)
            )
            ->if(
                $result = $proxy->slice($execution, new \Rezzza\Jobflow\Metadata\Metadata)
            )
                ->mock($extractor)
                    ->call('extract')
                    ->exactly(4)

                    ->call('seek')
                    ->withArguments(15)
                    ->once()

                ->array($result)
                    ->isEqualTo(array('jean', 'marc', 'ecureuil', 'chuck'))
        ;
    }
}
