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
     * @var TransportInterface[]
     */
    protected $transports = array();

    /**
     * @var JobExtensionInterface[]
     */
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
    public function getTransport($name)
    {
        if (!isset($this->transports[$name])) {
            $transport = null;

            foreach ($this->extensions as $extension) {
                if ($extension->hasTransport($name)) {
                    $transport = $extension->getTransport($name);
                    break;
                }
            }

            if (!$transport) {
                throw new \InvalidArgumentException(sprintf('Could not load transport "%s"', $name));
            }

            $this->transports[$transport->getName()] = $transport;
        }

        return $this->transports[$name];
    }
}