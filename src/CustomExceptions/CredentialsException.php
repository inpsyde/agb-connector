<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class CredentialsException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class CredentialsException extends XmlApiException
{
    public function __construct($message, $code = 2, XmlApiException $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
