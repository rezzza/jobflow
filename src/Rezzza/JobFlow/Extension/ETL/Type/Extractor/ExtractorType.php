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

        if (null === $execution->getOption('total')) {
            $execution->setGlobalOption('total', $extractor->count());
        }

        $offset = $execution->getOption('offset');
        $limit = $execution->getOption('limit');

        try {
            $extractor->seek($offset);
        } catch (\OutOfBoundsException $e) {
            $execution->getLogger()->debug('No data');
        }

        $etl = new ETL\Context\Context();

        // Skip Header
        if ($offset === 0) {
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

        $resolver->setRequired(array(
            'class'
        ));

        $resolver->setDefaults(array(
            'etl_config' => function(Options $options) {
                $io = $options['io'];

                return array(
                    'class' => $options['class'],
                    'args' => array($io->stdin->getDsn())
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