<?php


namespace Inpsyde\AGBConnector\CustomExceptions;

/**
 * Class ConfigurationException
 *
 * @package Inpsyde\AGBConnector\CustomExceptions
 */
class ConfigurationException extends XmlApiException
{
    const CODE = 80;
    public function __construct($message)
    {
        parent::__construct($message, self::CODE);
    }
}
