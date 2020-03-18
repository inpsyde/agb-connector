<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class AuthException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class AuthException extends XmlApiException
{
    const CODE = 3;
    public function __construct($message, XmlApiException $previous = null)
    {
        parent::__construct($message, self::CODE, $previous);
    }
}
