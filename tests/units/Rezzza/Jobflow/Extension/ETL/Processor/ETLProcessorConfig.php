<?php

namespace Rezzza\Jobflow\Tests\Units\Extension\ETL\Processor;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\Extension\ETL\Processor\ETLProcessorConfig as TestedClass;

class ETLProcessorConfig extends Units\Test
{
    public function test_processor_should_be_extractor_transformer_loader()
    {
        $this
            ->given(
                $mockMetadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,
                $etlProcessorConfig = new TestedClass('Rezzza\Jobflow\Tests\Fixtures\DummyProcessor', [], [], 'Rezzza\Jobflow\Tests\Fixtures\DummyProcessor')
            )
            ->exception(function () use ($etlProcessorConfig, $mockMetadata) {
                $etlProcessorConfig->createProcessor($mockMetadata);
            })
                ->hasMessage('In ETL execution, $processor should be an Extractor, Transformer or Loader')
        ;
    }

    public function test_proxy_should_be_etl_processor()
    {
        $this
            ->given(
                $mockMetadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,
                $etlProcessorConfig = new TestedClass('Rezzza\Jobflow\Tests\Fixtures\DummyExtractor', [], [], 'Rezzza\Jobflow\Tests\Fixtures\DummyProcessor')
            )
            ->exception(function () use ($etlProcessorConfig, $mockMetadata) {
                $etlProcessorConfig->createProcessor($mockMetadata);
            })
                ->hasMessage('$proxy classname in ETLProcessorConfig should extends Rezzza\Jobflow\Extension\ETL\Processor\ETLProcessor')
        ;
    }

    public function test_it_should_create_a_proxy_around_processor()
    {
        $this
            ->given(
                $mockMetadata = new \mock\Rezzza\Jobflow\Metadata\MetadataAccessor,
                $etlProcessorConfig = new TestedClass('Rezzza\Jobflow\Tests\Fixtures\DummyExtractor', [], [], 'Rezzza\Jobflow\Tests\Fixtures\DummyETLProcessor')
            )
            ->if(
                $proxy = $etlProcessorConfig->createProcessor($mockMetadata)
            )
                ->object($proxy)
                    ->isInstanceOf('Rezzza\Jobflow\Extension\ETL\Processor\ETLProcessor')
        ;
    }
}
