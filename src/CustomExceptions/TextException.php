<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class TextException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class TextException extends XmlApiException
{
    public function __construct($message, $code = 5, XmlApiException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
