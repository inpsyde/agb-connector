<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class LanguageException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class LanguageException extends XmlApiException
{
    const CODE = 9;
    public function __construct($message, XmlApiException $previous = null)
    {
        parent::__construct($message, self::CODE, $previous);
    }
}
