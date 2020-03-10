<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class CredentialsException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class CredentialsException extends XmlApiException
{
    const CODE = 2;
    public function __construct($message, XmlApiException $previous = null)
    {
        parent::__construct($message, self::CODE, $previous);
    }
}
