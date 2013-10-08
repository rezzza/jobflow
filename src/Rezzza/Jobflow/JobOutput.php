<?php

namespace Rezzza\Jobflow;

/**
 * Output for execute method in JobType
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobOutput extends JobStream
{
    private $metadataGenerator;

    public function setMetadataGenerator($generator)
    {
        $this->metadataGenerator = $generator;
    }

    public function write($result, $offset)
    {
        // Should find a way to remove this condition
        if ($this->processor instanceof \Knp\ETL\LoaderInterface) {
            $this->processor->load($result, new \Knp\ETL\Context\Context);

            return;
        }

        $this->message->data[$offset] = $result;
    }

    public function writeMetadata($result, $offset)
    {
        if (null !== $this->metadataGenerator) {
            $this->message->metadata[$offset] = $this->metadataGenerator->generate($result);
        }
    }

    public function setContextFromInput($input)
    {
        $options = $input->getMessage()->context->getOptions();

        $this->message->context->setOptions($options);
    }

    public function end()
    {
        $this->message->ended = true;
    }

    public function isEnded()
    {
        return $this->message->ended;
    }

    public function finish()
    {
        $this->message->pipe = $this->processor->flush(new \Knp\ETL\Context\Context);
        $this->processor->clear(new \Knp\ETL\Context\Context);
    }
}