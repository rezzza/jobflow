<?php

namespace Rezzza\Jobflow;

/**
 * Output for execute method in JobType
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobOutput extends JobStream
{
    public function write($result, $offset = null)
    {
        if (null === $offset) {
            $this->message->data[] = $result;
        } else {
            $this->message->data[$offset] = $result;            
        }
    }

    public function writeMetadata($result, $offset, $accessor)
    {
        $accessor->write($this->message->metadata, $result, $offset);
    }

    public function writePipe($value)
    {
        if (null !== $value) {
            $this->message->pipe = $value;
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
}