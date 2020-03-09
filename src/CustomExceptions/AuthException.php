<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class AuthException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class AuthException extends XmlApiException
{
    public function __construct($message, $code = 3, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
