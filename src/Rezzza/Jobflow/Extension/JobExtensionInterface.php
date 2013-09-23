<?php

namespace Rezzza\Jobflow\Extension;

/**
 * Extends JobFlow easy by adding type, transport or typeExtension
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
interface JobExtensionInterface
{
    public function getType($name);

    public function hasType($name);

    public function getTransport($name);

    public function hasTransport($name);

    public function getTypeExtensions($name);

    public function hasTypeExtensions($name);
}