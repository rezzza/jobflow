<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Extractor;

use Knp\ETL;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\Extension\ETL\Type\ETLType;
use Rezzza\JobFlow\JobInput;
use Rezzza\JobFlow\JobOutput;
use Rezzza\JobFlow\Scheduler\ExecutionContext;

class ExtractorType extends ETLType
{
    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
    {
        $extractor = $input->getExtractor();

        if ($this->isLoggable($extractor) && $execution->getLogger()) {
            $extractor->setLogger($execution->getLogger());
        }

        if (null === $execution->getGlobalOption('total')) {
            $execution->setGlobalOption('total', $extractor->count());
        }

        $offset = $execution->getGlobalOption('offset');
        $limit = $execution->getGlobalOption('limit');

        try {
            $extractor->seek($offset);
        } catch (\OutOfBoundsException $e) {
            if ($execution->getLogger()) {
                $execution->getLogger()->debug('No data');
            }
        }

        $etl = new ETL\Context\Context();

        // Skip Header if needed
        if ($offset === 0 && $execution->getJobOption('skip_headers')) {
            // We need to get the current to do the next. Va comprendre charles
            $extractor->current();
            $extractor->next();
        }

        for ($i = 0; $i < $limit && $extractor->valid(); $i++) {
            $output->write($extractor->current());
            $extractor->next();
        }

        return $output;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'skip_headers' => false,
            'args' => function(Options $options) {
                $io = $options['io'];

                return array(
                    'filename' => $io->stdin->getDsn()
                );
            } 
        ));
    }

    public function getName()
    {
        return 'extractor';
    }

    public function getETLType()
    {
        return self::TYPE_EXTRACTOR;
    }
}