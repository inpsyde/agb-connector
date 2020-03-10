<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class TextTypeException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class TextTypeException extends XmlApiException
{
    const CODE = 4;
    public function __construct($message, XmlApiException $previous = null)
    {
        parent::__construct($message, self::CODE, $previous);
    }
}
