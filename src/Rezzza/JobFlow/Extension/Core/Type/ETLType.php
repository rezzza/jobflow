<?php

namespace Rezzza\JobFlow\Extension\Core\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

/**
 * For all type based on ETL pattern we need to specify the step of the process
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
abstract class ETLType extends AbstractJobType
{
    abstract function getETLType();

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'etl_type' => $this->getETLType()
        ));
    }

    protected function isLoggable($object)
    {
        if (!is_object($object)) {
            return false;
        }

        return in_array('Psr\Log\LoggerAwareTrait', class_uses(get_class($object)));
    }
}