<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;
use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class ExtractorType extends ETLType
{
    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
    {
        $extractor = $input->getProcessor();

        if ($this->isLoggable($extractor) && $execution->getLogger()) {
            $extractor->setLogger($execution->getLogger());
        }

        $offset = $execution->getGlobalOption('offset');
        $limit = $execution->getGlobalOption('limit');
        $max = $execution->getGlobalOption('max');
        $total = $execution->getGlobalOption('total');

        // Limit total to the max if lesser
        if (null === $total) {
            $total = $extractor->count();

            if (null !== $max && $max < $total) {
                $total = $max;
            }

            $execution->setGlobalOption('total', $total);
        }

        if ($execution->getJobOption('offset', 0) > $offset) {
            $offset = $execution->getJobOption('offset');
            $execution->setGlobalOption('offset', $offset);
        }

        // Move offset to the specified position
        try {
            $extractor->seek($offset);
        } catch (\OutOfBoundsException $e) {
            // Message has no more data and should not be spread
            $output->end();

            if ($execution->getLogger()) {
                $execution->getLogger()->debug('No data');
            }
        }

        // Store data read
        for ($i = 0; $i < $limit && $extractor->valid(); $i++) {
            if ($extractor->key() > $total) {
                break;
            }

            $output->write($extractor->current(), $offset + $i);
            $extractor->next();
        }

        return $output;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'offset' => 0,
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