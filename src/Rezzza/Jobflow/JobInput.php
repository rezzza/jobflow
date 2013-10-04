<?php

namespace Rezzza\Jobflow;

/**
 * Input for execute method in JobType
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobInput
{
    private $extractor;

    private $transformer;

    private $data = array();

    private $metadata;

    private $metadataManager;

    public function getExtractor()
    {
        return $this->extractor;
    }

    public function getTransformer()
    {
        return $this->transformer;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setExtractor($extractor)
    {
        $this->extractor = $extractor;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
    }

    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function setMetadataManager($manager)
    {
        $this->metadataManager = $manager;
    }

    public function getMetadataManager()
    {
        return $this->metadataManager;
    }
}