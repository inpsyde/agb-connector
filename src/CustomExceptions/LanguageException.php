<?php

namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class LanguageException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class LanguageException extends XmlApiException
{
    public function __construct($message, $code = 9, XmlApiException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
