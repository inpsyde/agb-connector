<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class ConfigurationException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class ConfigurationException extends XmlApiException
{
    public function __construct($message, $code = 80, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
