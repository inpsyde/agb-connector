<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class TextException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class TextException extends XmlApiException
{
    const CODE = 5;
    public function __construct($message)
    {
        parent::__construct($message,self::CODE);
    }
}
