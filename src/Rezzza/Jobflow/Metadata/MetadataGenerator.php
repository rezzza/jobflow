<?php

namespace Rezzza\Jobflow\Metadata;

use Symfony\Component\PropertyAccess\PropertyAccess;

class MetadataGenerator
{
    private $mapping;

    private $accessor;

    public function __construct($mapping)
    {
        $this->mapping = $mapping;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function generate(&$metadatas, $result, $offset)
    {
        if (null === $metadatas) {
            $metadatas = new Metadata('root');
        }

        foreach ($this->mapping as $k => $v) {
            $metadata = isset($metadatas[$v]) ? $metadatas[$v] : new Metadata($v);
            $metadata[$offset] = $this->accessor->getValue($result, $k);
            $metadatas[$v] = $metadata;
        }
    }
}