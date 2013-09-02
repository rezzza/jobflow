<?php

namespace Rezzza\JobFlow;

/**
 * Store all JobType and IoWrapper availables
 * 
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobRegistry
{
    /**
     * @var JobTypeInterface[]
     */
    protected $types = array();

    /**
     * @var IoWrapperInterface[]
     */
    protected $wrappers = array();

    protected $extensions = array();

    public function __construct(array $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Try to find a JobTypeInterface registered with $name as alias
     *
     * @param string $id
     *
     * @return JobTypeInterface
     */
    public function getType($name)
    {
        if (!isset($this->types[$name])) {
            $type = null;

            foreach ($this->extensions as $extension) {
                if ($extension->hasType($name)) {
                    $type = $extension->getType($name);
                    break;
                }
            }

            if (!$type) {
                throw new \InvalidArgumentException(sprintf('Could not load type "%s"', $name));
            }

            $this->types[$type->getName()] = $type;
        }

        return $this->types[$name];
    }

    /**
     * Try to find a IoWrapperInterface registered with $name as alias
     *
     * @param string $id
     *
     * @return JobTypeInterface
     */
    public function getWrapper($name)
    {
        if (!isset($this->wrappers[$name])) {
            $wrapper = null;

            foreach ($this->extensions as $extension) {
                if ($extension->hasWrapper($name)) {
                    $wrapper = $extension->getWrapper($name);
                    break;
                }
            }

            if (!$wrapper) {
                throw new \InvalidArgumentException(sprintf('Could not load wrapper "%s"', $name));
            }

            $this->wrappers[$wrapper->getName()] = $wrapper;
        }

        return $this->wrappers[$name];
    }
}