<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class VersionException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class VersionException extends XmlApiException
{
    const CODE = 1;
    public function __construct($message, XmlApiException $previous = null)
    {
        parent::__construct($message, self::CODE, $previous);
    }
}
