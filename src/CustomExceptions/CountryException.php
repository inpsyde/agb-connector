<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class CountryException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class CountryException extends XmlApiException
{
    public function __construct($message, $code = 17, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
