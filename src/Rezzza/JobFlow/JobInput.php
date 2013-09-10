<?php

namespace Rezzza\JobFlow;

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
}