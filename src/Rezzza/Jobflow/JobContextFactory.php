<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Metadata\Metadata;

class JobContextFactory
{
    public function create(JobInterface $job, $input, $current, Metadata $metadata = null)
    {
        return new JobContext(
            $job->getName(),
            $input,
            $current,
            $job->getContextOption(),
            $job->getOptions(),
            $metadata
        );
    }
}
