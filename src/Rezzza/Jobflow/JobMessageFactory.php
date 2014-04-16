<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Io;

class JobMessageFactory
{
    public function createInitMsgs($contexts)
    {
        $messages = [];

        foreach ($contexts as $context) {
            $messages[] = $this->createMsg($context, new JobPayload());
        };

        return $messages;
    }

    public function createMsg($context, $payload)
    {
        return new JobMessage($context, $payload);
    }
}
