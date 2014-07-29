<?php

namespace Rezzza\Jobflow\Metadata;

use Symfony\Component\PropertyAccess\PropertyAccess;

class MetadataAccessor
{
    private $writeMapping;

    private $readMapping;

    private $accessor;

    public function __construct(array $read = array(), array $write = array())
    {
        $this->readMapping = $read;
        $this->writeMapping = $write;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function createMetadata($result, Metadata $source = null)
    {
        $metadata = new Metadata();

        if (null !== $source) {
            $metadata->copy($source);
        }

        foreach ($this->writeMapping as $k => $v) {
            if (is_scalar($v)) {
                $metadata[$k] = $this->accessor->getValue($result, $v);
            } else {
                $metadata[$k] = $v;
            }
        }

        return $metadata;
    }

    public function read($metadata, &$target)
    {
        foreach ($this->readMapping as $k => $v) {
            $this->accessor->setValue($target, $k, $metadata[$v]);
        }
    }
}
