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

    public function write(&$metadatas, $result, $offset)
    {
        if (null === $metadatas) {
            $metadatas = new Metadata('root');
        }

        foreach ($this->writeMapping as $k => $v) {
            $metadata = isset($metadatas[$v]) ? $metadatas[$v] : new Metadata($v);
            $metadata[$offset] = $this->accessor->getValue($result, $k);
            $metadatas[$v] = $metadata;
        }
    }

    public function read($metadatas, &$target, $offset)
    {
        foreach ($this->readMapping as $k => $v) {
            $metadata = $metadatas[$k][$offset];

            $this->accessor->setValue($target, $v, $metadata);
        }
    }
}