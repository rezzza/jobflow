<?php

namespace Rezzza\Jobflow\Extension\ETL\Processor;

use Knp\ETL\ExtractorInterface;
use Knp\ETL\TransformerInterface;
use Knp\ETL\LoaderInterface;
use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\Extension\ETL\Processor\ETLProcessor;
use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Processor\ProcessorConfig;

class ETLProcessorConfig extends ProcessorConfig
{
    /**
     * Classname of the proxy which controls Processor execution regarding Extraction, Transformation or Loading step.
     */
    protected $proxy;

    public function __construct($class, $args, $calls, $proxy)
    {
        parent::__construct($class, $args, $calls);

        $this->proxy = $proxy;
    }

    public function createProcessor(MetadataAccessor $metadataAccessor, LoggerInterface $logger = null)
    {
        $processor = $this->createObject($this->class, $this->args);

        if (
            ! $processor instanceof ExtractorInterface &&
            ! $processor instanceof TransformerInterface &&
            ! $processor instanceof LoaderInterface
        ) {
            throw new \InvalidArgumentException('In ETL execution, $processor should be an Extractor, Transformer or Loader');
        }

        if (method_exists($processor, 'setLogger')) {
            $processor->setLogger($logger);
        }

        // For ETLProcessor, we build a proxy around processor to wrap its execution regarding a defined process
        // Proxy should be ETLProcessor
        return $this->createProxy($processor, $metadataAccessor, $logger);
    }

    protected function createProxy($processor, MetadataAccessor $metadataAccessor, LoggerInterface $logger = null)
    {
        $proxy = $this->createObject(
            $this->proxy,
            [
                $processor,
                $metadataAccessor,
                $logger
            ]
        );

        if (! $proxy instanceof ETLProcessor) {
            throw new \InvalidArgumentException('$proxy classname in ETLProcessorConfig should extends Rezzza\Jobflow\Extension\ETL\Processor\ETLProcessor');
        }

        return $proxy;
    }
}
