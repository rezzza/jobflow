<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\Io\Driver\File;

class FileExtractorType extends AbstractJobType
{
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'args' => function(Options $options) {
                $input = $options['io']->getStdin();

                if (!$input->getDriver() instanceof File) {
                    throw new \InvalidArgumentException(sprintf('Driver given in input of extractor must be a \Rezzza\Jobflow\Io\Driver\File. %s given', get_class($input->getDriver())));
                }

                return array(
                    'filename' => $input->getDriver()->getDsn()
                );
            }
        ));
    }

    public function getName()
    {
        return 'file_extractor';
    }

    public function getParent()
    {
        return 'extractor';
    }

}
