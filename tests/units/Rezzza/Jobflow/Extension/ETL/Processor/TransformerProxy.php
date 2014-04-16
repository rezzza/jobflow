<?php

namespace Rezzza\Jobflow\Tests\Units\Extension\ETL\Processor;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\Extension\ETL\Processor\TransformerProxy as TestedClass;
use Rezzza\Jobflow\JobData;
use Rezzza\Jobflow\Metadata\Metadata;
use Rezzza\Jobflow\Extension\ETL\Context\ETLProcessorContext;

class TransformerProxy extends Units\Test
{
    public function test_no_data_should_not_be_written()
    {
        $this
            ->given(
                $metadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,
                $transformer = new \mock\Knp\ETL\TransformerInterface,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\TransformerProxy($transformer, $metadata),

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->read = [],
                $execution->getMockController()->valid = true
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($execution)
                    ->call('write')
                    ->never()
        ;
    }

    public function test_data_should_be_written()
    {
        $this
            ->given(
                $metadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,
                $transformer = new \mock\Knp\ETL\TransformerInterface,
                $context = new \mock\Knp\ETL\ContextInterface,

                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\TransformerProxy($transformer, $metadata),
                $proxy->getMockController()->transform[1] = 'call',
                $proxy->getMockController()->transform[2] = 'me',
                $proxy->getMockController()->transform[3] = 'maybe',

                $this->mockGenerator->orphanize('__construct'),
                $execution = new \mock\Rezzza\Jobflow\Scheduler\ExecutionContext,
                $execution->getMockController()->read = [
                    new JobData('jean', new Metadata),
                    new JobData('marc', new Metadata),
                    new JobData('ecureuil', new Metadata)
                ],
                $execution->getMockController()->valid = true,
                $execution->getMockController()->write = true
            )
            ->if(
                $proxy->execute($execution)
            )
                ->mock($proxy)
                    ->call('transform')
                        ->withArguments('jean', new ETLProcessorContext($execution, new Metadata))
                        ->once()

                        ->withArguments('marc', new ETLProcessorContext($execution, new Metadata))
                        ->once()

                        ->withArguments('ecureuil', new ETLProcessorContext($execution, new Metadata))
                        ->once()

                ->mock($execution)
                    ->call('write')
                        ->withArguments('call', new Metadata)
                        ->once()

                        ->withArguments('me', new Metadata)
                        ->once()

                        ->withArguments('maybe', new Metadata)
                        ->once()
        ;
    }

    /**
     * @dataProvider dataTransformer
     */
    public function test_transform_should_call_transform_processor($input, $output)
    {
        $this
            ->given(
                $metadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,
                $transformer = new \mock\Knp\ETL\TransformerInterface,
                $transformer->getMockController()->transform = $output,
                $context = new \mock\Knp\ETL\ContextInterface,
                $proxy = new \mock\Rezzza\Jobflow\Extension\ETL\Processor\TransformerProxy($transformer, $metadata)
            )
            ->if(
                $result = $proxy->transform($input, $context)
            )
                ->variable($result)
                    ->isEqualTo($output)
        ;
    }

    public function dataTransformer()
    {
        return [
            ['jean', 'marc'],
            [123456, 'chasseur'],
            [new \DateTime, 456]
        ];
    }
}
