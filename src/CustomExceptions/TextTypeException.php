<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class TextTypeException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class TextTypeException extends XmlApiException
{
    public function __construct($message, $code = 4, XmlApiException $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
