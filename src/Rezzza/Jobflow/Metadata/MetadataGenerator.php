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

    public function generate($result)
    {
        $metadata = new Metadata();

        foreach ($this->mapping as $k => $v) {
            $metadata->fields[$v] = $this->accessor->getValue($result, $k);
        }

        return $metadata;
    }
}