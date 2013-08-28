<?php

namespace Rezzza\JobFlow\Extension\Core\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

/**
 * Generic Parent Class for all job type. Generic logic should go here
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array(
            'io'
        ));

        $resolver->setAllowedTypes(array(
            'io' => 'Rezzza\JobFlow\Io\IoDescriptor',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'job';
    }
}