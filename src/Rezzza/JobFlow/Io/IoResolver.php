<?php

namespace Rezzza\JobFlow\Io;

use Rezzza\JobFlow\JobRegistry;

class IoResolver
{
    protected $registry;
    
    public function __construct(JobRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function resolve($descriptor)
    {
        if (null === $descriptor) {
            return null;
        }

        foreach ($descriptor as $stream) {
            if (!$stream instanceof AbstractStream) {
                continue;
            }

            $dsn = parse_url($stream->getDsn());

            if (false === $dsn) {
                throw new \RuntimeException(sprintf('Cannot parse dsn : %s', $stream->getDsn()));
            }

            if (!isset($dsn['scheme'])) {
                throw new \RuntimeException(sprintf('dsn "%s" should follow standard format "scheme://host/path"', $stream->getDsn()));
            }

            $protocol = $dsn['scheme'];
            $phpSupported = $this->isPhpSupported($protocol);

            if (false === $phpSupported) {
                $wrapper = $this->registry->getWrapper($protocol);
            } else {
                $wrapper = $this->registry->getWrapper($stream->getFormat());
            }

            $stream->setWrapper($wrapper);
            $stream->parts = $dsn;
        }

        return $descriptor;
    }

    public function isPhpSupported($protocol)
    {
        return in_array($protocol, stream_get_wrappers());
    }
}