<?php

namespace Rezzza\Jobflow;

/**
 * Output for execute method in JobType
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobOutput
{
    private $destination;

    private $data = array();

    private $pipe;

    private $end = false;

    private $metadataManager;

    private $metadata;

    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function setMetadataManager($manager)
    {
        $this->metadataManager = $manager;
    }

    public function getMetadataManager()
    {
        return $this->metadataManager;
    }

    public function write($result, $offset)
    {
        if (null !== $this->metadataManager) {
            $this->metadata[$offset] = $this->metadataManager->generate($result);
        }

        if (null === $this->getDestination()) {
            $this->data[$offset] = $result;

            return;
        }

        $this->getDestination()->load($result, new \Knp\ETL\Context\Context);
    }

    public function finish()
    {
        $this->pipe = $this->getDestination()->flush(new \Knp\ETL\Context\Context);
        $this->getDestination()->clear(new \Knp\ETL\Context\Context);
    }

    public function getPipe()
    {
        return $this->pipe;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function end()
    {
        $this->end = true;
    }

    public function isEnded()
    {
        return $this->end;
    }
}