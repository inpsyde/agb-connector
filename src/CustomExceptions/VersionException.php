<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class VersionException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class VersionException extends XmlApiException
{
    public function __construct($message, $code = 1, XmlApiException $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
