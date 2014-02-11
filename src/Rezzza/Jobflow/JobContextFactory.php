<?php

namespace Rezzza\Jobflow;

class JobContextFactory
{
    public function create($job, $input, $current, $transport)
    {
        return new JobContext(
            $job->getName(),
            $input,
            $current,
            $job->getConfig()->getOption('context', []),
            $job->getOptions(),
            $transport
        );
    }
}
