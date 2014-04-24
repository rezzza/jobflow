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

    /**
     * @return boolean
     */
    public function hasType($name);

    public function getTransport($name);

    /**
     * @return boolean
     */
    public function hasTransport($name);

    public function getTypeExtensions($name);

    /**
     * @return boolean
     */
    public function hasTypeExtensions($name);
}