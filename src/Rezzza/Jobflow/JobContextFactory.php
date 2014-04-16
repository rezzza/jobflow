<?php

namespace Rezzza\Jobflow;

class JobContextFactory
{
    public function create($job, $input, $current, $transport, $metadata = null)
    {
        return new JobContext(
            $job->getName(),
            $input,
            $current,
            $job->getContextOption(),
            $job->getOptions(),
            $transport,
            $metadata
        );
    }
}
